<?php

namespace ryunosuke\dbml\Query;

use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Schema\Column;
use ryunosuke\dbml\Database;
use ryunosuke\dbml\Entity\Entityable;
use ryunosuke\dbml\Gateway\TableGateway;
use ryunosuke\dbml\Mixin\DebugInfoTrait;
use ryunosuke\dbml\Mixin\IteratorTrait;
use ryunosuke\dbml\Mixin\OptionTrait;
use ryunosuke\dbml\Query\Expression\Alias;
use ryunosuke\dbml\Query\Expression\Expression;
use ryunosuke\dbml\Query\Expression\PhpExpression;
use ryunosuke\dbml\Query\Expression\SelectOption;
use ryunosuke\dbml\Query\Expression\TableDescriptor;
use ryunosuke\dbml\Query\Pagination\Paginator;
use ryunosuke\dbml\Query\Pagination\Sequencer;
use ryunosuke\dbml\Utility\Adhoc;
use function ryunosuke\dbml\array_convert;
use function ryunosuke\dbml\array_depth;
use function ryunosuke\dbml\array_each;
use function ryunosuke\dbml\array_find;
use function ryunosuke\dbml\array_flatten;
use function ryunosuke\dbml\array_implode;
use function ryunosuke\dbml\array_lookup;
use function ryunosuke\dbml\array_map_method;
use function ryunosuke\dbml\array_maps;
use function ryunosuke\dbml\array_order;
use function ryunosuke\dbml\array_set;
use function ryunosuke\dbml\array_sprintf;
use function ryunosuke\dbml\array_strpad;
use function ryunosuke\dbml\array_unset;
use function ryunosuke\dbml\arrayize;
use function ryunosuke\dbml\concat;
use function ryunosuke\dbml\first_keyvalue;
use function ryunosuke\dbml\first_value;
use function ryunosuke\dbml\optional;
use function ryunosuke\dbml\preg_splice;
use function ryunosuke\dbml\rbind;
use function ryunosuke\dbml\split_noempty;
use function ryunosuke\dbml\stdclass;
use function ryunosuke\dbml\throws;

// @formatter:off
/**
 * クエリビルダークラス
 *
 * SELECT に特化していて、 AFFECT 系クエリをビルドすることはできない。
 *
 * ### IteratorAggregate, Countable
 *
 * IteratorAggregate, Countable を実装しているので、 foreach で回すことができるし、count($qb) で件数の取得もできる。
 * foreach の取得処理は array() であり、レコードの配列（≒連想配列）が渡ってくる。 count() はその件数である。
 *
 * ```php
 * $qb = $db->select('t_article');
 * foreach ($qb as $row) {
 *     // $row は ['id' => 1, 'title' => 'hoge title'] のような配列
 * }
 * echo count($qb); // t_article の件数を返す
 * ```
 *
 * ### プリペアードステートメント
 *
 * 明示的に prepare のようなメソッドを呼ばない限り内部のプリペアードステートメントでは名前付きパラメータを一切使用しない（{@link Statement} も参照）。
 * したがってメソッド呼び出し順によってはパラメータ順が「ズレ」ることがある。
 * 基本的にはズレないようにはしてある（ズレるのはかなり特殊な条件）が、怖い場合は `queryInto` で文字列化してから結合したりサブクエリで使用したりした方が確実。
 *
 * @method bool                   getAutoOrder()
 * @method $this                  setAutoOrder($bool) {自動で主キー順にするか（{@link detectAutoOrder()} を参照）}
 * @method string                 getPrimarySeparator()
 * @method $this                  setPrimarySeparator($string) {
 *     主キー区切り文字を設定する
 *
 *     自動で子供テーブルをひっぱるような処理において、その配列のキーは主キーになる。
 *     ただし、単純な親子テーブルを引っ張るような場合は自動でよしなに一意性を検出してキーとする。
 *
 *     1. 共に自動採番主キーである親テーブルと子テーブル
 *     2. 親テーブルへの外部キーが複合主キーの一部である子テーブル
 *
 *     1 の場合は共に自動採番なので子テーブルのキーは主キーになる。区切り文字は使われない。
 *     2 の場合は複合主キーであり、単一カラムで表すことはできないが、「自動で子供テーブルを引っ張る」ような処理は外部キーを元に引っ張るので**その子供の世界では複合主キーの片方だけで一意に**なる。
 *     つまり結果として区切り文字は使われない。
 *
 *     それでも冗長にキーを持っていたりするとどうしても自動では一意性が検出できない場合がある。
 *     そのような場合に結合する文字列を指定する。
 *
 *     @param string $string 主キー区切り文字
 * }
 * @method string                 getAggregationDelimiter()
 * @method $this                  setAggregationDelimiter($string) {
 *     集約関数の区切り文字を設定する
 *
 *     min, max などの集約関数を実行するときに付与される文字列を指定する。
 *     集約関数は大抵の場合は単一クエリで実行するので大部分で指定不要だが、時折指定が必要になることがある。
 *
 *     ```php
 *     # こういった個別メソッドの場合は指定する意味はない（区切りも何もスカラー値で取れるんだから）
 *     $db->min('table');
 *     // results: 1
 *
 *     # このように aggregate メソッドを使用したときに活きる
 *     $db->aggregate(['min', 'max'], 'table.id', [], 'table.group_id');
 *     // results:
 *     [
 *         1 => [
 *             'table.id@min' => 1,
 *             'table.id@max' => 2,
 *         ],
 *     ]
 *     ```
 *
 *     @param string $string  集約関数の区切り文字
 * }
 * @method bool                   getInjectChildColumn()
 * @method $this                  setInjectChildColumn($bool)
 *
 * @method $this                  innerJoinOn($table, $on, $from = null) {結合方法が INNER で結合条件指定の {@link join()}@inheritdoc join()}
 * @method $this                  leftJoinOn($table, $on, $from = null) {結合方法が LEFT で結合条件指定の {@link join()}@inheritdoc join()}
 * @method $this                  rightJoinOn($table, $on, $from = null) {結合方法が RIGHT で結合条件指定の {@link join()}@inheritdoc join()}
 *
 * @method $this                  autoJoinForeign($table, $fkeyname = null, $from = null) {結合方法が AUTO で外部キー指定の {@link join()}@inheritdoc join()}
 * @method $this                  innerJoinForeign($table, $fkeyname = null, $from = null) {結合方法が INNER で外部キー指定の {@link join()}@inheritdoc join()}
 * @method $this                  leftJoinForeign($table, $fkeyname = null, $from = null) {結合方法が LEFT で外部キー指定の {@link join()}@inheritdoc join()}
 * @method $this                  rightJoinForeign($table, $fkeyname = null, $from = null) {結合方法が RIGHT で外部キー指定の {@link join()}@inheritdoc join()}
 *
 * @method $this                  autoJoinForeignOn($table, $on, $fkeyname = null, $from = null) {結合方法が AUTO で結合条件・外部キー指定の {@link join()}@inheritdoc join()}
 * @method $this                  innerJoinForeignOn($table, $on, $fkeyname = null, $from = null) {結合方法が INNER で結合条件・外部キー指定の {@link join()}@inheritdoc join()}
 * @method $this                  leftJoinForeignOn($table, $on, $fkeyname = null, $from = null) {結合方法が LEFT で結合条件・外部キー指定の {@link join()}@inheritdoc join()}
 * @method $this                  rightJoinForeignOn($table, $on, $fkeyname = null, $from = null) {結合方法が RIGHT で結合条件・外部キー指定の {@link join()}@inheritdoc join()}
 *
 * @method array|Entityable[]     array(array $params = []) {自身が保持しているクエリでレコードの配列を返す（{@link Database::fetchArray()} 参照）}
 * @method array|Entityable[]     assoc(array $params = []) {自身が保持しているクエリでレコードの連想配列を返す（{@link Database::fetchAssoc()} 参照）}
 * @method array                  lists(array $params = []) {自身が保持しているクエリでレコード[1列目]の配列を返す（{@link Database::fetchLists()} 参照）}
 * @method array                  pairs(array $params = []) {自身が保持しているクエリでレコード[1列目=>2列目]の連想配列を返す（{@link Database::fetchPairs()} 参照）}
 * @method array|Entityable|false tuple(array $params = []) {自身が保持しているクエリでレコードを返す（{@link Database::fetchTuple()} 参照）}
 * @method mixed                  value(array $params = []) {自身が保持しているクエリでレコード[1列目]を返す（{@link Database::fetchValue()} 参照）}
 */
// @formatter:on
class QueryBuilder implements Queryable, \IteratorAggregate, \Countable
{
    use OptionTrait;
    use IteratorTrait;
    use DebugInfoTrait;

    // 構成要素のキー
    public const CLAUSES = [
        'column',
        'where',
        'orderBy',
        'limit',
        'groupBy',
        'having',
    ];

    private const COUNT_ALIAS = '__dbml_auto_cnt';

    // パラメータオフセットのゲタ
    private const PARAMETER_OFFSET = 1000000;

    /** @var array bind パラメータのオフセット */
    private const PARAMETER_OFFSETS = [
        'select' => 1 * self::PARAMETER_OFFSET,
        'union'  => 2 * self::PARAMETER_OFFSET,
        'from'   => 3 * self::PARAMETER_OFFSET,
        'join'   => 4 * self::PARAMETER_OFFSET,
        'where'  => 5 * self::PARAMETER_OFFSET,
        'having' => 6 * self::PARAMETER_OFFSET,
    ];

    /** @var array SQL の各句 */
    private $sqlParts = [
        'comment' => [],
        'option'  => [],
        'select'  => [],
        'from'    => [],
        'join'    => [],
        'hint'    => [],
        'where'   => [],
        'groupBy' => [],
        'having'  => [],
        'orderBy' => [],
        'offset'  => null,
        'limit'   => null,
        'union'   => [],
        'colval'  => [],
    ];

    /** @var string 生成した SQL（キャッシュ） */
    private $sql;

    /** @var array bind パラメータ */
    private $params = [];

    /** @var PhpExpression[] php 式配列 */
    private $phpExpressions = [];

    /** @var array JOIN 順 */
    private $joinOrders = [];

    /** @var mixed|null php レイヤのソート順 */
    private $phpOrders;

    /** @var array ラッピング文字列配列 */
    private $wrappers = [];

    /** @var int ロックモード定数（LockMode::XXX） */
    private $lockMode;

    /** @var string ロックオプション */
    private $lockOption;

    /** @var Database データベース */
    private $database;

    /** @var Statement prepare されたステートメント */
    private $statement;

    /** @var SubqueryBuilder[] サブビルダー配列 */
    private $subbuilders = [];

    /** @var null|bool submethod(null で無効、true で有効、false で否定有効、文字列で集約関数) */
    private $submethod;

    /** @var bool|int クエリを投げると同時に limit を外した件数を取得するか */
    private $rowcount = false;

    /** @var string|array|callable fetch 時のタイプ */
    private $caster;

    /** @var bool '!' 付き条件で全てがフィルタされたか */
    private $emptyCondition;

    /** @var bool テーブル名プレフィックスを付与するか */
    private $autoTablePrefix = false;

    /** @var bool 自動 order by 有効/無効フラグが効いているか否か（基本的に単純な toString では効かせない） */
    private $enableAutoOrder = false;

    public static function getDefaultOptions()
    {
        return [
            // 自動 order by 有効/無効フラグ
            'autoOrder'            => true,
            // 複合主キーを単一主キーとみなすための結合文字列
            'primarySeparator'     => "\x1F",
            // aggregate 時（columnname@sum）の区切り文字
            'aggregationDelimiter' => '@',
            // サブクエリをコメント化して親のクエリに埋め込むか否か
            'injectChildColumn'    => false,
        ];
    }

    /**
     * コンストラクタ
     *
     * @param Database $database データベースオブジェクト
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->setDefault($database->getOptions());
        $this->setProvider(\Closure::bind(function () {
            return $this->array();
        }, null, null));
    }

    /**
     * @ignore
     */
    public function __clone()
    {
        $clone = static function ($somthing) use (&$clone) {
            if (is_object($somthing)) {
                $somthing = clone $somthing;
            }
            elseif (is_array($somthing)) {
                foreach ($somthing as $key => $value) {
                    $somthing[$key] = $clone($value);
                }
            }
            return $somthing;
        };
        $this->sqlParts = $clone($this->sqlParts);
        $this->params = $clone($this->params);
        $this->subbuilders = $clone($this->subbuilders);
    }

    /**
     * @ignore
     *
     * @param string $name メソッド名
     * @param array $arguments 引数配列
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        // OptionTrait へ移譲
        $result = $this->OptionTrait__callGetSet($name, $arguments, $called);
        if ($called) {
            return $result;
        }

        // join 系
        if (preg_match('/^(inner|left|right)joinOn$/ui', $name, $matches)) {
            array_splice($arguments, 2, 0, ['']);
            return $this->join($matches[1], ...$arguments);
        }
        if (preg_match('/^(auto|inner|left|right)joinForeign$/ui', $name, $matches)) {
            array_splice($arguments, 1, 0, [[]]);
            return $this->join($matches[1], ...$arguments);
        }
        if (preg_match('/^(auto|inner|left|right)joinForeignOn$/ui', $name, $matches)) {
            return $this->join($matches[1], ...$arguments);
        }

        // perform 系
        $methods = Database::METHODS;
        if (isset($methods[strtolower($name)])) {
            return $this->getDatabase()->{"fetch$name"}($this, ...$arguments);
        }

        throw new \BadMethodCallException("'$name' is undefined.");
    }

    /**
     * クエリ文字列を返す
     *
     * @return string エスケープされていないクエリ文字列
     */
    public function __toString()
    {
        // __toString が例外を投げると即死するので例外ではなくエラーに変換する
        try {
            return $this->_getSql();
        }
        catch (\Exception $ex) {
            trigger_error(get_class($ex) . ':' . $ex->getMessage() . "\n" . $ex->getTraceAsString(), E_USER_WARNING);
            return 'invalid QueryBuilder';
        }
    }

    private function _getSql()
    {
        if ($this->sql !== null) {
            return $this->sql;
        }

        $platform = $this->database->getPlatform();
        $cplatform = $this->database->getCompatiblePlatform();

        $commentize = static function ($comments, $nest) use (&$commentize, $cplatform) {
            $spacer = str_repeat(' ', $nest * 3);
            $result = '';
            foreach ($comments as $key => $comment) {
                if (is_array($comment)) {
                    if (!is_int($key)) {
                        $result .= $spacer . $cplatform->commentize($key);
                    }
                    $result .= $commentize($comment, $nest + 1);
                }
                else {
                    $result .= $spacer . $cplatform->commentize($comment);
                }
            }
            return $result;
        };

        $comments = $commentize($this->sqlParts['comment'], 0);

        // 無理に変更してるので clone する
        $builder = clone $this;

        // UNION
        if ($builder->sqlParts['union']) {
            // 0 には UNIONN ALL/UNION の記号（最初の要素以外）、1 にはクエリ文字列が入っている
            $sql = array_sprintf($builder->sqlParts['union'], function ($union) { return trim(implode(' ', $union)); }, ' ');

            // select,join,where,group,having,order,limit(要するに from 以外) が設定されている場合はラップしたサブクエリに掛かる
            $parts = $builder->sqlParts;
            if ($parts['select'] || $parts['join'] || $parts['where'] || $parts['groupBy'] || $parts['having'] || $parts['orderBy'] || $parts['offset'] !== null || $parts['limit'] !== null) {
                // from が変わるので join も変えなければならない
                $builder->sqlParts['select'] = $parts['select'] ?: ['*'];
                $builder->sqlParts['from'][] = ['table' => "($sql)", 'alias' => '__dbml_union_table', 'fkeyname' => null, 'condition' => null];
                $builder->sqlParts['join'] = ['__dbml_union_table' => array_merge([], ...array_values($parts['join']))];
                $sql = $builder->resetQueryPart('union');
            }
            return $this->sql = $comments . $sql;
        }

        // SELECT 句に手を加える
        foreach ($builder->sqlParts['select'] as $n => $select) {
            if ($builder->autoTablePrefix && is_string($select)) {
                $parts = explode('.', $select);
                if (count($parts) === 2 && $parts[1] !== '*') {
                    $qalias = $cplatform->quoteIdentifierIfNeeded($select);
                    $builder->sqlParts['select'][$n] = new Alias($qalias, $select);
                }
            }
            elseif ($select instanceof Alias) {
                $alias = $select->getAlias();
                $actual = $select->getActual();

                if ($builder->autoTablePrefix && is_string($actual)) {
                    $parts = explode('.', $actual);
                    if (count($parts) === 2 && $parts[1] !== '*') {
                        $alias = $parts[0] . '.' . $alias;
                    }
                }

                $qalias = $cplatform->quoteIdentifierIfNeeded($alias);
                if ($qalias !== $alias) {
                    $builder->sqlParts['select'][$n] = new Alias($qalias, $actual);
                }
            }
        }
        // 子セレクトを埋め込む
        if ($builder->subbuilders && $builder->getInjectChildColumn() && count($builder->sqlParts['select']) > 0) {
            $toParentColumns = array_sprintf($builder->subbuilders, function (SubqueryBuilder $subbuilder, $key) {
                return $subbuilder->toParentColumn($key);
            }, '');
            $builder->sqlParts['select'][0] = $toParentColumns . ' ' . $builder->sqlParts['select'][0];
        }

        // FROM,JOIN 句に手を加える
        array_maps(array_filter(array_column($builder->getFromPart(), 'table'), rbind('is_a', self::class)), ['wrap' => ['', '']]);

        // ORDER BY 句に手を加える
        if ($builder->enableAutoOrder && !$builder->sqlParts['orderBy']) {
            // 集約なら除外
            if (!$builder->sqlParts['groupBy']) {
                // EXISTS 述語も除外
                if (!isset($builder->wrappers['EXISTS']) && !isset($builder->wrappers['NOT EXISTS'])) {
                    // COUNT を含むなら除外
                    if (false === array_find($builder->sqlParts['select'], function ($s) { return strpos("$s", self::COUNT_ALIAS) !== false; })) {
                        $builder->orderByPrimary();
                    }
                }
            }
        }
        foreach ($builder->joinOrders as $jorder) {
            $builder->addOrderBy($jorder);
        }

        // 色々手を加えたやつでクエリ文字列化
        $sql = 'SELECT'
            . concat(' ', implode(' ', $builder->sqlParts['option']))
            . concat(' ', implode(', ', $builder->sqlParts['select']) ?: '*')
            . concat(' FROM ', implode(', ', $builder->_getFromClauses()))
            . concat(' WHERE ', implode(' AND ', Adhoc::wrapParentheses($builder->sqlParts['where'])))
            . concat(' GROUP BY ', implode(', ', $builder->sqlParts['groupBy']))
            . concat(' HAVING ', implode(' AND ', Adhoc::wrapParentheses($builder->sqlParts['having'])))
            . concat(' ORDER BY ', array_sprintf($builder->sqlParts['orderBy'], function ($v, $k) { return "$k " . ($v ? 'ASC' : 'DESC'); }, ', '));

        $sql = $platform->modifyLimitQuery($sql, $builder->sqlParts['limit'], $builder->sqlParts['offset']);
        $sql = $cplatform->appendLockSuffix($sql, $builder->lockMode, $builder->lockOption);

        // 最後にラッピングして終わり
        foreach ($builder->wrappers as $wraper) {
            $sql = concat($wraper[0], ' ') . "($sql)" . concat(' ', $wraper[1]);
        }

        return $this->sql = $comments . $sql;
    }

    private function _getFromClauses()
    {
        $platform = $this->getDatabase()->getPlatform();
        $fromClauses = [];
        foreach ($this->sqlParts['from'] as $from) {
            $tname = $from['alias'] ?: $from['table'];
            $clause = $from['table'] . concat(' ', $from['alias']);
            $clause = $platform->appendLockHint($clause, $this->lockMode); // for SQLServer
            $sql = $clause
                . concat(' ', $this->sqlParts['hint'][$tname] ?? '')
                . $this->_getJoinClauses($tname);

            $fromClauses[$tname] = $sql;
        }
        return $fromClauses;
    }

    private function _getJoinClauses($fromAlias)
    {
        $sql = $jsql = '';
        foreach ($this->sqlParts['join'][$fromAlias] ?? [] as $join) {
            $jname = $join['alias'] ?: $join['table'];
            $clause = $join['table'] . concat(' ', $join['alias']);
            $sql .= ' ' . strtoupper($join['type']) . ' JOIN ' . $clause
                . concat(' ', $this->sqlParts['hint'][$jname] ?? '')
                . ' ON ' . $join['condition'];

            $jsql .= $this->_getJoinClauses($jname);
        }
        return $sql . $jsql;
    }

    private function _buildCondition($type, $predicates, $ack = true)
    {
        $subtype = "and$type";

        $froms = array_filter($this->getFromPart(), function ($from) {
            return is_string($from['table']);
        });

        $predicates = array_convert($predicates, function ($cond, &$param, $keys) use ($subtype, $froms) {
            // 主キー
            if ($cond === '') {
                if (count($keys) > 1) {
                    return false;
                }
                $from = reset($froms) ?: throws(new \UnexpectedValueException('base table not found.'));
                $pcols = $this->database->getSchema()->getTablePrimaryKey($from['table'])->getColumns();
                $params = (array) $param;
                if (count($pcols) !== 1 && count($params) !== 0 && array_depth($params) === 1) {
                    $params = [$params];
                }
                $pvals = array_each($params, function (&$carry, $pval) use ($pcols) {
                    $pvals = (array) $pval;
                    if (count($pcols) !== count($pvals)) {
                        throw new \InvalidArgumentException('argument\'s length is not match primary columns.');
                    }
                    $carry[] = array_combine($pcols, $pvals);
                });
                return [$this->database->getCompatiblePlatform()->getPrimaryCondition($pvals, $from['alias'])];
            }
            // エニーカラム（*.*）
            if (is_string($cond) && preg_match('#^((.*)\.)?\*$#u', $cond, $matches)) {
                if (array_filter($keys, 'is_int') !== $keys) {
                    return false;
                }
                list(, , $alias) = $matches + [2 => '*'];
                $wcond = [];
                foreach ($froms as $from) {
                    if ($alias === '*' || $from['alias'] === $alias) {
                        $taname = $from['table'] . ' ' . $from['alias'];
                        $wcond += $this->database->anywhere($taname, $param);
                    }
                }
                $param = $wcond;
                return true;
            }
            // エニーカラム（*.column_name）
            if (is_string($cond) && strpos($cond, '*.') === 0) {
                if (count($keys) > 1) {
                    return false;
                }
                list(, $column) = explode('.', $cond, 2);
                $subcond = [];
                foreach ($froms as $from) {
                    $columns = $this->database->getSchema()->getTableColumns($from['table']);
                    if (array_key_exists($column, $columns)) {
                        $subcond[$from['alias'] . '.' . $column] = $param;
                    }
                }
                // サブビルダにも適用する
                foreach ($this->subbuilders as $subkey => $subbuilder) {
                    $subbuilder->$subtype([$cond => $param]);
                }
                return $subcond;
            }
            // ネストカラム（T1/T2/T3.column_name）
            if (is_string($cond) && strpos($cond, '/') !== false) {
                if (count($keys) > 1) {
                    return false;
                }
                list($subkey, $subcolumn) = explode('/', $cond, 2);
                if (isset($this->subbuilders[$subkey])) {
                    $this->subbuilders[$subkey]->$subtype([$subcolumn => $param]);
                }
                return false;
            }
            // サブカラム（['Sub' => [$condition]]）
            if (isset($this->subbuilders[$cond])) {
                if (count($keys) > 1) {
                    return false;
                }
                $this->subbuilders[$cond]->$subtype($param);
                return false;
            }
            // サブクエリビルダ(subexists, submax, sub...)
            if ($param instanceof QueryBuilder && ($submethod = $param->getSubmethod()) !== null) {
                // "P" or "P:fkey" or "colname|P" or "colname|P:fkey"
                $conds = is_bool($submethod) ? "|$cond" : $cond;
                $colname = preg_splice('#\|([a-z0-9_]+)(:([a-z0-9_]+))?#ui', '', $conds, $matches);
                $falias = $matches[1] ?? null;
                $fkname = $matches[3] ?? null;
                if (array_key_exists($falias, $froms)) {
                    if ($param->setSubwhere($froms[$falias]['table'], $froms[$falias]['alias'], $fkname)) {
                        return is_string($submethod) ? $colname : true;
                    }
                }
                else {
                    foreach ($froms as $from) {
                        if ($param->setSubwhere($from['table'], $from['alias'])) {
                            return null;
                        }
                    }
                }
            }
        }, true);

        $params = [];
        $ands = [];
        foreach ($predicates as $cond) {
            $placeholders = $this->database->whereInto(arrayize($cond), $params, 'OR', $this->emptyCondition);
            Adhoc::array_push($ands, implode(' AND ', Adhoc::wrapParentheses($placeholders)));
        }

        if ($ands) {
            $ors = implode(' OR ', Adhoc::wrapParentheses($ands));
            if (!$ack) {
                $ors = 'NOT (' . $ors . ')';
            }
            $this->sqlParts[$type][] = $ors;
        }
        $this->addParam($params, $type);

        return $this->_dirty();
    }

    private function _isSecureColumn($column)
    {
        // Expression は信頼できる
        if ($column instanceof Expression) {
            return true;
        }

        // 文字列は select,from,join 句を調べて一致するもののみ
        if (is_string($column)) {
            // SELECT 句と比較
            foreach ($this->sqlParts['select'] as $select) {
                // エイリアス指定ならそれを使う
                if ($select instanceof Alias) {
                    $select = $select->getAlias();
                }

                // 完全に一致するならそれはセキュア
                if ($column === $select) {
                    return true;
                }
            }

            // 修飾をバラす
            $col = $column;
            if (strpos($col, '.') !== false) {
                list($modifier, $col) = explode('.', $col, 2);
            }

            // FROM 句と比較
            foreach ($this->getFromPart() as $table) {
                // QueryBuilder なら更に再帰
                if ($table['table'] instanceof QueryBuilder) {
                    if ($table['table']->_isSecureColumn($column)) {
                        return true;
                    }
                    continue;
                }
                // 修飾されていたならテーブル名と比較しないと '1; DELETE FROM tablename -- .id' で攻撃が可能
                if (isset($modifier) && ($modifier !== $table['table'] && $modifier !== $table['alias'])) {
                    continue;
                }
                // テーブルにカラムが存在しないなら次へ
                if (!array_key_exists($col, $this->database->getSchema()->getTableColumns($table['table']))) {
                    continue;
                }

                return true;
            }
        }

        // 上記で引っかからなかったら false
        return false;
    }

    private function _dirty()
    {
        $this->sql = null;
        $this->resetResult();
        return $this;
    }

    /**
     * 指定カラム群を concat したカラムを先頭に追加する
     *
     * ただし、カラム群が1つでかつ既に含まれているなら何もしない。
     * なお、このメソッドの Primary とはいわゆる主キーではなく「主要な」という意味の Primary である。
     *
     * @ignore
     *
     * @param string $alias エイリアス名
     * @param array $columns 追加するカラム
     * @param bool $prepended 前に追加するなら true
     * @return string Primary キー
     */
    protected function _addPrimary($alias, $columns, $prepended)
    {
        $selects = $this->sqlParts['select'];

        // 1つの場合は既に含まれているか探す
        if (count($columns) === 1) {
            $pk = reset($columns);
            $modifier = null;
            $pkc = $pk;
            if (strpos($pk, '.') !== false) {
                list($modifier, $pkc) = explode('.', $pk);
            }
            foreach ($selects as $n => $select) {
                // Alias は実名を使う
                if ($select instanceof Alias) {
                    $select = $select->getActual();
                }
                // 含まれているなら・・・
                if (in_array($select, [$pk, $pkc], true)) {
                    // 挿入モードで 0 番目ならOK. あるいは追加モードならそもそも順番は関係ない
                    if (($prepended && $n === 0) || (!$prepended)) {
                        return $pkc;
                    }
                }
                // * を含む場合は・・・
                if (in_array($select, ["$modifier.*", '*'], true)) {
                    // 挿入モードならエイリアスを変更して後段に任せる(* なら対象を必ず含んでいるので前に追加するだけで良い)
                    // 結果「table.id, table.*」のようになってしまうが php レイヤーでは関係ない
                    if ($prepended) {
                        $alias = $pkc;
                        break;
                    }
                    // 追加モードならそのまま返す(位置は関係なく対象が含まれていれば良いので * で事足りる)
                    else {
                        return $pkc;
                    }
                }
            }
        }

        // 区切り文字で区切って、引数に応じて前・後に追加
        $pkcol = array_implode($columns, $this->database->quote($this->getPrimarySeparator()));
        $primary = new Alias($alias, $this->database->getCompatiblePlatform()->getConcatExpression($pkcol));
        if ($prepended) {
            array_unshift($selects, $primary);
            $this->select(...$selects);
        }
        else {
            $this->addSelect($primary);
        }

        return $alias;
    }

    /**
     * データベースを返す
     *
     * @return Database 保持している Database オブジェクト
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * 取得クラスを指定。クラス名ならそのクラスで、コールバックならそれの呼び出し、になる
     *
     * このメソッドを使うとこのインスタンスが返すレコード配列の型を指定できる。
     * 指定の方法は大まかには下記の5種類。
     *
     * 1. callable （行配列を受け取るクロージャ）
     *     - 最も汎用性がある
     * 2. クラス名
     *     - 与えたクラスのインスタンスで返却されるようになる
     * 3. クラス名 => コンストラクタパラメータ
     *     - 上記とほぼ同じだが、コンストラクタパラメータを指定できる
     * 4. null （あるいは未指定）
     *     - 駆動表から導き出されるエンティティクラスで返却されるようになる（指定がない場合はデフォルトエンティティ）
     * 5. "array" という文字列
     *     - 配列で返却するようになる（実質的に解除動作として動作する）
     *
     * ```php
     * # 1. callable
     * $qb->column('table_name')->cast(function ($row) {
     *     // $row はレコードの各行の配列
     *     return new \ArrayObject($row, \ArrayObject::ARRAY_AS_PROPS);
     * });
     * // table_name のレコードを ArrayObject インスタンスで返すようになる
     *
     * # 2. クラス名
     * $qb->column('table_name')->cast(EntityClass::class);
     * // table_name のレコードを EntityClass インスタンスで返すようになる
     *
     * # 3. クラス名 => コンストラクタパラメータ
     * $qb->column('table_name')->cast([EntityClass::class => ['somthing']]);
     * // table_name のレコードを EntityClass インスタンスで返すようになる（そのインスタンスは 'something' でコンストラクタがコールされる）
     *
     * # 4. null（省略）
     * $qb->column('table_name')->cast();
     * // table_name のレコードを TableName インスタンスで返すようになる（駆動表 -> エンティティ名は Database に対して指定する）
     *
     * # 5. "array"
     * $qb->column('table_name')->cast("array");
     * // 何もしなかった場合と変わらない。が、上記の 1～4 で設定したものを解除できるという重要な役割がある
     * ```
     *
     * なお、 このメソッドを呼んでも、 `lists` や `pairs` には一切影響しない。
     * これらは配列を返すメソッドであり、「レコード」という概念が通用しない。 `value` もスカラー値なので同様。
     *
     * @param string|array|callable $classname 取得クラス
     * @return $this 自分自身
     */
    public function cast($classname = null)
    {
        // null は特別扱い(駆動表をエンティティにする)
        if ($classname === null) {
            $froms = $this->getFromPart();
            $from = reset($froms);
            $classname = $this->database->getEntityClass([$from['alias'], $from['table']], true);
            foreach ($this->subbuilders as $subselect) {
                $subselect->cast(null);
            }
        }

        // array は特別扱い(array に戻す)
        if ($classname === 'array') {
            $this->caster = null;
            return $this;
        }

        // callable は素で OK
        if (is_callable($classname)) {
            $this->caster = $classname;
            return $this;
        }

        $cname = $classname;
        $ctorargs = $this->database->getEntityArgument();

        if (is_array($classname)) {
            if (count($classname) !== 1) {
                throw new \InvalidArgumentException('$classname array must be nested array (class => ctorargs).');
            }
            list($cname, $ctorargs) = first_keyvalue($classname);
            if (!is_array($ctorargs)) {
                throw new \InvalidArgumentException('$classname array must be nested array (class => ctorargs).');
            }
        }

        if (!class_exists($cname)) {
            throw new \InvalidArgumentException("class '$cname' is not exists.");
        }

        if (!is_subclass_of($cname, Entityable::class)) {
            throw new \InvalidArgumentException('$classname must be implements Entityable.');
        }

        $this->caster = [$cname => $ctorargs];
        return $this;
    }

    /**
     * 行キャストクロージャを取得する
     *
     * @ignore
     *
     * @return callable 行キャストクロージャ
     */
    public function getCaster()
    {
        if ($this->caster === null) {
            return null;
        }
        if (is_callable($this->caster)) {
            return $this->caster;
        }
        if (is_array($this->caster)) {
            list($ft, $ctorargs) = first_keyvalue($this->caster);
            return function ($row) use ($ft, $ctorargs) {
                /** @var Entityable $entity */
                $entity = new $ft(...$ctorargs);
                return $entity->assign($row);
            };
        }

        // 今のところあり得ないが将来に備えて例外は投げておく
        throw new \DomainException("caster is invalid type (" . gettype($this->caster) . ")."); // @codeCoverageIgnore
    }

    /**
     * submethod を設定する
     *
     * Database 経由・あるいは内部から呼ばれる前提で外からは呼ばれない。
     *
     * @ignore
     *
     * @param null|bool|string $method サブメソッド
     * @return $this 自分自身
     */
    public function setSubmethod($method)
    {
        if ($method === null) {
            $this->submethod = $method;
            return $this;
        }
        if ($method === true) {
            $this->submethod = $method;
            return $this->exists();
        }
        if ($method === false) {
            $this->submethod = $method;
            return $this->notExists();
        }
        if (is_string($method)) {
            $this->submethod = $method;
            return $this;
        }

        throw new \DomainException('submethod is invalid type.');
    }

    /**
     * submethod を取得する
     *
     * @ignore
     *
     * @return null|bool|string サブメソッド
     */
    public function getSubmethod()
    {
        return $this->submethod;
    }

    /**
     * subexists の where 句を設定する
     *
     * @ignore
     *
     * @param string $table テーブル名
     * @param string|null $alias エイリアス名
     * @param string|null $fkeyname 外部キー名
     * @return bool 関連が見つかり、設定されたら true
     */
    public function setSubwhere($table, $alias = null, $fkeyname = null)
    {
        $froms = $this->getFromPart();
        $from = reset($froms);

        $fkeyname = $fkeyname ?: $from['fkeyname'];

        $mapper = [];
        if ($from['condition']) {
            $mapper = array_merge($mapper, array_each($from['condition'], function (&$carry, $v) {
                foreach ($v as $c => $p) {
                    $carry[$c] = $p;
                }
            }, []));
        }
        if ($fkeyname !== '') {
            $mapper = array_merge($mapper, $this->database->getSchema()->getForeignColumns($table, $from['table'], $fkeyname));
        }

        if (!$mapper) {
            if (func_num_args() === 3) {
                throw new \UnexpectedValueException("has not foreign key between '{$table}' and '{$from['table']}' ($fkeyname).");
            }
            return false;
        }

        $pre_p = $alias ?: $table;
        $pre_c = $from['alias'];
        $this->andWhere(array_sprintf($mapper, "$pre_c.%2\$s = $pre_p.%1\$s"));
        return true;
    }

    /**
     * 各種設定メソッドへのプロクシメソッド
     *
     * - see {@link column()}
     * - see {@link where()}
     * - see {@link orderBy()}
     * - see {@link limit()}
     * - see {@link groupBy()}
     * - see {@link having()}
     *
     * @param array $queryParts 句毎の連想配列
     * @param bool $append true を指定すると現状の状態が維持される。 false を指定するとクリアされる
     * @return $this 自分自身
     */
    public function build($queryParts, $append = false)
    {
        if (array_key_exists('column', $queryParts) && $queryParts['column']) {
            $this->{($append ? 'add' : '') . 'column'}($queryParts['column']);
        }
        if (array_key_exists('where', $queryParts) && $queryParts['where']) {
            $this->{($append ? 'and' : '') . 'where'}($queryParts['where']);
        }
        if (array_key_exists('orderBy', $queryParts) && $queryParts['orderBy']) {
            $this->{($append ? 'add' : '') . 'orderBy'}($queryParts['orderBy']);
        }
        if (array_key_exists('limit', $queryParts) && $queryParts['limit']) {
            $this->limit($queryParts['limit']);
        }
        if (array_key_exists('groupBy', $queryParts) && $queryParts['groupBy']) {
            $this->{($append ? 'add' : '') . 'groupBy'}($queryParts['groupBy']);
        }
        if (array_key_exists('having', $queryParts) && $queryParts['having']) {
            $this->{($append ? 'and' : '') . 'having'}($queryParts['having']);
        }

        return $this;
    }

    /**
     * select オプションを追加する
     *
     * SELECT オプションとは「SELECT 句のカラム群の前に（カラムとは区別されて）置かれる文字列」のこと。
     * 典型的には DISTINCT や STRAIGHT_JOIN など。
     *
     * ```php
     * $qb->addSelectOption(SelectOption::SQL_CACHE)->column('test');
     * // SELECT SQL_CACHE test.* FROM test
     * ```
     *
     * @param string|SelectOption $option 'SQL_CALC_FOUND_ROWS' とか 'STRAIGHT_JOIN' とか。
     * @return $this 自分自身
     */
    public function addSelectOption($option)
    {
        if (!$option) {
            return $this;
        }

        if (!in_array($option, $this->sqlParts['option'])) {
            $this->sqlParts['option'][] = $option;
        }
        return $this->_dirty();
    }

    /**
     * [table => [col1, col2]] のような指定を出来るようにする（クリア版）
     *
     * かなり多彩な指定ができる（複雑とも言う）。
     *
     * ```php
     * $qb->column([
     *     'table1' => [
     *         'aliasA'  => 'columnA',
     *         'aliasB'  => 'columnB',
     *         '+table2'  => [
     *             'aliasA' => 'columnA',
     *             'aliasB' => 'columnB',
     *         ],
     *         'table3' => [
     *             'aliasA' => 'columnA',
     *             'aliasB' => 'columnB',
     *         ],
     *     ],
     *     'table4' => [
     *         'aliasA' => 'columnA',
     *         'aliasB' => 'columnB',
     *     ],
     * ]);
     * ```
     *
     * 上記が基本構文となる。原則的には「取得したいテーブルの配下にカラムを置く」になる。
     * ネストさせると JOIN あるいは子テーブル取得になる（先頭の JOIN 記号で区別する）。
     * 並列に並べると JOIN ではなく複数の FROM になる。
     *
     * 上記で言えば table1, table4 を駆動表として、table2 を JOIN、table3 を子テーブルとして取得する、というクエリになる。
     *
     * 実際は怠惰に文字列だけで指定できたり、糖衣構文が多数存在する。そもそも「テーブル名」を書く場所にテーブル記法が使えたりする。
     * ざっくりと記法を一覧したものが下記（No が飛んでいるのに深い意味はない）。
     *
     * | No | type                                             | 説明
     * | --:|:--                                               |:--
     * |  0 | `['cond1', 'cond2' => 1]`                        | ネスト配列に素の配列を混ぜると JOIN 条件扱い
     * |  1 | `"**"`                                           | 子テーブルを含めた全テーブル全列を取得
     * |  2 | `null`                                           | null を与えると「何も取得しない」を明示
     * |  4 | `"!hoge"`                                        | hoge 列**以外**を取得
     * |  8 | `"..hoge"`                                       | subtable 時において親のカラムを表す
     * | 10 | `"+prefix.column_name"`                          | JOIN 記号＋ドットを含む文字列は prefix テーブルと JOIN してそのカラムを取得
     * | 11 | `new Expression("NOW()")`                        | {@link Expression} を与えると一切加工せずそのまま文字列を表す
     * | 12 | `"NOW()"`                                        | 上と同じ。 `()` を含む文字列は自動で {@link Expression} 化される
     * | 20 | `new PhpExpression($val)`                        | 列の値として {@link PhpExpression} でラップされた値をそのまま返す
     * | 21 | `new PhpExpression(function($row){})`            | 列の値として {@link PhpExpression} でラップされたクロージャの返り値を返す。クロージャの引数は行配列
     * | 22 | `function($row){}`                               | 上と同じ。クロージャは自動で {@link PhpExpression} 化される
     * | 23 | `new PhpExpression(function($cname){}, 'cname')` | PhpExpression に第2引数を指定すると列の値として {@link PhpExpression} でラップされたクロージャの返り値を返す。引数は**第2引数で指定された列の値**
     * | 24 | `function($cname = 'cname'){}`                   | 上と同じ。引数にデフォルト値が指定されている場合はそのデフォルト値が上で言う「指定された列」に相当する。
     * | 25 | `['cname' => function($value = null){}]`         | 上と同じ。引数のデフォルト値が null の場合はキーの値が使用される
     * | 26 | `function(){return function($v){return $v;};}`   | クロージャの亜種。クロージャを返すクロージャはそのままクロージャとして活きるのでメソッドのような扱いにできる
     * | 30 | `Gateway object`                                 | Gateway の表すテーブルとの {@link Database::subtable()} 相当の動作
     * | 31 | `['+alias' => Gateway object]`                   | Gateway の表すテーブルとの JOIN を表す
     * | 50 | `'TableDescriptor'`                              | 「テーブル名」を書く場所にはテーブル記法が使用できる（駆動表）
     * | 51 | `['+TableDescriptor' => ['*']]`                  | 「テーブル名」を書く場所にはテーブル記法が使用できる（JOIN）
     * | 80 | `SelectOption::DISTINCT()`                       | SelectOption インスタンスを与えると `addSelectOption` と同等の効果を示す
     * | 99 | `['' => ['expression']]`                         | 空キーは「テーブルに紐付かないカラム指定」を表す
     *
     * 上記の通り、尋常ではないほど複雑なのでサンプルコードを以下に記す。
     *
     * ```php
     * # No.0： 素の配列を混ぜると JOIN 条件になる（形式は where と全く同じ）
     * $qb->column([
     *     't_article A.*' => [
     *         '+t_comment: C.*' => [ // 外部キーがあると自動で ON が付くので、外すことを明示するために: が必要
     *             // 素の配列はそのテーブルと親テーブル（この場合 t_article と t_comment） の結合条件になる
     *             ['A.article_id = C.article_id', 'C.delete_flg' => 0],
     *         ],
     *     ],
     * ]);
     *
     * # No.1： t_ancestor に紐づく t_parent に紐づく t_child を怠惰に取得（*の数だけ子リレーションを辿る）
     * $qb->column('t_ancestor.***');
     *
     * # No.2： null は何も取得しないがキーは活きる（t_article に紐づくリレーションを怠惰に取得したいが、特定テーブルは除きたい場合など）
     * $qb->column([
     *     't_article' => [
     *         '***',
     *         't_imgblob' => null,
     *         't_hugelog' => null,
     *     ],
     * ]);
     *
     * # No.4： ! を付けるとそのテーブル内でそれ以外を取得する
     * $qb->column([
     *     't_article' => [
     *         '!content', // 例えば一覧画面でデータ量の大きい本文を取得したくないときなど
     *     ],
     * ]);
     *
     * # No.8： "..hoge" で subtable における親カラムを参照できる
     * $qb->column([
     *     't_article' => [
     *         '*',
     *         't_comment C' => [
     *             '..article_title',             // 子テーブルのコンテキストで親のカラムが参照できる
     *             'atitle' => '..article_title', // 全く同じ。エイリアスも貼れる
     *         ],
     *     ],
     * ]);
     *
     * # No.10： 自動プレフィックス JOIN
     * $qb->column([
     *     't_comment' => [
     *         // このように値に join 記号＋テーブル.カラムを置くと自動で JOIN される
     *         '+t_article.article_title',
     *         '+t_article.tags',
     *         // このように2つ並べても同テーブルであれば JOIN されるのは1回のみ
     *     ],
     * ]);
     *
     * # No.11, 12： Expression
     * $qb->column([
     *     't_article' => [
     *         'upper_title' => new Expression('UPPER(article_title)'), // タイトルを大文字で取得
     *         'upper_title' => 'UPPER(article_title)',                 // 全く同じ。カッコを含めば自動で Expression 化される
     *     ],
     * ]);
     *
     * # No.20 ～ 25： PhpExpression（他の用法は PhpExpression クラスのリファレンスを参照）
     * $qb->column([
     *     't_article' => [
     *         'phpval' => new PhpExpression('hoge'), // "hoge" という文字列を取得
     *     ],
     * ]);
     * # No.26： クロージャを返すクロージャ
     * $tuple = $qb->column([
     *     't_article.*' => [
     *         // クロージャ内の $this は行そのものを表す ArrayAccess なオブジェクトで bind される
     *         'func' => function(){return function($prefix){return $prefix . $this['name'];};},
     *     ],
     * ])->tuple();
     * // 'func' にはクロージャが格納されているので呼び出しが可能
     * $tuple['func']('prefix-'); // => 'prefix-hogehoge'
     *
     * # No.30, 31：配列で指定する箇所は Gateway も指定できる
     * $qb->column([
     *     't_article' => [
     *         'comments1'  => $db->t_comment, // t_comment を子テーブルとして取得する
     *         '+comments2' => $db->t_comment, // t_comment と JOIN される
     *     ],
     * ]);
     *
     * # No.50, 51：テーブル記法
     * $qb->column('t_article(1)'); // 主キー = 1 と同じ
     * $qb->column([
     *     // 駆動表にも使えるし
     *     't_article(1) AS A' => [
     *         // JOIN 表にも使える
     *         '+t_comment@scope[state: active] AS C' => ['*'],
     *     ]
     * ]);
     * // 応用。アクティブな記事をID昇順で10件取り、そのそれぞれのコメントを作成日降順で3件ずつ取る（いわゆるグループ内のN件取得）
     * $qb->column([
     *     't_article[state: active]+id#0-10 AS A.*' => [
     *         't_comment-create_date#0-3 AS C.*' => []
     *     ],
     * ]);
     *
     * # No.80：SelectOption を与える
     * $qb->column([
     *     't_article' => [
     *         SelectOption::DISTINCT(),
     *         '*',
     *     ],
     * ]);
     *
     * # No.99： テーブルに紐付かないカラム指定（勝手に修飾されたり JOIN されたりせず、シンプルに SELECT 句に追加される）
     * $qb->column([
     *     't_table' => '*',
     *     '' => [
     *         'now' => 'NOW()',
     *         'ttc' => 't_table.colA',                 // 修飾子として動作する
     *         'ope' => ['column_name:LIKE' => 'hoge'], // operator として動作する
     *     ],
     * ]);
     * ```
     *
     * @param array|string $tableDescriptor テーブル名
     * @return $this 自分自身
     */
    public function column($tableDescriptor)
    {
        return $this->resetQueryPart(['select', 'from', 'join', 'where', 'orderBy'])->addColumn($tableDescriptor);
    }

    /**
     * [table => [col1, col2]] のような指定を出来るようにする（{@link column()} の追加版）
     *
     * @inheritdoc column()
     */
    public function addColumn($tableDescriptor, $parent = null)
    {
        $schema = $this->database->getSchema();
        foreach (TableDescriptor::forge($this->database, $tableDescriptor) as $descriptor) {
            // カラム配列
            $columns = [];
            $ignores = [];

            // カラム修飾子
            $prefix = $descriptor->descriptor ? $descriptor->accessor . '.' : '';

            foreach ($descriptor->column as $key => $column) {
                // 仮想カラム
                if ($descriptor->table && $column !== null) {
                    $vcolumn = optional($this->database->{$descriptor->table})->virtualColumn($column, 'expression');
                    if ($vcolumn) {
                        $key = is_int($key) ? $column : $key;
                        // 仮想カラムは修飾子を付与するチャンスを与えなければ実質使い物にならない（エイリアスが動的だから）
                        if (is_string($vcolumn)) {
                            $vcolumn = sprintf($vcolumn, $descriptor->accessor);
                        }
                        $column = $vcolumn;
                    }
                }

                // Expression 化出来そうならする
                $column = Expression::forge($column);
                $column = PhpExpression::forge($column, $key);

                // テーブルに紐付かない列指定で配列指定は operator(Expression 化) する
                if (!$descriptor->table && is_array($column)) {
                    $column = $this->database->operator($column);
                }

                // null はダミーとして扱い、取得しない
                if ($column === null) {
                    // dummy
                }
                // SelectOption は単純に addSelectOption するだけ
                elseif ($column instanceof SelectOption) {
                    $this->addSelectOption($column);
                }
                // Alias はそのまま
                elseif ($column instanceof Alias) {
                    $columns[] = $column;
                }
                // 配列や Gateway なら subtable 化
                elseif (is_array($column) || $column instanceof TableGateway) {
                    // Gateway ならパースの必要はない
                    if ($column instanceof TableGateway) {
                        $parsed = stdclass([
                            'table'    => $column->tableName(),
                            'alias'    => $column->alias(),
                            'accessor' => $column->modifier(),
                            'fkeyname' => $column->foreign(),
                        ]);
                        $build_params = $column->getScopeParams([]);
                        $key = is_int($key) ? $column->modifier() : $key;
                    }
                    // 配列なら key をパース
                    else {
                        $parsed = new TableDescriptor($this->database, $key, []);
                        $build_params = ['column' => [$key => array_merge($parsed->column, $column)]];
                        $key = $parsed->alias ?: $this->database->convertEntityName($parsed->table) . $parsed->fkeysuffix;
                    }

                    $fcols = $schema->getForeignColumns($descriptor->table, $parsed->table, $parsed->fkeyname);
                    $columns[] = array_strpad($fcols, '', $prefix);

                    $subtable = $this->database->createSubqueryBuilder();
                    $subtable->build($build_params)->lazy('select', array_strpad($fcols, $parsed->accessor . '.'));

                    // 「主キーから外部キーを差っ引いたものが空」はすなわち「親との結合」とみなすことができる
                    // その場合 assoc は無駄だし、assoc のためのカラムを追加する必要もない
                    $pk = $schema->getTablePrimaryKey($parsed->table);
                    $jk = array_diff($pk->getColumns(), array_keys($fcols));
                    if (!$jk) {
                        $this->subbuilders[$key] = $subtable->tuple();
                    }
                    else {
                        $jk = array_strpad($jk, '', $parsed->accessor . '.');
                        $subtable->_addPrimary(Database::AUTO_PRIMARY_KEY, $jk, true);
                        $this->subbuilders[$key] = $subtable->assoc();
                    }
                }
                // PhpExpression なら phpExpressions に追加するだけ
                elseif ($column instanceof PhpExpression) {
                    $columns[] = $column->getDependColumns();
                    $this->phpExpressions[$key] = $column;
                }
                // SubqueryBuilder ならクエリ化せずに subbuilders に追加するだけ
                elseif ($column instanceof SubqueryBuilder) {
                    // ビルド保留状態ならここでビルドする
                    if ($dparam = $column->getDelayedParams()) {
                        $parsed = new TableDescriptor($this->database, $key, []);

                        $dparam['column'] = [$key => array_merge($parsed->column, arrayize($dparam['column']))];
                        $fcols = $schema->getForeignColumns($descriptor->table, $parsed->table, $parsed->fkeyname);
                        $column->build($dparam)->lazy('select', array_strpad($fcols, $parsed->accessor . '.'));

                        $key = $parsed->alias ?: $this->database->convertEntityName($parsed->table) . $parsed->fkeysuffix;
                        $add_columns = $fcols;
                    }
                    else {
                        $add_columns = $column->setLazyColumnFrom($descriptor->table);
                    }

                    $columns[] = array_strpad($add_columns, '', $prefix);
                    $this->subbuilders[$key] = $column;
                }
                // QueryBuilder なら文字列化したものをカッコつきで select + パラメータ追加
                elseif ($column instanceof QueryBuilder) {
                    // サブクエリで order は無意味
                    $column->setAutoOrder(false);

                    // subexists はこの段階で where が確定する
                    if (($submethod = $column->getSubmethod()) !== null) {
                        $column->setSubwhere($descriptor->table, $descriptor->alias);
                        if (is_bool($submethod)) {
                            $column = $this->database->getCompatiblePlatform()->convertSelectExistsQuery($column);
                        }
                    }
                    $columns[] = Alias::forge($key, $column->getQuery());
                    $this->addParam($column->getParams(), 'select');
                    if (!is_int($key)) {
                        $this->sqlParts['colval'][$prefix . $key] = $column->getQuery();
                    }
                }
                // Expression なら文字列化したものをそのまま select
                elseif ($column instanceof Expression) {
                    $columns[] = Alias::forge($key, $column);
                    $this->addParam($column->getParams(), 'select');
                    if (!is_int($key)) {
                        $this->sqlParts['colval'][$prefix . $key] = $column;
                    }
                }
                // ! プレフィクスなら「それ以外のカラム」
                elseif (is_string($column) && $column[0] === '!') {
                    $ignores[] = ltrim($column, '!');
                }
                // .. プレフィクスなら「親カラムの参照」（識別のために $prefix を付与しないで追加する）
                elseif (is_string($column) && strpos($column, '..') === 0) {
                    $columns[] = Alias::forge($key, $column);
                }
                // 上記以外。文字列として扱う
                else {
                    foreach (split_noempty(',', (string) $column) as $col) {
                        // エイリアスをバラす
                        list($key, $col) = Alias::split($col, $key);
                        $columns[] = Alias::forge($key, $prefix . $col);
                        if (!is_int($key)) {
                            $this->sqlParts['colval'][$prefix . $key] = $col;
                        }
                    }
                }
            }

            // 上の過程で配列ごと突っ込んでいる箇所があるのでフラットにする
            $columns = array_flatten($columns);

            // ignore 指定があるなら describe してそれ以外のカラムをマージ
            if ($ignores) {
                $allcolumns = array_keys($schema->getTableColumns($descriptor->table));

                // 無視しようとしているカラムが存在しない場合（テーブル定義を変更した時とかの対策）
                if (array_intersect($ignores, $allcolumns) !== $ignores) {
                    throw new \UnexpectedValueException('some columns are not found (' . implode(', ', $ignores) . ').');
                }

                $allcolumns = array_diff($allcolumns, $ignores, array_map_method($columns, 'getAlias', [], true));
                $columns = array_merge(array_strpad($allcolumns, '', $prefix), $columns);
            }

            $this->sqlParts['select'] = array_merge($this->sqlParts['select'], array_unique($columns));

            // テーブル未指定ならカラムが確定したこの時点で終わり
            if (!$descriptor->table) {
                continue;
            }

            $this->from($descriptor->table, $descriptor->alias, $descriptor->jointype, $descriptor->condition, $descriptor->fkeyname, $parent);

            if ($descriptor->order) {
                $this->addOrderBy(array_strpad($descriptor->order, $prefix));
            }
            if ($descriptor->offset || $descriptor->limit) {
                $this->limit($descriptor->limit, $descriptor->offset);
            }

            if ($descriptor->scope) {
                $gateway = $this->database->{$descriptor->table}->clone();
                $gateway->as($descriptor->alias);
                foreach ($descriptor->scope as $sname => $sarg) {
                    $gateway->scope($sname, ...$sarg);
                }
                $sparam = $gateway->getScopeParams([]);
                $scolumn = array_unset($sparam, 'column');
                $this->addSelect(reset($scolumn));
                $this->build($sparam, true);
            }

            foreach ($descriptor->jointable as $join) {
                /** @var TableDescriptor $join */
                $jointable = $join->descriptor;
                $jcondition = $join->condition;

                if ($jointable instanceof TableGateway) {
                    $this->hint($jointable->hint(), $jointable->modifier());
                    $joinable = $jointable->joinize();
                    if (isset($joinable['order'])) {
                        $this->joinOrders[] = $joinable['order'];
                        $jointable = $joinable['table'];
                        $jointable[] = $joinable['condition'];
                    }
                    else {
                        $jcondition = array_merge($join->condition, $joinable['condition']);
                        $jointable = $joinable['table'];
                    }
                }

                if ($jointable instanceof QueryBuilder) {
                    $this->from($jointable, $join->accessor, $join->jointype, $jcondition, $join->fkeyname, $parent);
                }
                else {
                    $this->addColumn([$join->key => $jointable], $descriptor->accessor);
                }
            }
        }

        // from が無い subselect 指定は無効
        if (count($this->sqlParts['from']) === 0 && count($this->subbuilders) > 0) {
            throw new \InvalidArgumentException('column is not possible to specify only children.');
        }

        return $this->_dirty();
    }

    /**
     * select 列を設定する（クリア版）
     *
     * @param array $selects select 列
     * @return $this 自分自身
     */
    public function select(...$selects)
    {
        $this->sqlParts['select'] = [];
        return $this->addSelect(...$selects);
    }

    /**
     * select 列を設定する（{@link select()} の追加版）
     *
     * @param array $selects select 列
     * @return $this 自分自身
     */
    public function addSelect(...$selects)
    {
        foreach ($selects as $select) {
            $this->addColumn(['' => $select]);
        }
        return $this;
    }

    /**
     * FROM 句（JOIN 込）を構成する
     *
     * 結合タイプや結合条件をまとめて指定して FROM, JOIN を構成できるが、複雑極まりないので使用は非推奨（FROM 句の設定は {@link column()} を使用すれば基本的に不要）。
     *
     * @param string|array|QueryBuilder $table 対象テーブル
     * @param string $alias テーブルエイリアス
     * @param string $type INNER, LEFT などの JOIN タイプ
     * @param array|string $condition 結合条件。 {@link where()} と同じ形式が使える
     * @param string $fkeyname 外部キー名
     * @param string $fromAlias 結合させるテーブル。省略時は自動判別
     * @return $this 自分自身
     */
    public function from($table, $alias = null, $type = null, $condition = [], $fkeyname = null, $fromAlias = null)
    {
        $froms = array_lookup($this->getFromPart(), 'table');
        $schema = $this->database->getSchema();

        // $table, $alias の解決（配列・ビルダ・文字列を受け入れる）
        if (is_array($table)) {
            list($alias, $table) = first_keyvalue($table);
        }
        if (!$alias && is_string($table)) {
            list($alias, $table) = Alias::split($table, $alias);
        }
        if ($table instanceof Queryable) {
            if ($alias === null) {
                $alias = '__dbml_auto_from_' . count($froms);
            }
        }

        // $fkeyname, $fromAlias の解決（大抵はどちらか一方が決まればどちらか一方も決まる）
        if (empty($fromAlias)) {
            if ($fkeyname !== '') {
                $fkey = $schema->getForeignTable($fkeyname);
                if ($fkey) {
                    list($local, $foreign) = first_keyvalue($fkey);
                    if ($table === $local) {
                        $fromAlias = array_search($foreign, $froms, true);
                    }
                    elseif ($table === $foreign) {
                        $fromAlias = array_search($local, $froms, true);
                    }
                }
                else {
                    foreach (array_reverse($froms, true) as $falias => $from) {
                        if (is_object($from)) {
                            continue;
                        }
                        $fromAlias = $falias;
                        if ($schema->getForeignColumns($table, $from, $fkeyname)) {
                            break;
                        }
                    }
                }
            }
            else {
                end($froms);
                $fromAlias = key($froms);
            }
        }
        $fromTable = $froms[$fromAlias] ?? null;
        $joinAlias = $alias ?: $table;

        // 外部キーの解決
        $fcols = [];
        $direction = null;
        if ($type !== null && $fkeyname !== '') {
            $fcols = $schema->getForeignColumns($table, $fromTable, $fkeyname, $direction) ?: [];
            if (!$fcols && $fromTable !== null && strlen($fkeyname)) {
                throw new \UnexpectedValueException("foreign key '$fkeyname' is not exists between $table<->$fromTable.");
            }
        }

        // $condition の解決
        $condition = arrayize($condition);
        $stdclasses = array_unset($condition, function ($v) { return $v instanceof \stdClass; }, []);

        if ($fromAlias) {
            foreach ($stdclasses as $n => $cond) {
                $condition = array_merge(array_sprintf((array) $cond, function ($v, $k) use ($joinAlias, $fromAlias) {
                    return sprintf('%s.%s = %s.%s', $joinAlias, is_int($k) ? $v : $k, $fromAlias, $v);
                }), $condition);
            }
        }
        $condition = array_merge(array_sprintf($fcols, function ($v, $k) use ($joinAlias, $fromAlias) {
            return sprintf('%s.%s = %s.%s', $joinAlias, $v, $fromAlias, $k);
        }), Adhoc::modifier($joinAlias, $condition));

        // $type の解決
        if (strcasecmp($type, 'AUTO') === 0) {
            $type = $condition ? 'LEFT' : null;
            if ($fcols) {
                $cols1 = array_flip(array_values($fcols));
                $cols2 = $fcols;

                $nullable = static function (Column $c) { return !$c->getNotnull(); };
                $join_nullable = array_find(array_intersect_key($schema->getTableColumns($table), $cols1), $nullable);
                $from_nullable = array_find(array_intersect_key($schema->getTableColumns($fromTable), $cols2), $nullable);
                // 4パターンで inner,left,right,full に対応してもいいけど、旨味が少ない（勝手に right されても使い勝手が悪い）上、full 対応の DBMS は少ない
                if ($join_nullable || $from_nullable) {
                    $type = 'LEFT';
                }
                elseif ($direction === true) {
                    $type = 'LEFT';
                }
                else {
                    $type = 'INNER';
                }
            }
        }

        $isfrom = $type === null || (empty($fromAlias) && empty($froms));
        if ($table instanceof Queryable) {
            $this->addParam($table->getParams(), $isfrom ? 'from' : 'join');
        }

        if ($isfrom) {
            $this->sqlParts['from'][] = [
                'table'     => $table,
                'alias'     => $alias,
                'fkeyname'  => $fkeyname,
                'condition' => $stdclasses,
            ];
            return $this->andWhere($condition);
        }

        $qb = new QueryBuilder($this->database);
        $qb->sqlParts = $this->sqlParts;
        $qb->where($condition);
        if (empty($qb->sqlParts['where'])) {
            throw new \InvalidArgumentException("can't nocondition join $table<->$fromTable.");
        }
        $this->addParam($qb->params, 'join');

        $this->sqlParts['join'][$fromAlias][] = [
            'type'      => $type,
            'table'     => $table,
            'alias'     => $alias,
            'condition' => implode(' AND ', Adhoc::wrapParentheses($qb->sqlParts['where'])),
        ];

        return $this->_dirty();
    }

    /**
     * 結合タイプや結合条件、外部キーを指定して JOIN する
     *
     * 実際は下記のようなエイリアスメソッドが定義されているのでそちらを使うことが多く、明示的に呼ぶことはほとんどない。
     * さらに単純な JOIN であれば {@link column()} でも可能なため、ますます出番はない。
     *
     * ```php
     * # 指定条件で ON して JOIN
     * $qb->from('t_from')->innerJoinOn('t_join', ['hoge = fuga']);
     * // SELECT  FROM t_from INNER JOIN t_join ON hoge = fuga
     *
     * # 外部キーカラムで ON して JOIN
     * $qb->from('t_from')->innerJoinForeign('t_join', 'ForeignKeyName');
     * $qb->from('t_from')->innerJoinForeign('t_join'); // テーブル間外部キーが1つなら省略可能
     * // SELECT  FROM t_from INNER JOIN t_join ON t_from.foreign_col = t_join.foreign_col
     *
     * # 外部キーカラムと指定条件で ON して JOIN
     * $qb->from('t_from')->innerJoinForeignOn('t_join', ['hoge = fuga'], 'ForeignKeyName');
     * // SELECT  FROM t_from INNER JOIN t_join ON ((t_from.foreign_col = t_join.foreign_col) AND (hoge = fuga))
     * ```
     *
     * @used-by innerJoinOn()
     * @used-by leftJoinOn()
     * @used-by rightJoinOn()
     * @used-by autoJoinForeign()
     * @used-by innerJoinForeign()
     * @used-by leftJoinForeign()
     * @used-by rightJoinForeign()
     * @used-by autoJoinForeignOn()
     * @used-by innerJoinForeignOn()
     * @used-by leftJoinForeignOn()
     * @used-by rightJoinForeignOn()
     *
     * @param string $type 結合タイプ（CROSS, INNER, LEFT, RIGHT, AUTO）
     * @param string|array|QueryBuilder $table 結合するテーブル
     * @param string|array $on 結合条件。 {@link where()} と同じ形式が使える
     * @param string $fkeyname 外部キー名称。省略時は唯一の外部キーを使用（無かったり2個以上ある場合は例外）
     * @param string $from 結合させるテーブル。省略時は自動判別
     * @return $this 自分自身
     */
    public function join($type, $table, $on, $fkeyname = null, $from = null)
    {
        return $this->from($table, null, $type, $on, $fkeyname, $from);
    }

    /**
     * 引数内では AND、引数間では OR する where（クリア版）
     *
     * 基本は {@link Database::whereInto()} の where 記法と同じ。加えて下記の記法が使用できる。
     *
     * | No | where                   | 説明
     * | --:|:--                      |:--
     * | 30 | `['' => 123]`           | キーを空文字にすると駆動表の主キーを表す
     * | 31 | `['C' => 'childwhere']` | サブビルダの名前をキーにして配列を渡すと「その子供ビルダの where」を意味する
     * | 32 | `['C/childwhere']`      | サブビルダの名前を "/" で区切ると「その子供ビルダの where」を意味する
     * | 33 | `['*.delete_flg' => 1]` | テーブル部分に `*` を指定すると「あらゆるテーブルのそのカラム」を意味する
     * | 34 | `['*' => "hoge"]`       | `*` を指定すると「よしなに検索」となる。{@link Database::anywhere()} も参照
     *
     * ```php
     * # 引数配列内では AND、引数間では OR される
     * $qb->where(['hoge = 1', 'fuga = ?' => 1], ['piyo' => 1]); // WHERE (hoge = 1 AND fuga = 1) OR (piyo = 1)
     *
     * # No.30（空キーは駆動表の主キーを表す）
     * $qb->column('t_article')->where(['' => 123]);            // WHERE article_id = 123
     * $qb->column('t_article')->where(['' => [123, 456]]);     // WHERE article_id IN (123, 456)
     * $qb->column('t_multi')->where(['' => [123, 456]]);       // WHERE id1 = 123 AND id2 = 456
     * $qb->column('t_multi')->where(['' => [[1, 2], [3, 4]]]); // WHERE (id1 = 1 AND id2 = 2) OR (id1 = 3 AND id2 = 4)
     *
     * # No.31, 32（糖衣構文であり、できることは同じ）
     * $qb->column('t_parent P/t_child C')->where([
     *     // P(親)の WHERE
     *     'P.delete_time' => 0,
     *     // エイリアスキーを指定して配列でネストすると子ビルダの WHERE を意味する
     *     'C'             => [
     *         // C(子)の WHERE
     *         'C.approval_flg' => 1,
     *     ],
     *     // "/" 区切り。上記と全く同じ
     *     'C/approval_flg' => 1, // 修飾する場合は 'C/C.approval_flg'
     * ]);
     *
     * # No.33（例えば対象テーブルに delete_flg があり、 delete_flg = 0 を付与したい場合、下記のようにすると全てのテーブルに付与される）
     * $qb->column('table1 t1, table2 t2, table3 t3')->where(['*.delete_flg' => 0]); // WHERE (t1.delete_flg = 0) AND (t2.delete_flg = 0) AND (t3.delete_flg = 0)
     *
     * # No.34（table1, table2, table3 から "hoge" でよしなに検索する）
     * $qb->column('table1 t1, table2 t2, table3 t3')->where(['*.*' => 'hoge']); // テーブル定義次第だが、全テーブルのあらゆるテキスト系カラムで LIKE "%hoge%" される
     * $qb->column('table1 t1, table2 t2, table3 t3')->where(['t2.*' => 'hoge']); // "*.*" ではなく "エイリアス名.*" とすると全テーブルではなく指定したものだけよしなにされる
     * ```
     *
     * @param mixed $predicates 条件配列
     * @return $this 自分自身
     */
    public function where(...$predicates)
    {
        return $this->resetQueryPart('where')->_buildCondition('where', $predicates);
    }

    /**
     * NOT つきで引数内では AND、引数間では OR する where（クリア版）
     *
     * NOT が付くこと以外は {@link where()} と同じ。
     *
     * @inheritdoc where()
     */
    public function notWhere(...$predicates)
    {
        return $this->resetQueryPart('where')->_buildCondition('where', $predicates, false);
    }

    /**
     * 引数内では AND、引数間では OR する（{@link where()} の追加版）
     *
     * クリアされずに追加されること以外は {@link where()} と同じ。
     *
     * @inheritdoc where()
     */
    public function andWhere(...$predicates)
    {
        return $this->_buildCondition('where', $predicates);
    }

    /**
     * NOT つきで引数内では AND、引数間では OR する（{@link notWhere()} の追加版）
     *
     * クリアされずに追加されること以外は {@link notWhere()} と同じ。
     *
     * @inheritdoc where()
     */
    public function andNotWhere(...$predicates)
    {
        return $this->_buildCondition('where', $predicates, false);
    }

    /**
     * 引数内では AND、引数間では OR する having（クリア版）
     *
     * WHERE ではなく HAVING である点を除いて引数体系などは {@link where()} と同じ。
     *
     * @inheritdoc where()
     */
    public function having(...$predicates)
    {
        return $this->resetQueryPart('having')->_buildCondition('having', $predicates);
    }

    /**
     * NOT つきで引数内では AND、引数間では OR する having（クリア版）
     *
     * NOT が付くこと以外は {@link having()} と同じ。
     *
     * @inheritdoc having()
     */
    public function notHaving(...$predicates)
    {
        return $this->resetQueryPart('having')->_buildCondition('having', $predicates, false);
    }

    /**
     * 引数内では AND、引数間では OR する（{@link having()} の追加版）
     *
     * クリアされずに追加されること以外は {@link having()} と同じ。
     *
     * @inheritdoc having()
     */
    public function andHaving(...$predicates)
    {
        return $this->_buildCondition('having', $predicates);
    }

    /**
     * NOT つきで引数内では AND、引数間では OR する（{@link notHaving()} の追加版）
     *
     * クリアされずに追加されること以外は {@link notHaving()} と同じ。
     *
     * @inheritdoc having()
     */
    public function andNotHaving(...$predicates)
    {
        return $this->_buildCondition('having', $predicates, false);
    }

    /**
     * [col => ASC] のように指定できるように拡張した orderBy（クリア版）
     *
     * ORDER BY 句を設定する。
     * 渡し方が引数だったり配列だったりするのでややこしく見えるが、原則として {カラム名, 順序} のタプルを渡す。
     * 「カラム名」に特記事項はない。「順序」は 未指定, 'ASC', true などが昇順を表し、 'DESC', false などが降順を表す。
     *
     * ```php
     * # シンプルなカラム ORD
     * $qb->orderBy('col');         // ORDER BY col ASC
     * $qb->orderBy('col', true);   // ORDER BY col ASC
     * $qb->orderBy('col', 'ASC');  // ORDER BY col ASC
     * $qb->orderBy('col', false);  // ORDER BY col DESC
     * $qb->orderBy('col', 'DESC'); // ORDER BY col DESC
     *
     * # [col => ORD] 形式
     * $qb->orderBy(['colA' => 'ASC', 'colB' => false]);  // ORDER BY colA ASC, colB DESC
     * $qb->orderBy(['colA' => true, 'colB' => 'DESC']);  // ORDER BY colA ASC, colB DESC
     * $qb->orderBy(['colA', 'colB' => false]);           // ORDER BY colA ASC, colB DESC
     *
     * # [+col, -col] 形式
     * $qb->orderBy('+colA');            // ORDER BY colA ASC
     * $qb->orderBy(['-colA', '+colB']); // ORDER BY colA DESC, colB ASC
     *
     * # [col, col, col], ORD 形式
     * $qb->orderBy(['colA', 'colB', 'colC'], false);  // ORDER BY colA DESC, colB DESC, colC DESC
     *
     * # 配列指定で子ビルダを設定
     * $qb->column('t_parent P/t_child C')->orderBy([
     *     // P(親)の ORDER
     *     'P.parent_id' => 'ASC',
     *     // エイリアスキーを指定して配列でネストすると子ビルダの ORDER BY を意味する
     *     'C'           => [
     *         // C(子)の ORDER
     *         'C.comment_time' => 'DESC',
     *     ],
     *      // "/" 区切り。上記と全く同じ
     *     'C/comment_time' => 'DESC', // 修飾する場合は 'C/C.comment_time'
     * ]);
     * ```
     *
     * 特殊な機能として、SQL レイヤではなく、「取得後にアプリレイヤでソートする」機能があり、クロージャを渡すとそのような動作になる。
     *
     * ```php
     * # シンプルにクロージャを渡すと uasort される（$a, $b は行配列）
     * $qb->orderBy(function ($a, $b) {
     *      // アプリレイヤ で id 降順になる
     *      return $a['id'] - $b['id'];
     * });
     *
     * # 空文字キーで配列を渡すと https://arima-ryunosuke.github.io/php-functions/#ryunosuke\Functions\Package\Arrays::array_order() 相当の動作になる
     * $qb->orderBy([
     *     '' => [
     *         'colA' => misc,
     *         'colB' => misc,
     *         'colC' => misc,
     *     ],
     * ]);
     * ```
     *
     * @param mixed $sort キー
     * @param mixed $order ASC/DESC
     * @return $this 自分自身
     */
    public function orderBy($sort, $order = null)
    {
        return $this->resetQueryPart('orderBy')->addOrderBy(...func_get_args());
    }

    /**
     * [col => ASC] のように指定できるように拡張した {@link orderBy()}（追加版）
     *
     * @inheritdoc orderBy()
     */
    public function addOrderBy($sort, $order = null)
    {
        // クロージャは行自体の比較関数
        if ($sort instanceof \Closure) {
            $this->phpOrders = $sort;
        }
        // 配列は ['column' => 'order'] 形式
        elseif (is_array($sort)) {
            // 空文字キーは特殊で php レイヤーのルールとみなす
            if (isset($sort[''])) {
                $this->phpOrders = $sort[''];
                unset($sort['']);
            }
            foreach ($sort as $col => $ord) {
                if (is_string($col) && strpos($col, '/') !== false) {
                    list($col, $column) = explode('/', $col, 2);
                    $ord = [$column => $ord];
                }
                if (isset($this->subbuilders[$col])) {
                    $this->getSubbuilder($col)->addOrderBy($ord);
                }
                elseif (is_int($col)) {
                    $this->addOrderBy($ord, $order);
                }
                else {
                    $this->addOrderBy($col, $ord);
                }
            }
        }
        else {
            if (is_string($sort) && $order === null) {
                $order = $sort[0] !== '-';
                $sort = ltrim($sort, '-+');
            }
            if (is_bool($order)) {
                $order = $order ? 'ASC' : 'DESC';
            }
            $this->sqlParts['orderBy'][(string) $sort] = strtoupper($order) !== 'DESC';
        }

        return $this->_dirty();
    }

    /**
     * 実在するカラムやエイリアスをチェックするセキュアな orderBy
     *
     * - from,join 句にあるテーブルカラムの実名
     * - select 句にあるエイリアス名
     * - Expression インスタンス
     *
     * 以外は何もせずスルーされる。
     * その性質上、事前に実行しておくことは出来ない。
     *
     * ```php
     * // test に id カラムは存在するので order by id される
     * $db->select('test')->orderBySecure('id');
     *
     * // 修飾しても OK
     * $db->select('test')->orderBySecure('test.id');
     *
     * // Expression インスタンスは無条件で OK
     * $db->select('test.id AS hoge')->orderBySecure(new Expression('NOW()'));
     *
     * // test に hoge カラムは存在しないが id のエイリアスなので OK
     * $db->select('test.id AS hoge')->orderBySecure('hoge');
     *
     * // test に fuga カラムは存在せず、エイリアスもないため、このメソッドは何も行わない
     * $db->select('test.id as hoge')->orderBySecure('fuga');
     * ```
     *
     * エラーや例外が出ないので挙動が分かりにくいが、下手にエラーを出すと「攻撃が可能そう」に見えてしまうのでこのような動作にしている。
     *
     * @inheritdoc orderBy()
     */
    public function orderBySecure($sort, $order = null)
    {
        // 配列は再帰
        if (is_array($sort)) {
            foreach ($sort as $col => $ord) {
                if (is_int($col)) {
                    $this->orderBySecure($ord, $order);
                }
                else {
                    $this->orderBySecure($col, $ord);
                }
            }
            return $this;
        }

        // セキュア？
        if ($this->_isSecureColumn($sort)) {
            $this->addOrderBy($sort, $order);
        }

        return $this;
    }

    /**
     * 主キーによる orderBy
     *
     * @param bool $is_asc ASC なら true
     * @return $this 自分自身
     */
    public function orderByPrimary($is_asc = true)
    {
        if (!$frompart = $this->sqlParts['from']) {
            throw new \UnexpectedValueException('query builder is not set "from".');
        }

        $fromtable = reset($frompart);
        if (!$this->database->getSchema()->hasTable($fromtable['table'])) {
            return $this;
        }

        $primary = $this->database->getSchema()->getTablePrimaryKey($fromtable['table']);
        if ($primary === null) {
            return $this;
        }

        $columns = $primary->getColumns();

        $tablename = $fromtable['alias'] ?: $fromtable['table'];
        return $this->orderBy(array_strpad($columns, '', $tablename . '.'), $is_asc);
    }

    /**
     * [table => array(col1, col2)] のように指定できるように拡張した {@link groupBy()}（クリア版）
     *
     * ```php
     *  # シンプルにカラムを指定
     * $qb->groupBy('id1');          // GROUP BY id1
     * $qb->groupBy('id1', 'id2');   // GROUP BY id1, id2
     *
     * # 配列も指定できる。キーを与えるとテーブルプレフィックスになる
     * $qb->groupBy(['id1', 'id2']);          // GROUP BY id1, id2
     * $qb->groupBy(['T' => ['id1', 'id2']]); // GROUP BY T.id1, T.id2
     *
     * # 配列指定で子ビルダを設定
     * $qb->column('t_parent P/t_child C')->groupBy([
     *     // P(親)の GROUP BY
     *     'parent_id',
     *     // エイリアスキーを指定して配列でネストすると子ビルダの GROUP BY を意味する
     *     'C'           => [
     *         // C(子)の GROUP BY
     *         'child_id',
     *     ],
     *      // "/" 区切り。上記と全く同じ
     *     'C/child_id', // 修飾する場合は 'C/C.child_id'
     * ]);
     * ```
     *
     * @param mixed $groupBy GROUP BY 句
     * @return $this 自分自身
     */
    public function groupBy($groupBy)
    {
        return $this->resetQueryPart('groupBy')->addGroupBy(...func_get_args());
    }

    /**
     * [table => [col1, col2]] のように指定できるように拡張した {@link groupBy()}（追加版）
     *
     * @inheritdoc groupBy()
     */
    public function addGroupBy($groupBy)
    {
        $current_args = Adhoc::argumentize(func_get_args());

        foreach ($current_args as $tbl => $arg) {
            foreach (arrayize($arg) as $col) {
                if (is_string($col) && strpos($col, '/') !== false) {
                    list($t, $col) = explode('/', $col, 2);
                    $this->getSubbuilder($t)->addGroupBy($col);
                }
                elseif (isset($this->subbuilders[$tbl])) {
                    $this->getSubbuilder($tbl)->addGroupBy($col);
                }
                elseif (is_int($tbl)) {
                    $this->sqlParts['groupBy'][] = $col;
                }
                else {
                    $this->sqlParts['groupBy'][] = $tbl . '.' . $col;
                }
            }
        }

        return $this->_dirty();
    }

    /**
     * (count, offset) or [offset => length] or [offset, length] のように指定できるようにする
     *
     * LIMIT OFFSET 句を設定する。
     *
     * ```php
     * # シンプルにスカラーで指定
     * $qb->limit(10);     // LIMIT 10
     * $qb->limit(10, 20); // LIMIT 10 OFFSET 20
     *
     * # 配列で指定（単純引数でも連想配列でも指定できる）。意味合いがスカラー指定と逆になっているので注意
     * $qb->limit([20, 10]);   // LIMIT 10 OFFSET 20
     * $qb->limit([20 => 10]); // LIMIT 10 OFFSET 20
     *
     * # 配列キー指定で子ビルダを設定
     * $qb->column('t_parent P/t_child C')->limit([
     *     // P(親)の LIMIT
     *     [0 => 20],
     *     // エイリアスキーを指定して配列でネストすると子ビルダの LIMIT を意味する
     *     'C' => [
     *         // C(子)の LIMIT
     *         [0 => 10],
     *     ],
     * ]);
     * ```
     *
     * @param int|array $count 最大件数、あるいは[オフセット => 最大件数]な配列
     * @param int $offset オフセット。オプショナル
     * @return $this 自分自身
     */
    public function limit($count, $offset = null)
    {
        if (is_array($count)) {
            foreach ($count as $sub => $args) {
                if (isset($this->subbuilders[$sub])) {
                    if (!is_array($args) || count($args) === 1) {
                        $this->getSubbuilder($sub)->limit($args);
                    }
                    else {
                        $this->getSubbuilder($sub)->limit(...$args);
                    }
                    unset($count[$sub]);
                }
            }
            $c = count($count);
            if ($c === 1) {
                list($offset, $count) = first_keyvalue($count);
            }
            elseif ($c === 2) {
                $offset = array_shift($count);
                $count = array_shift($count);
            }
            else {
                throw new \InvalidArgumentException('limit array length must be 1 or 2.');
            }
        }

        $this->sqlParts['offset'] = $offset === null ? null : (int) $offset;
        $this->sqlParts['limit'] = $count === null ? null : (int) $count;

        return $this->_dirty();
    }

    /**
     * ページ単位として LIMIT OFFSET する
     *
     * できることは {@link limit()} と同じ。指定の仕方が異なるだけ。
     * limit() は LIMIT, OFFSET をダイレクトに指定するが、こちらは一定の単位（ページ）で設定する。
     * 「LIMIT ありきで、それを元に OFFSET する」とも言える。
     *
     * ※ {@link Paginator} は全く関係ない
     *
     * ```php
     * # 10 件ごと 5 ページ目を設定
     * $qb->page(5, 10);        // LIMIT 10 OFFSET 50
     *
     * # あらかじめ limit 設定しておけば第2引数は省略できる
     * $qb->limit(10)->page(5); // LIMIT 10 OFFSET 50
     * ```
     *
     * @param int $page ページ数
     * @param int $limit 1ページの単位。省略時は現在の limit が使用される
     * @return $this 自分自身
     */
    public function page($page, $limit = null)
    {
        if ($limit === null) {
            $limit = $this->sqlParts['limit'];
            if ($limit === null) {
                return $this;
            }
        }
        return $this->limit($limit, $page * $limit);
    }

    /**
     * クエリに対してコメントを付ける
     *
     * コメントはクエリ冒頭に改行付きで付与される。複数設定した場合はその分付与される。
     * コメント内に : や ? などのメタ的な文字列を含んではならない。
     *
     * ```php
     * # 単一文字列は追加される
     * echo $qb->column('test')->comment('hoge')->comment('fuga');
     * // -- hoge
     * // -- fuga
     * // SELECT * FROM test
     *
     * # 配列は置換される
     * echo $qb->column('test')->comment('foo')->comment(['hoge', 'fuga']);
     * // -- hoge
     * // -- fuga
     * // SELECT * FROM test
     *
     * # 配列は置換されるので削除も行える
     * echo $qb->column('test')->comment([]);
     * // SELECT * FROM test
     * ```
     *
     * @param string|array $comment コメント文字列。文字列なら追加、配列ならダイレクトに代入される
     * @return $this 自分自身
     */
    public function comment($comment)
    {
        if (is_array($comment)) {
            $this->sqlParts['comment'] = $comment;
        }
        else {
            $this->sqlParts['comment'][] = $comment;
        }
        return $this->_dirty();
    }

    /**
     * UNION する
     *
     * UNION を行うと「UNION テーブルから自身のクエリビルダを利用して SELECT する」という動作になる。
     * 言い換えれば「自身の各句は UNION テーブルのためのものになる」となる。
     *
     * ```php
     * $qb = $db->select([
     *     // 後の union のための column 指定
     *     '' => ['title', 'content']
     * ], [
     *     // 後の union のための where 指定
     *     'status' => 'active',
     * ]);
     * // t_article と t_comment を union する（これが駆動表となる）
     * $qb->union($db->select('t_article'));
     * $qb->union($db->select('t_comment'));
     * echo $qb;
     * // SELECT title, content FROM
     * // (
     * //   SELECT t_article.* FROM t_article
     * //   UNION
     * //   SELECT t_comment.* FROM t_comment
     * // ) __dbml_union_table
     * // WHERE status = 'active'
     * ```
     *
     * @param string|QueryBuilder $query クエリ
     * @return $this 自分自身
     */
    public function union($query)
    {
        // 隠し引数 $isall
        $isall = func_num_args() === 2 ? func_get_arg(1) : false;

        foreach (arrayize($query) as $subq) {
            if ($subq instanceof QueryBuilder) {
                $this->addParam($subq->getParams(), 'union');
            }
            $this->sqlParts['union'][] = [$this->sqlParts['union'] ? ($isall ? 'UNION ALL' : 'UNION') : '', $subq];
        }

        return $this->_dirty();
    }

    /**
     * UNION ALL する
     *
     * ALL で UNION される以外は {@link union()} と全く同じ。
     *
     * @param string|QueryBuilder $query
     * @return $this 自分自身
     */
    public function unionAll($query)
    {
        return $this->union($query, true);
    }

    /**
     * '!' 付き条件で全てがフィルタされたかを返す
     *
     * @ignore
     *
     * @return bool 全条件が空なら true
     */
    public function isEmptyCondition()
    {
        // $this->emptyCondition は単純な bool ではないので !! する
        return !!$this->emptyCondition;
    }

    /**
     * COUNT(\*) クエリ化（厳密に言えば limit なしの COUNT(\*) 化）
     *
     * ```php
     * # status: 'active' の COUNT(*) を発行する
     * $qb->column('t_article')->where(['status' => 'active'])->countize();
     * // SELECT COUNT(*) AS __dbml_auto_cnt FROM t_article WHERE status = 'active'
     * ```
     *
     * @param string $column COUNT 対象カラム
     * @return $this 自分自身
     */
    public function countize($column = '*')
    {
        $that = clone $this;
        $this->setAutoOrder(false);
        $that->resetQueryPart('orderBy');
        $that->resetQueryPart('offset');
        $that->resetQueryPart('limit');

        // COUNT だけなのでこの辺は全部不要
        $that->subbuilders = [];
        $that->phpExpressions = [];
        $that->phpOrders = [];
        $that->caster = null;

        if ($that->sqlParts['groupBy'] || $that->sqlParts['having']) {
            if ($that->sqlParts['having']) {
                // having があるときは select をクリアすることは出来ない(select as alias が条件に使われているかもしれない)
                // が、消さないと mysql で「1060: Duplicate column name」が出る可能性がある
                // ひとまず安全を取り、消さない方向で↑が出たら呼び出し側で重複カラムを取り除く使い方とする
            }
            else {
                $that->resetQueryPart('select')->select('1');
            }
            $counter = $this->database->createQueryBuilder();
            $counter->select(new Alias(self::COUNT_ALIAS, "COUNT($column)"));
            $counter->from(['__dbml_auto_table' => $that]);
            $counter->params = $that->params;
        }
        else {
            $that->resetQueryPart('select');
            $counter = $that->select(new Alias(self::COUNT_ALIAS, "COUNT($column)"));
        }

        return $counter;
    }

    /**
     * new Paginator へのプロキシメソッド
     *
     * 引数が与えられている場合は {@link Paginator::paginate()} も同時に行う。
     *
     * @inheritdoc Paginator::paginate()
     *
     * @return Paginator Paginator オブジェクト
     */
    public function paginate($currentpage = null, $countperpage = null, $shownpage = null)
    {
        $p = new Paginator($this);
        if (func_num_args()) {
            $p->paginate(...func_get_args());
        }
        return $p;
    }

    /**
     * new Sequencer へのプロキシメソッド
     *
     * 引数が与えられている場合は {@link Sequencer::sequence()} も同時に行う。
     *
     * @inheritdoc Sequencer::sequence()
     *
     * @return Sequencer
     */
    public function sequence($condition, $count, $orderbyasc = true, $bidirection = true)
    {
        $p = new Sequencer($this);
        if (func_num_args()) {
            $p->sequence(...func_get_args());
        }
        return $p;
    }

    /**
     * 分割して sequence してレコードジェネレータを返す
     *
     * 例えば 150 件のレコードに対して chunk: 10 するとクエリを 15 回に分けて実行する。
     * そのそれぞれのクエリは別々であり、php 側 にも db 側にもバッファは作られないためメモリ効率が非常に良い（そのかわりそこまで高速ではない）。
     *
     * 一般的な実装と違い、下記の制限がある。
     *
     * - 数値主キーか、数値的な NOT NULL ユニークキーを持っている必要がある
     * - あらかじめ設定していても ORDER BY, LIMIT は無視される
     *
     * そのかわり chunk 内でレコードの更新を行ってもズレが発生しないようになっている。
     *
     * ```php
     * // 100 件ずつループする
     * foreach ($qb->where(['status' => 'active'])->chunk(100) as $row) {
     *     // do something
     *     var_dump($row['id']);
     * }
     * // SELECT * FROM t_table WHERE (status = "active") AND (id > 0) ORDER BY id ASC LIMIT 100
     * // SELECT * FROM t_table WHERE (status = "active") AND (id > 100) ORDER BY id ASC LIMIT 100
     * // SELECT * FROM t_table WHERE (status = "active") AND (id > 200) ORDER BY id ASC LIMIT 100
     * // ・・・のようなクエリが順次投げられる（Generator で返されるので分割されていることは意識しなくて良い）
     * ```
     *
     * @param int $chunk 分割数
     * @param string $column 基準カラム。省略時は AUTO_INCREMENT な主キー。 '-' プレフィックスを付けると降順になる
     * @return \Generator 分割して返す Generator
     */
    public function chunk($chunk, $column = null)
    {
        $from = first_value($this->getFromPart())['table'] ?? throws(new \UnexpectedValueException('from table is not set.'));
        $column = $column ?: strval(optional($this->database->getSchema()->getTableAutoIncrement($from))->getName() ?: throws(new \UnexpectedValueException('not autoincrement column.')));
        $orderasc = $column[0] !== '-';
        $column = ltrim($column, '-+');

        $sequencer = new Sequencer($this);
        $items = [];
        do {
            $n = end($items)[$column] ?? null;
            $sequencer->sequence([$column => $n], $chunk, $orderasc, null);
            $items = $sequencer->getItems();
            yield from $items;
        } while ($items);
    }

    /**
     * クエリの結果行数を返すようにするか
     *
     * @ignore
     *
     * @param bool $countable
     * @return $this
     */
    public function setRowCountable($countable)
    {
        $this->rowcount = $countable;
        return $this;
    }

    /**
     * クエリの結果行数を返す
     *
     * @ignore
     *
     * @return int|null クエリの結果行数
     */
    public function getRowCount()
    {
        if ($this->rowcount === true) {
            throw new \UnexpectedValueException('query is not executed yet.');
        }

        if (is_int($this->rowcount)) {
            return $this->rowcount;
        }

        return null;
    }

    /**
     * 行フェッチ後に QueryBuilder 特有の処理を行う
     *
     * ほぼ内部処理で明示的に呼ぶことはない。
     *
     * @param array $parents 親結果行
     * @param bool $continuity 連続でコールされる可能性があるか
     * @return array 特有の処理が行われた $parents
     */
    public function postselect($parents, $continuity = false)
    {
        // mysql の FOUND_ROWS のためにあらかじめ呼んでおく必要がある(途中にクエリが挟まると取得できなくなる)
        if ($this->rowcount === true) {
            $foundRowsQuery = $this->database->getCompatiblePlatform()->getFoundRowsQuery();
            $this->rowcount = false; // stop recursion
            $this->rowcount = intval($this->database->fetchValue($foundRowsQuery ?: $this->countize()));
        }

        // 親フェッチ行がないなら不要
        if (empty($parents)) {
            return $parents;
        }

        // フェッチ形式で色々異なるのでチェック用
        $first = reset($parents);
        $is_scalar = is_scalar($first) || is_null($first);

        // subselect
        if ($this->subbuilders) {
            // 親行がスカラーなのは何かがおかしい
            if ($is_scalar) {
                throw new \BadMethodCallException("parent is scalar value.");
            }

            // subselects 分ループ（多くても数個）
            foreach ($this->subbuilders as $column => $subselect) {
                // 連続コールされる場合は無駄なので clone もしないし prepare を使用する
                if ($continuity) {
                    $subselect->setLazyMode('fetch');
                }
                else {
                    $subselect = $subselect->getLazyClonable() ? clone $subselect : $subselect;
                }

                // 親のロックモードを受け継ぐ
                if ($this->lockMode !== null && $subselect->lockMode === null && $subselect->getPropagateLockMode()) {
                    $subselect->lockMode = $this->lockMode;
                    $subselect->lockOption = $this->lockOption;
                }
                $parents = $subselect->subquery($parents, $column);
            }
        }

        // evalute
        foreach ($parents as $n => $parent_row) {
            $row_class = null;
            foreach ($this->phpExpressions as $name => $column) {
                $excol = $column($parents[$n]);
                if ($excol instanceof \Closure) {
                    if ($row_class === null) {
                        if ($parents[$n] instanceof Entityable) {
                            $row_class = $parents[$n];
                        }
                        else {
                            $row_class = new \ArrayObject($parents[$n], \ArrayObject::ARRAY_AS_PROPS);
                        }
                    }
                    $excol = $excol->bindTo($row_class);
                }

                if ($is_scalar) {
                    $parents[$n] = $excol;
                }
                else {
                    $parents[$n][$name] = $excol;
                }
            }
        }

        // orderphp
        if (!$continuity && $this->phpOrders !== null) {
            if ($this->phpOrders instanceof \Closure) {
                uasort($parents, $this->phpOrders);
            }
            else {
                $parents = array_order($parents, $this->phpOrders, true);
            }
        }

        return $parents;
    }

    /**
     * 特定文字列でラップする
     *
     * 典型的には `EXISTS` だが、それ以外の「何らかの文字列で囲みたい」場合に汎用的に使用できる。
     * （もっとも、 `EXISTS` は専用メソッドが有るので使用頻度はそこまで高くない）。
     *
     * ラップ文字は追加で積み重なっていくが、第3引数（$name）を指定すると、種別を指定できて、後から上書きすることができる。
     *
     * ```php
     * $qb->column('t_article')->wrap('A', 'B');
     * // A (SELECT t_article.* FROM t_article) B
     *
     * # 種別なしでラップ（積み重ね）
     * $qb->column('t_article')->wrap('A', 'B')->wrap('C', 'D');
     * // C (A (SELECT t_article.* FROM t_article) B) D
     *
     * # 種別を hoge でラップ（上書き）
     * $qb->column('t_article')->wrap('A', 'B', 'hoge')->wrap('C', 'D', 'hoge');
     * // C (SELECT t_article.* FROM t_article) D
     * ```
     *
     * @param string $keyword1 ラップする文字列1
     * @param string $keyword2 ラップする文字列2
     * @param string $name ラップした登録名
     * @return $this 自分自身
     */
    public function wrap($keyword1, $keyword2 = '', $name = null)
    {
        array_set($this->wrappers, [$keyword1, $keyword2], $name, false);
        return $this->_dirty();
    }

    /**
     * EXISTS でラップして返す
     *
     * {@link wrap()} の特化メソッド。
     *
     * @return $this 自分自身
     */
    public function exists()
    {
        $this->sqlParts['select'] = ['*'];
        return $this->wrap('EXISTS', '', 'EXISTS');
    }

    /**
     * NOT EXISTS でラップして返す
     *
     * {@link wrap()} の特化メソッド。
     *
     * @return $this 自分自身
     */
    public function notExists()
    {
        $this->sqlParts['select'] = ['*'];
        return $this->wrap('NOT EXISTS', '', 'NOT EXISTS');
    }

    /**
     * 集約関数化する
     *
     * {@link Database::aggregate()} のためのヘルパーメソッドで、明示的には呼ばれないし呼ばない。
     *
     * @param string|array $aggregations 集約関数
     * @param int $select_limit カラム数制限
     * @return $this 自分自身
     */
    public function aggregate($aggregations, $select_limit = PHP_INT_MAX)
    {
        // 集約クエリで主キー順に意味は無い
        $this->setAutoOrder(false);

        // カラムとタプルのセットを取得しておく
        $fields = $this->sqlParts['select'] ?: ['*'];
        $tuples = array_each(arrayize($aggregations), function (&$carry, $aggregation) {
            $carry[] = [
                'method'    => "get{$aggregation}Expression",
                'aggregate' => $aggregation,
            ];
        }, []);

        // どちらかの最大数に合わせるように埋める
        $fields_count = count($fields);
        $tuples_count = count($tuples);
        if ($tuples_count === 0) {
            throw new \InvalidArgumentException('$aggregations is empty.');
        }
        if (max($fields_count, $tuples_count) > $select_limit) {
            throw new \InvalidArgumentException("aggregate column's length is over $select_limit.");
        }
        $fields = array_pad($fields, $tuples_count, end($fields));
        $tuples = array_pad($tuples, $fields_count, end($tuples));

        $platform = $this->database->getCompatiblePlatform();
        $delimiter = $this->getAggregationDelimiter();
        foreach ($fields as $n => $field) {
            $method = $tuples[$n]['method'];
            $aggregate = $tuples[$n]['aggregate'];
            $field = strpos($field, '*') === false ? $field : '*'; // for example: COUNT(TableName.*) -> COUNT(*)
            $fields[$n] = new Alias($field . $delimiter . $aggregate, $platform->$method($field));
        }
        $this->sqlParts['select'] = array_merge($this->sqlParts['groupBy'], $fields);

        return $this->_dirty();
    }

    /**
     * ヒント句を追加する
     *
     * 第2引数で紐づくテーブルを指定できるが、省略すると（その時点の）駆動表と紐づく。
     *
     * RDBMS の方言は吸収しないのでダイレクトに与える必要がある。
     * （一応 {@link \ryunosuke\dbml\Metadata\CompatiblePlatform::getIndexHintSQL()} に実装がある）。
     *
     * ```php
     * // SELECT * FROM tablename FORCE INDEX (PRIMARY)
     * $qb->column('tablename')->hint('FORCE INDEX (PRIMARY)');
     * ```
     *
     * @param string $hint ヒント構文
     * @param string $talias ヒント句を結びつけるテーブル or エイリアス
     * @return $this 自分自身
     */
    public function hint($hint, $talias = null)
    {
        if (!strlen($hint)) {
            return $this;
        }
        if ($talias === null) {
            $froms = $this->getFromPart();
            $talias = reset($froms)['alias'];
        }
        $this->sqlParts['hint'][$talias] = $hint;
        return $this->_dirty();
    }

    /**
     * 共有ロック構文を付与する
     *
     * $lockoption で付随するロックオプション（SKIP LOCKED とか）を指定できる（共有ロックで指定することはあまりないと思うけど）。
     * SKIP LOCKED などは有用だが方言がかなり激しく正規化が難しいので文字列で指定する。
     * ただし、 SqlServer は未対応（Doctrine 側が対応していない）。
     *
     * ```php
     * # 共有ロック（mysql）
     * $qb->column('t_article')->lockInShare();
     * // SELECT t_article.* FROM t_article LOCK IN SHARE MODE
     * ```
     *
     * @param string $lockoption ロックオプション
     * @return $this 自分自身
     */
    public function lockInShare($lockoption = null)
    {
        $this->lockMode = LockMode::PESSIMISTIC_READ;
        $this->lockOption = $lockoption;
        return $this->_dirty();
    }

    /**
     * 排他ロック構文を付与する
     *
     * $lockoption で付随するロックオプション（SKIP LOCKED とか）を指定できる。
     * SKIP LOCKED などは有用だが方言がかなり激しく正規化が難しいので文字列で指定する。
     * ただし、 SqlServer は未対応（Doctrine 側が対応していない）。
     *
     * ```php
     * # 排他ロック（mysql）
     * $qb->column('t_article')->lockForUpdate();
     * // SELECT t_article.* FROM t_article FOR UPDATE
     *
     * # オプション付き排他ロック（postgres や mysql 8.0）
     * $qb->column('t_article')->lockForUpdate('SKIP LOCKED');
     * // SELECT t_article.* FROM t_article FOR UPDATE SKIP LOCKED
     * ```
     *
     * @param string $lockoption ロックオプション
     * @return $this 自分自身
     */
    public function lockForUpdate($lockoption = null)
    {
        $this->lockMode = LockMode::PESSIMISTIC_WRITE;
        $this->lockOption = $lockoption;
        return $this->_dirty();
    }

    /**
     * ロックを解除する
     *
     * {@link lockInShare()} や {@link lockForUpdate()} で追加したロック構文を解除する。
     *
     * ```php
     * $qb->column('t_article');
     *
     * # 排他ロック
     * $qb->lockForUpdate();
     * // SELECT t_article.* FROM t_article FOR UPDATE
     *
     * # ロック解除
     * $qb->unlock();
     * // SELECT t_article.* FROM t_article
     * ```
     *
     * @return $this 自分自身
     */
    public function unlock()
    {
        $this->lockMode = null;
        $this->lockOption = null;
        return $this->_dirty();
    }

    /**
     * 自動主キーソートの有効無効を設定する
     *
     * `select` メソッドの第3引数、あるいは `orderBy` メソッドにて ORDER 句が指定されなかった場合、自動で駆動表の主キーカラムが設定される。
     *
     * この自動 ORDER はあくまで駆動表の主キーであり、 JOIN した結果の並び順は未定義である。
     * JOIN を含めて ORDER したい場合は明示的に `orderBy` するか、あるいは JOIN 狙いのインデックスを作るとか生クエリを書くとかそういった方面で対処しなければならない。
     *
     * autoOrder が無効ならそもそも無意味だが、その設定に関わらず下記のような場合は付加されない。
     *
     * - selectTuple, selectValue で取得した
     * - 集約関数が存在する
     * - `GROUP BY` 句が存在する
     * - `EXISTS` で包まれている
     *
     * @param bool $use 切り替えフラグ
     * @return $this 自分自身
     */
    public function detectAutoOrder($use)
    {
        if (!$this->getAutoOrder()) {
            return $this;
        }

        $current = $this->enableAutoOrder;
        $this->enableAutoOrder = $use;

        if ($current !== $this->enableAutoOrder) {
            $this->_dirty();
        }
        return $this;
    }

    /**
     * @ignore
     *
     * @param bool $enable
     * @return $this 自分自身
     */
    public function setAutoTablePrefix($enable)
    {
        if ($this->autoTablePrefix !== $enable) {
            $this->autoTablePrefix = $enable;
            $this->_dirty();
        }
        return $this;
    }

    /**
     * サブクエリビルダを返す
     *
     * ```php
     * $select = $db->select('t_parent/t_child');
     * # t_child のサブクエリビルダを返す
     * $select->getSubbuilder('t_child');
     * # 全サブクエリビルダを返す
     * $select->getSubbuilder();
     * ```
     *
     * このメソッドでサブビルダを取得すると、テーブル記法や簡易記法で記述した子供ビルダに対して各句を指定することができる。
     *
     * ```php
     * $qb->column([
     *     't_table' => [
     *         't_child AS children' => [],
     *     ],
     * ]);
     * $qb->getSubbuilder('children')->where(['delete_time' => 0])->orderBy(['update_time' => 'DESC'])->limit(5);
     * ```
     *
     * @param string $name キー。省略すると単一ではなく全サブクエリビルダを配列で返す
     * @return SubqueryBuilder|SubqueryBuilder[] サブクエリビルダ
     */
    public function getSubbuilder($name = null)
    {
        if ($name === null) {
            return $this->subbuilders;
        }
        if (!isset($this->subbuilders[$name])) {
            throw new \InvalidArgumentException("subbuilder '$name' is not defined.");
        }
        return $this->subbuilders[$name];
    }

    /**
     * FROM 句(from,join)を返す
     *
     * {@link getQueryPart()} でも得られるが、 FROM は JOIN も兼ねており、ややこしい構造になっているのでそれを補正しつつシンプルな構造で返す。
     *
     * ```php
     * $qb->column('t_article A + t_comment C');
     * $qb->getFromPart();
     * // result:
     * [
     *     'A' => [
     *         'from'      => null,
     *         'table'     => 't_article',
     *         'alias'     => 'A',
     *         'fkeyname'  => null,
     *         'condition' => [],
     *     ],
     *     'C' => [
     *         'from'      => 'A',
     *         'table'     => 't_comment',
     *         'alias'     => 'C',
     *         'fkeyname'  => null,
     *         'condition' => null,
     *         'type'      => 'INNER',
     *     ],
     * ];
     * ```
     *
     * @return array FROM 句配列
     */
    public function getFromPart()
    {
        $result = [];
        foreach ($this->sqlParts['from'] as $from) {
            $alias = $from['alias'] ?: $from['table'];
            $result[$alias] = [
                'from'      => null,
                'table'     => $from['table'],
                'alias'     => $alias,
                'fkeyname'  => $from['fkeyname'],
                'condition' => $from['condition'],
            ];
        }
        foreach ($this->sqlParts['join'] as $fromkey => $joins) {
            foreach ($joins as $join) {
                $alias = $join['alias'] ?: $join['table'];
                $result[$alias] = [
                    'from'      => $fromkey,
                    'table'     => $join['table'],
                    'alias'     => $alias,
                    'fkeyname'  => null,
                    'condition' => null,
                    'type'      => $join['type'],
                ];
            }
        }
        return $result;
    }

    /**
     * SQL の各句を返す
     *
     * @param string $queryPartName 句名
     * @return mixed 句
     */
    public function getQueryPart($queryPartName)
    {
        return $this->sqlParts[$queryPartName];
    }

    /**
     * SQL の各句をリセットする
     *
     * 引数でリセットする句を指定する。
     * 配列を与えると複数の句をリセットする。
     * null を与えると全句をリセットする。
     *
     * ```php
     * $qb = $db->select('t_article', ['article_id' => 1], ['article_id' => 'DESC'], 5);
     *
     * # where をリセット
     * $qb->resetQueryPart('where');
     * // SELECT t_article.* FROM t_article ORDER BY article_id DESC LIMIT 5
     *
     * # limit をリセット
     * $qb->resetQueryPart('limit');
     * // SELECT t_article.* FROM t_article ORDER BY article_id DESC
     *
     * # orderBy をリセット
     * $qb->resetQueryPart('orderBy');
     * // SELECT t_article.* FROM t_article
     * ```
     *
     * @param string|array $queryPartName リセットする句
     * @return $this 自分自身
     */
    public function resetQueryPart($queryPartName = null)
    {
        if ($queryPartName === null || is_array($queryPartName)) {
            foreach ($queryPartName ?? array_keys($this->sqlParts) as $name) {
                $this->resetQueryPart($name);
            }
            return $this;
        }

        if (!array_key_exists($queryPartName, $this->sqlParts)) {
            throw new \InvalidArgumentException("queryPartName:'$queryPartName' is undefined.");
        }

        $this->sqlParts[$queryPartName] = is_array($this->sqlParts[$queryPartName]) ? [] : null;

        // 各句に紐づくフィールドは特別にクリアしなければならない
        if ($queryPartName === 'select') {
            $this->subbuilders = [];
            $this->phpExpressions = [];
            $this->joinOrders = [];
            $this->sqlParts['option'] = [];
        }

        // 同上（where）
        if ($queryPartName === 'where') {
            $this->submethod = null;
            $this->emptyCondition = null;
        }

        // 同上（orderBy）
        if ($queryPartName === 'orderBy') {
            $this->phpOrders = null;
        }

        // パラメータはゲタを履かせたオフセット値で識別して伏せる
        if (isset(self::PARAMETER_OFFSETS[$queryPartName])) {
            $offset = self::PARAMETER_OFFSETS[$queryPartName];
            foreach ($this->params as $k => $v) {
                if ($offset <= $k && $k < $offset + self::PARAMETER_OFFSET) {
                    unset($this->params[$k]);
                }
            }
        }

        return $this->_dirty();
    }

    /**
     * すべてを無に帰す
     *
     * @return $this 自分自身
     */
    public function reset()
    {
        // 固有なフィールドをクリア
        $this->statement = null;
        $this->caster = null;
        $this->subbuilders = [];
        $this->phpExpressions = [];
        $this->joinOrders = [];
        $this->wrappers = [];
        $this->lockMode = null;
        $this->lockOption = null;
        $this->rowcount = false;

        $this->resetQueryPart();
        $this->params = [];

        return $this->_dirty();
    }

    /**
     * 現在のビルダの状態で固定して prepare する
     *
     * 「peparedStatement を返す」のではなく「prepare 状態にするだけ」なのに注意。
     * peparedStatement は {@link getPreparedStatement()} で取得する。
     *
     * ```php
     * $qb->column('t_article', ['state' => 'active', 'id = :id']);
     * # 現在の状態で prepare する
     * $qb->prepare();
     * // この段階では state: active は固定されているが、:id は未確定
     * $stmt = $qb->getPreparedStatement();
     * // ここで実行することで id: 1 がプリペアで実行される
     * $stmt->executeQuery(['id' => 1]); // SELECT t_article.* FROM t_article WHERE (id = 1) AND (state = "active")
     * // さらに続けてプリペアで id: 2 を実行できる
     * $stmt->executeQuery(['id' => 2]); // SELECT t_article.* FROM t_article WHERE (id = 2) AND (state = "active")
     * ```
     *
     * @return $this 自分自身
     */
    public function prepare()
    {
        $this->statement = $this->database->prepare($this);
        return $this;
    }

    /**
     * prepare したステートメントを返す
     *
     * {@link prepare()} の例も参照。
     *
     * @return Statement プリペアされたステートメント
     */
    public function getPreparedStatement()
    {
        return $this->statement;
    }

    /**
     * パラメータを利用してクエリ化
     *
     * @return string エスケープ済みの完全なクエリ
     */
    public function queryInto()
    {
        return $this->database->queryInto($this->__toString(), $this->getParams());
    }

    /**
     * bind 値を追加する
     *
     * @param mixed $params bind パラメータ
     * @param int|string $position 追加インデックス。文字列を与えると句文字列を意味する
     * @return $this 自分自身
     */
    public function addParam($params, $position = null)
    {
        // bind 順のズレを極力防ぐために句毎にゲタを履かせて取得時（getParameters）にソートする
        $position = intval(self::PARAMETER_OFFSETS[$position] ?? $position);
        $n = count($this->params);
        foreach (arrayize($params) as $param) {
            $this->params[$position + $n++] = $param;
        }
        return $this;
    }

    /**
     * bind 値を設定する
     *
     * なんの制約もなしにダイレクトに代入できるが、内部向けなので非推奨。
     *
     * @param array $params bind パラメータ
     * @return $this 自分自身
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    public function getQuery()
    {
        return "($this)";
    }

    public function getParams($queryPartName = null)
    {
        if ($queryPartName !== null) {
            $params = [];
            $offset = self::PARAMETER_OFFSETS[$queryPartName];
            foreach ($this->params as $k => $v) {
                if ($offset <= $k && $k < $offset + self::PARAMETER_OFFSET) {
                    $params[] = $this->params[$k];
                }
            }
            return $params;
        }

        $params = $this->params;
        ksort($params);
        return array_merge($params);
    }

    public function merge(&$params)
    {
        foreach ($this->getParams() as $param) {
            $params[] = $param;
        }
        return $this->getQuery();
    }
}
