<?php

namespace ryunosuke\dbml\Query\Expression;

use function ryunosuke\dbml\array_each;
use function ryunosuke\dbml\date_convert;
use function ryunosuke\dbml\first_keyvalue;
use function ryunosuke\dbml\split_noempty;

/**
 * php の式を表すクラス
 *
 * このクラスのインスタンスを select に組み込むとアプリケーションレイヤで値を返すようになる。
 * 応用的に「カラムAとカラムBを結合してカラムXを作り出す」という処理を透過的に行うことができる。
 *
 * ```php
 * # シンプルなスカラー値の例
 * $db->selectTuple([
 *     'tablename' => [
 *         'nullvalue' => new PhpExpression(null),
 *         'boolvalue' => new PhpExpression(true),
 *         'strvalue'  => new PhpExpression('mojiretu'),
 *     ],
 * ]);
 * // results:
 * [
 *     // PhpExpression に与えた null がそのまま結果配列に含まれてくる
 *     'nullvalue' => null,
 *     // PhpExpression に与えた true がそのまま結果配列に含まれてくる
 *     'boolvalue' => true,
 *     // PhpExpression に与えた 'mojiretu' がそのまま結果配列に含まれてくる
 *     'strvalue'  => 'mojiretu',
 * ];
 *
 * # 上記の例は冗長な上にほとんど旨味がない。似たようなことは Expression の自動変換で代替可能（ただの文字列だけは無理）
 * $db->selectTuple([
 *     'tablename' => [
 *         'nullvalue' => "NULL",
 *         'boolvalue' => true,
 *     ],
 * ]);
 * // results:
 * [
 *     'nullvalue' => null,
 *     'boolvalue' => true,
 * ];
 * ```
 *
 * ただし、上記にような使い方はただの副産物であり、本来はクロージャを渡すことを想定している。
 *
 * ```php
 * # 最も有用な使い方はクロージャを与えた時（有用と言うか、クロージャのために存在していると言っても過言ではない）
 * $db->selectTuple([
 *     'tablename' => [
 *         // カラムa, カラムb を自動で取得し、その加算結果を列として取得する（第2引数以降で取得するカラムを指定する）
 *         'a+b' => new PhpExpression(function ($a, $b) { return $a + $b; }, 'a', 'b'),
 *         // なお、クロージャは自動で PhpExpression に変換されるのでこれでも同じ（引数のデフォルト値で取得するカラムを指定する）
 *         'a*b' => function ($a = 'a', $b = 'b') { return $a * $b; },
 *         // 素のクロージャで引数のデフォルト値がない場合、行全体が引数として渡ってくる
 *         'a-b' => function ($row) { return $row['a'] - $row['b']; },
 *         // 素のクロージャで引数のデフォルト値が null の場合、キーをカラム名として使用する
 *         'a'   => function ($id = null) { $id * 10; },
 *     ],
 * ]);
 * // results:
 * [
 *     // 仮に、a:4 b:2 だとしたら下記が結果配列に含まれてくる
 *     'a+b' => 6,
 *     'a*b' => 8,
 *     'a-b' => 2,
 *     'a'   => 40,
 * ];
 * ```
 *
 * 上記の最後の「引数のデフォルト値が null」について補足すると、この形式は動的な部分が一切ない。つまりただの関数として持ち回すことができる。
 * よくありそうな処理は組み込みで用意してあるが、 define を使用して登録することもできる。
 *
 * ```php
 * # もち回せる関数の登録と組み込み処理の例
 * // まず append という名前で処理を登録する
 * PhpExpression::define('append', function ($column, ...$appendix) {
 *     return $column . '-' . implode('', $appendix);
 * });
 * // 組み込みと登録した append を使用する例
 * $db->selectTuple([
 *     'tablename' => [
 *         // PhpExpression::explode は「指定文字列で explode するクロージャ」を返すので、こうするだけでカラム a をカンマでバラすことができる
 *         'a' => PhpExpression::explode(','),
 *         // 上記で登録した append を使う（使い方自体は変わらない）
 *         'b' => PhpExpression::append('X', 'Y', 'Z'),
 *     ],
 * ]);
 * // results:
 * [
 *     // 仮に、a:"x,y,z" b:"hogestring" だとしたら下記が結果配列に含まれてくる
 *     'a' => ['x', 'y', 'z'], // カンマでバラされている
 *     'b' => 'hogestring-XYZ', // 与えた引数 XYZ が付与されている
 * ];
 * ```
 *
 * ただし、実際のところすごく難解でかつニッチなので、実際のところ
 * - `function ($row) {}` 小細工せずに行をまるごと返す形式
 * - `function ($id = 'id') {}` デフォルト値でカラムを指定する形式
 * 程度しか使われないしこれさえ覚えておけば困らない（その場にクロージャで書かれていた方が何をしているか分かりやすい、というメリットもある）。
 *
 * @method static \Closure explode($delimiter) 指定文字列でバラすクロージャを返す
 * @method static \Closure sprintf($format) 指定書式文字列でフォーマットするクロージャを返す
 * @method static \Closure date($format) 指定フォーマットで日付文字列化するクロージャを返す
 */
class PhpExpression
{
    /** @var \Closure[] 外部注入クロージャ */
    private static $registereds = [];

    /** @var mixed php の式 */
    private $expr;

    /** @var bool callable か否か */
    private $isCallable;

    /** @var int callable の場合の引数の数 */
    private $argCount;

    /** @var array 依存カラム */
    private $dependColumns = [];

    /** @var array 依存カラムエイリアス */
    private $dependAliases = [];

    /**
     * クロージャを定義する
     *
     * @param string $name 定義名
     * @param callable||null $callback 処理実体
     */
    public static function define($name, $callback)
    {
        self::$registereds[$name] = $callback;
    }

    /**
     * 値を PhpExpression 化して返す
     *
     * 変換できなそうならそのまま返す。
     *
     * @param mixed $expr 値
     * @param string $key 元キー
     * @return $this|mixed PhpExpression インスタンス
     */
    public static function forge($expr, $key = null)
    {
        if ($expr instanceof \Closure) {
            $params = (new \ReflectionFunction($expr))->getParameters();
            $pc = count($params);
            // 引数が1つでデフォルト引数が null の場合はキーを使う、という仕様がある
            if ($pc === 1 && $params[0]->isDefaultValueAvailable() && $params[0]->getDefaultValue() === null) {
                return new PhpExpression($expr, Alias::forge($key, ''));
            }
            // 引数のデフォルト値は依存カラムにする、という仕様がある
            $depends = array_each($params, function (&$carry, \ReflectionParameter $param, $n) {
                if ($param->isDefaultValueAvailable()) {
                    $carry[$n] = $param->getDefaultValue();
                }
            }, []);
            // 引数が1より大きいかつ $depends より大きい場合はパラメータが足りない
            if ($pc > 1 && $pc > count($depends)) {
                throw new \OutOfBoundsException('parameter can not be derived.');
            }
            return new PhpExpression($expr, ...$depends);
        }

        return $expr;
    }

    /**
     * 単一引数のシンプルなクロージャを返す
     *
     * @param string $name 定義名
     * @param array $arguments クロージャに与える引数配列
     * @return \Closure クロージャ
     */
    public static function __callStatic($name, $arguments)
    {
        // 注入されているならそれを使う
        if (isset(self::$registereds[$name])) {
            // ただし、デフォルト値が null である必要があるし、$arguments の適用のためにラップして返す
            $closure = self::$registereds[$name];
            return function ($v = null) use ($closure, $arguments) {
                array_unshift($arguments, $v);
                return $closure(...$arguments);
            };
        }

        // それ以外は組み込みを使う
        switch ($name) {
            case 'explode':
                return function ($v = null) use ($arguments) {
                    return $v === null ? null : split_noempty($arguments[0], $v);
                };
            case 'sprintf':
                return function ($v = null) use ($arguments) {
                    return $v === null ? null : sprintf($arguments[0], $v);
                };
            case 'date':
                return function ($v = null) use ($arguments) {
                    return $v === null ? null : date_convert($arguments[0], $v);
                };
        }

        throw new \InvalidArgumentException("'$name is not defined.'");
    }

    /**
     * コンストラクタ
     *
     * @param mixed $expr php 式
     * @param array $depends 依存カラム
     */
    public function __construct($expr, ...$depends)
    {
        foreach ($depends as $column) {
            if ($column instanceof Alias) {
                $this->dependColumns[$column->getAlias()] = $column;
            }
            else {
                if (is_array($column)) {
                    list($alias, $actual) = first_keyvalue($column);
                    $this->dependColumns[$alias] = new Alias($alias, $actual);
                }
                elseif (strpos($column, '.') !== false) {
                    list(, $c) = explode('.', $column, 2);
                    $this->dependColumns[$c] = new Alias($c, $column);
                }
                else {
                    $this->dependColumns[$column] = new Alias(null, $column);
                }
            }
        }

        $this->expr = $expr;
        $this->isCallable = is_callable($expr);
        $this->argCount = count($this->dependColumns);
        $this->dependAliases = array_keys($this->dependColumns);
    }

    /**
     * 式の結果を返す
     *
     * @param mixed $row 自身の行
     * @return mixed 保持してる表現。クロージャならそのコール結果
     */
    public function __invoke($row)
    {
        // callable じゃないならそのまま返す
        if (!$this->isCallable) {
            return $this->expr;
        }

        /** @var callable $expr */
        $expr = $this->expr;

        // 引数なしなら行でコールバック
        if (!$this->argCount) {
            return $expr($row);
        }

        // めちゃくちゃ高頻度に呼ばれる可能性があるので4つまではベタ書きする
        if ($this->argCount === 1) {
            return $expr($row[$this->dependAliases[0]]);
        }
        if ($this->argCount === 2) {
            return $expr($row[$this->dependAliases[0]], $row[$this->dependAliases[1]]);
        }
        if ($this->argCount === 3) {
            return $expr($row[$this->dependAliases[0]], $row[$this->dependAliases[1]], $row[$this->dependAliases[2]]);
        }
        if ($this->argCount === 4) {
            return $expr($row[$this->dependAliases[0]], $row[$this->dependAliases[1]], $row[$this->dependAliases[2]], $row[$this->dependAliases[3]]);
        }

        // 4より多いならしょうがないので配列展開でコール
        $args = [];
        foreach ($this->dependAliases as $alias) {
            $args[] = $row[$alias];
        }
        return $expr(...$args);
    }

    /**
     * 依存カラムを返す
     *
     * @return Alias[] 自身が依存しているカラム配列
     */
    public function getDependColumns()
    {
        // エイリアス名を表す物を除外するため array_filter をかける
        return array_filter($this->dependColumns, function (Alias $alias) {
            return strlen($alias->getActual()) > 0;
        });
    }
}
