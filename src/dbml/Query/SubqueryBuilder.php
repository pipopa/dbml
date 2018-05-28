<?php

namespace ryunosuke\dbml\Query;

use ryunosuke\dbml\Database;
use ryunosuke\dbml\Mixin\OptionTrait;
use ryunosuke\dbml\Query\Expression\Alias;
use ryunosuke\dbml\Utility\Adhoc;
use function ryunosuke\dbml\array_find;
use function ryunosuke\dbml\array_get;
use function ryunosuke\dbml\array_sprintf;
use function ryunosuke\dbml\array_strpad;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * サブクエリビルダークラス
 *
 * 直接 new する類のクラスではない。 Database や QueryBuilder が間接的に利用する。
 *
 * @method bool            getLazyClonable()
 * @method $this           setLazyClonable($bool)
 * @method bool            getPropagateLockMode()
 * @method $this           setPropagateLockMode($bool)
 *
 * @method $this           array(array $params = [])
 * @method $this           assoc(array $params = [])
 * @method $this           lists(array $params = [])
 * @method $this           pairs(array $params = [])
 * @method $this           tuple(array $params = [])
 * @method $this           value(array $params = [])
 *
 * @no-virtual
 */
class SubqueryBuilder extends QueryBuilder
{
    use OptionTrait;

    /** @var string 遅延実行モード(select|fetch) */
    private $lazyMode;

    /** @var array 関連カラム */
    private $lazyColumns = [];

    /** @var bool|array 後で設定するパラメータ(フラグも兼ねる) */
    private $delayParams = [];

    /** @var string サブセレクト時の fetch メソッド */
    private $submethod;

    public static function getDefaultOptions()
    {
        return array_replace([
            // 遅延実行時に自身・subselect を clone するか否か
            'lazyClonable'      => true,
            // 遅延実行時に親のロックモードを受け継ぐか否か
            'propagateLockMode' => true,
        ], parent::getDefaultOptions());
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

        // perform 系
        $methods = Database::METHODS;
        if (isset($methods[strtolower($name)])) {
            if (!$this->lazyMode) {
                throw new \UnexpectedValueException('subquery must be lazy mode.');
            }
            $this->submethod = $name;
            $this->addParam(array_key_exists(0, $arguments) ? $arguments[0] : []);
            return $this;
        }

        throw new \BadMethodCallException("'$name' is undefined.");
    }

    private function _convertChildKey($ck)
    {
        if ($this->lazyMode === 'select') {
            return $ck;
        }
        else {
            return str_replace('.', '__', $ck);
        }
    }

    /**
     * 遅延実行化する
     *
     * このメソッドを呼ぶと fetch 系メソッドは実行されなくなる
     *
     * @param string $mode 遅延モード(select|fetch)
     * @param array|string $child_columns [子供カラム => 親カラム] あるいは [共通カラム]
     * @return $this 自分自身
     */
    public function lazy($mode, $child_columns)
    {
        // 現状 select/fetch しか受け付けない
        if (!array_key_exists($mode, ['select' => true, 'fetch' => true])) {
            throw new \InvalidArgumentException('$mode is must be [select|fetch]');
        }

        $child_columns = Adhoc::to_hash($child_columns);
        if (count($child_columns) === 0) {
            throw new \InvalidArgumentException('$child_columns is a need to 1 or more.');
        }

        $this->lazyMode = $mode;
        $this->lazyColumns = $child_columns;

        return $this;
    }

    /**
     * 外部キーによる lazy_column の自動設定
     *
     * @param string $tablename テーブル名
     * @return array 結合カラム配列
     */
    public function setLazyColumnFrom($tablename)
    {
        if ($this->lazyColumns !== ['' => null]) {
            return [];
        }

        if (!$tablename) {
            throw new \UnexpectedValueException('basetable is not specified.');
        }

        $lazy_columns = array_find($this->getFromPart(), function ($from) use ($tablename) {
            $fcols = $this->getDatabase()->getSchema()->getForeignColumns($tablename, $from['table'], $from['fkeyname']);
            if ($fcols) {
                return array_strpad($fcols, $from['alias'] . '.');
            }
        }, false);
        if (!$lazy_columns) {
            throw new \UnexpectedValueException("foreign key is not found on $tablename.");
        }

        return $this->lazyColumns = $lazy_columns;
    }

    /**
     * （親のキーで）後でbuild させるようにする
     *
     * @return $this 自分自身
     */
    public function delay()
    {
        $this->delayParams = true;
        return $this;
    }

    /**
     * 取得後にサブカラムを伏せる必要があるかを返す
     *
     * @return bool サブカラムを伏せる必要があるなら true
     */
    public function isRequireUnsetSubcolumn()
    {
        return $this->lazyMode === 'fetch';
    }

    /**
     * lazy_mode を強制的に設定する
     *
     * @param string $lazyMode 遅延モード文字列
     * @return $this 自分自身
     */
    public function setLazyMode($lazyMode)
    {
        $this->lazyMode = $lazyMode;
        return $this;
    }

    /**
     * 遅延パラメータを取得する
     *
     * @return array 遅延パラメータ
     */
    public function getDelayedParams()
    {
        return $this->delayParams === true ? [] : $this->delayParams;
    }

    public function build($queryParts, $append = false)
    {
        // 遅延モードなら蓄えるだけ
        if ($this->delayParams === true) {
            $this->delayParams = $queryParts;
            return $this;
        }

        $result = parent::build($queryParts, $append);
        $this->delayParams = [];
        return $result;
    }

    /**
     * サブクエリを実行する
     *
     * @param array $parents 親行配列
     * @param string $column 親行に格納するキー
     * @return array サブクエリ結果が埋め込まれた $parents
     */
    public function subquery($parents, $column)
    {
        $subdatabase = $this->getDatabase();
        $subselect = $this->getLazyClonable() ? clone $this : $this;

        $lazy_mode = $this->lazyMode;

        // 親カラム参照カラムを分離しておく
        $pcolumns = [];
        $selects = $subselect->getQueryPart('select');
        foreach ($selects as $n => $select) {
            $palias = null;
            if ($select instanceof Alias && is_string($select->getActual())) {
                $palias = $select->getAlias();
                $select = $select->getActual();
            }
            if (is_string($select) && strpos($select, '..') === 0) {
                list(, $pcol) = explode('..', $select, 2);
                $pcolumns[$palias ?: $pcol] = $pcol;
                unset($selects[$n]);
            }
        }
        $subselect->select(...$selects);

        // [子供のキー => 親の値] の配列を作成
        $conds = [];
        $childkeys = [];
        foreach ($subselect->lazyColumns as $child => $parent) {
            $childkey = $subselect->_convertChildKey($child);
            $childkeys[$child] = $childkey;
            foreach ($parents as $n => $parent_row) {
                // 親行カラムがあるかチェック（1回で十分なので初回のみ）
                if (!isset($checked)) {
                    $checked = true;
                    if (!array_key_exists($parent, $parent_row)) {
                        throw new \OutOfBoundsException("'$parent' is undefined at parent row.");
                    }
                    if ($pcolumns && array_diff_key(array_flip($pcolumns), $parent_row)) {
                        throw new \OutOfBoundsException("reference undefined parent column [" . implode(', ', $pcolumns) . "].");
                    }
                }
                $conds[$n][$childkey] = $parent_row[$parent];
            }
        }

        $subselect->detectAutoOrder(true);

        switch ($lazy_mode) {
            case 'select':
                // 分類に必要なので子どもキーを加える
                $lckey = $subselect->_addPrimary(Database::AUTO_CHILD_KEY, array_keys($childkeys), false);

                // 親行から抽出した where（queryInto してるのは誤差レベルではなく速度に差が出るから）
                $expr = $subdatabase->getCompatiblePlatform()->getPrimaryCondition($conds);
                $subselect->andWhere($subdatabase->queryInto($expr));

                // 子供行の limit は親の範囲内の limit として利用する
                $suboffset = $subselect->getQueryPart('offset');
                $sublength = $subselect->getQueryPart('limit') === null ? null : $suboffset + $subselect->getQueryPart('limit');
                $subselect->limit(null, null);

                // 子供行の取得とグループ化
                $children = [];
                $counter = [];
                $child_rows = $subdatabase->fetchArray($subselect);
                foreach ($child_rows as $nc => $child_row) {
                    $pckey = $child_row[$lckey];
                    if ($suboffset !== null || $sublength !== null) {
                        $counter[$pckey] = ($counter[$pckey] ?? 0) + 1;
                        if ($suboffset !== null && $suboffset >= $counter[$pckey]) {
                            continue;
                        }
                        if ($sublength !== null && $sublength < $counter[$pckey]) {
                            continue;
                        }
                    }
                    $children[$pckey][] = $child_row;
                }

                // 親の指定キーに子供行を突っ込むループ
                $psep = $subselect->getPrimarySeparator();
                foreach ($parents as $n => $parent_row) {
                    $ckey = implode($psep, array_get($parent_row, $subselect->lazyColumns));
                    $crow = $children[$ckey] ?? [];
                    $parents[$n][$column] = $subdatabase->perform($crow, $subselect->submethod, null, true);
                }
                break;
            case 'fetch':
                $subselect->andWhere(array_sprintf($childkeys, '%2$s = :%1$s'));
                $subselect->prepare();
                $submethod = 'fetch' . $subselect->submethod;
                foreach ($parents as $n => $parent_row) {
                    $parents[$n][$column] = $subdatabase->$submethod($subselect, $conds[$n]);
                }
                break;
        }

        // 親カラムを参照して入れる
        $scalarable = $subselect->submethod === Database::METHOD_TUPLE || $subselect->submethod === Database::METHOD_VALUE;
        foreach ($pcolumns as $alias => $pcolumn) {
            foreach ($parents as $n => $parent_row) {
                if ($scalarable) {
                    if ($parents[$n][$column]) {
                        $parents[$n][$column][$alias] = $parent_row[$pcolumn];
                    }
                }
                else {
                    foreach ($parent_row[$column] as $k => $crow) {
                        $parents[$n][$column][$k][$alias] = $parent_row[$pcolumn];
                    }
                }
            }
        }

        return $parents;
    }

    public function reset()
    {
        parent::reset();

        $this->lazyMode = null;
        $this->lazyColumns = [];
        $this->submethod = null;
        $this->delayParams = [];

        return $this;
    }

    /**
     * サブクエリを SQL 文字列化して返す（デバッグ用）
     *
     * @ignore
     *
     * @param string $key 親のエイリアスキー
     * @return string サブクエリ化した文字列
     */
    public function toParentColumn($key)
    {
        $that = clone $this;

        // 自身の subbuilder は再帰しない(そいつの実行時にどうせ実行される)
        $params = $that->getParams();
        $selects = $that->getQueryPart('select');
        $that->resetQueryPart('select');
        $that->select(...$selects);
        $that->setParams($params);

        // 結合カラムを WHERE に加えてわかりやすくする
        $that->andWhere(array_sprintf($that->lazyColumns, '%2$s IN ([parent.%1$s])'));

        // カラムコメント化
        $cplatform = $this->getDatabase()->getCompatiblePlatform();
        $x = $that->queryInto();
        return $cplatform->commentize("($x) AS $key");
    }
}
