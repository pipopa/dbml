<?php

namespace ryunosuke\dbml\Query\Expression;

use ryunosuke\dbml\Metadata\CompatiblePlatform;
use ryunosuke\dbml\Query\Queryable;
use const ryunosuke\dbml\strcat;
use function ryunosuke\dbml\array_depth;
use function ryunosuke\dbml\array_each;
use function ryunosuke\dbml\array_flatten;
use function ryunosuke\dbml\array_nmap;
use function ryunosuke\dbml\arrayize;
use function ryunosuke\dbml\first_keyvalue;
use function ryunosuke\dbml\str_subreplace;

/**
 * 演算子を表すクラス
 *
 * 内部的に使用されるだけで、明示的に使用する箇所はほとんど無い。
 * ただし、下記の演算子登録を使用する場合は {@link define()} で登録する必要がある。
 *
 * 組み込みの演算子は下記。これらは何もしなくても whereInto で使用することができる。
 *
 * | operator                                       | result                                       | 説明
 * |:--                                             |:--                                           |:--
 * | `LIKE`, `BETWEEN`, `=`, `<>`, etc...           | 略。BETWEEN は値に配列を与える               | 大体の RDBMS に備わっている標準的な演算子。想起される通りの動作するので説明は省略。他にも `IN` や `<=>` 等がある。
 * | `'hoge:[~)' => [1, 9]`                         | `hoge >= 1 AND hoge < 9`                     | 範囲指定。 `[~]` の `[]` はイコール有り、`()` はイコール無しを意味する。つまり、 `[~]` `[~)` `(~]` `(~)` の4つの演算子がある。順に「以上・以下」「以上・小なり」「大なり・以下」「大なり・小なり」を意味する。
 * | `'hoge:[~)' => [1, null]`                      | `hoge >= 1`                                  | 上記と同じ。ただし、バインド値に null を渡すと指定した方の条件が吹き飛ぶ
 * | `'hoge:[~)' => [null, 9]`                      | `hoge < 9`                                   | 上記の後半部分版
 * | `'hoge:[~)' => [null, null]`                   | -                                            | バインド値に両方 null を渡すと条件自体が吹き飛ぶ
 * | `'hoge:LIKE%' => 'wo%rd'`                      | `hoge LIKE 'wo\%rd%'`                        | LIKEエスケープを施した上で右に"%"を付加してLIKEする。他にも `%LIKE` `%LIKE%` がある
 * | `'hoge:LIKEIN%' => ['he%lo', 'wo%rd']`         | `hoge LIKE 'he\%lo%' OR hoge LIKE 'wo\%rd%'` | 上記の配列IN版。構文的には `LIKE ANY('str1', 'str2')` みたいなもの
 * | `'hoge:NULLIN' => [1, 2, 3, NULL]`             | `hoge IN (1, 2, 3) OR hoge IS NULL`          | NULL を許容できる IN。 `[1, 2, 3, null]` などとすると IN(1, 2, 3) or NULL のようになる
 *
 * ```php
 * # 独自演算子 FISOR を定義する
 * Operator::define('FISOR', function ($column, $params) {
 *     $conds = array_fill(0, count($params), "FIND_IN_SET(?, $column)");
 *     return [implode(' OR ', $conds) => $params];
 * });
 *
 * # すると whereInto の演算子指定で使用できるようになる
 * $db->whereInto(['col:FISOR' => [1, 2]]);
 * // WHERE FIND_IN_SET(1, col) OR FIND_IN_SET(2, col)
 * ```
 */
class Operator implements Queryable
{
    /// 内部演算子
    public const RAW    = '__RAW__';
    public const COLVAL = '__COLVAL__';

    /// 標準演算子
    public const OP_SPACESHIP = '<=>';
    public const OP_IS_NULL   = 'IS NULL';
    public const OP_BETWEEN   = 'BETWEEN';
    public const OP_IN        = 'IN';

    /// 拡張LIKE演算子
    public const OP_RIGHT_LIKE = 'LIKE%';
    public const OP_LEFT_LIKE  = '%LIKE';
    public const OP_BOTH_LIKE  = '%LIKE%';

    /// 独自演算子
    public const OP_NULLIN        = 'NULLIN';  // x IN (1, 2, 3) OR x IS NULL
    public const OP_RIGHT_LIKEIN  = 'LIKEIN%';  // x LIKE "hoge%" OR x LIKE "fuga%"
    public const OP_LEFT_LIKEIN   = '%LIKEIN';  // x LIKE "%hoge" OR x LIKE "%fuga"
    public const OP_BOTH_LIKEIN   = '%LIKEIN%'; // x LIKE "%hoge%" OR x LIKE "%fuga%"
    public const OP_RANGE         = '(~)';     // x > 1  && x <  9
    public const OP_RANGE_LTE     = '[~)';     // x >= 1 && x <  9
    public const OP_RANGE_GTE     = '(~]';     // x > 1 && x <= 9
    public const OP_RANGE_BETWEEN = '[~]';     // x >= 1 && x <= 9

    /** @var \Closure[] 外部注入演算子 */
    private static $registereds = [];

    /** @var CompatiblePlatform */
    private $platform;

    /** @var string 演算子 */
    private $operator;

    /** @var string 演算値1 */
    private $operand1;

    /** @var string 演算値2 */
    private $operand2;

    /** @var bool 演算値2が配列か */
    private $isarray;

    /** @var bool 否定フラグ */
    private $not = false;

    /** @var string __toString で返される文字列 */
    private $string;

    /** @var array パラメータ */
    private $params;

    /**
     * 演算子を定義する
     *
     * 返り値として「カラム, 値を受け取り、[式 => パラメータ] を返すクロージャ」を返さなければならない。
     * （クラス冒頭のサンプルを参照）。
     *
     * @param string $operator 演算子文字列
     * @param callable||null $callback 演算処理
     */
    public static function define($operator, $callback)
    {
        self::$registereds[$operator] = $callback;
    }

    /**
     * インスタンスを返す
     *
     * - `new Operator($platform, 'BETWEEN', 'hoge', '1');`
     * - `Operator::BETWEEN('hoge', '1', $platform);`
     * - `Operator::BETWEEN('hoge', '1');`
     *
     * これらはそれぞれ等価になる（$platform は optional）。
     *
     * @param string $operator 演算子
     * @param array $operands 演算値
     * @return $this Operator インスタンス
     */
    public static function __callStatic($operator, $operands)
    {
        if (count($operands) < 2) {
            throw new \InvalidArgumentException('argument\'s length must be greater than 2.');
        }

        $operand1 = $operands[0];
        $operand2 = $operands[1];
        $platform = $operands[2] ?? null;

        return new Operator($platform, $operator, $operand1, $operand2);
    }

    /**
     * コンストラクタ
     *
     * @param CompatiblePlatform $platform プラットフォーム
     * @param string $operator 演算子
     * @param string $operand1 演算値1
     * @param string|array $operand2 演算値2
     */
    public function __construct($platform, $operator, $operand1, $operand2)
    {
        $this->platform = $platform;
        $this->operator = strtoupper(trim($operator));
        $this->operand1 = $operand1;
        $this->operand2 = arrayize($operand2);
        $this->isarray = is_array($operand2);

        // ! は否定を意味する
        if (isset($this->operator[0]) && $this->operator[0] === '!') {
            $this->operator = substr($this->operator, 1);
            $this->not = true;
        }
    }

    /**
     * 文字列表現を返す
     *
     * @return string 文字列表現
     */
    public function __toString()
    {
        if ($this->string === null) {
            $this->string = $this->_getString();
        }
        return $this->string;
    }

    /**
     * toString の実体
     *
     * 引数取れなかったり例外を投げると死んだりテストしづらかったり等のつらみがあるので分離している。
     *
     * @uses _default()
     * @uses _colval()
     * @uses _raw()
     * @uses _spaceship()
     * @uses _isnull()
     * @uses _between()
     * @uses _in()
     * @uses _like()
     * @uses _likein()
     * @uses _range()
     *
     * @return string
     */
    private function _getString()
    {
        // 外部注入優先で処理
        if (isset(self::$registereds[$this->operator])) {
            $callback = self::$registereds[$this->operator];
            $result = $callback($this->operand1, $this->operand2);
            list($this->string, $this->params) = first_keyvalue($result);
        }
        else {
            $methods = [
                ''                     => ['_default' => []],
                self::COLVAL           => ['_colval' => []],
                self::RAW              => ['_raw' => []],
                self::OP_SPACESHIP     => ['_spaceship' => []],
                self::OP_IS_NULL       => ['_isnull' => []],
                self::OP_BETWEEN       => ['_between' => []],
                self::OP_IN            => ['_in' => [false]],
                self::OP_NULLIN        => ['_in' => [true]],
                self::OP_RIGHT_LIKE    => ['_like' => ['', '%']],
                self::OP_LEFT_LIKE     => ['_like' => ['%', '']],
                self::OP_BOTH_LIKE     => ['_like' => ['%', '%']],
                self::OP_RIGHT_LIKEIN  => ['_likein' => ['', '%']],
                self::OP_LEFT_LIKEIN   => ['_likein' => ['%', '']],
                self::OP_BOTH_LIKEIN   => ['_likein' => ['%', '%']],
                self::OP_RANGE         => ['_range' => ['>', '<']],
                self::OP_RANGE_LTE     => ['_range' => ['>=', '<']],
                self::OP_RANGE_GTE     => ['_range' => ['>', '<=']],
                self::OP_RANGE_BETWEEN => ['_range' => ['>=', '<=']],
            ];
            $method = $methods[strlen($this->operator) ? $this->operator : self::COLVAL] ?? $methods[''];
            foreach ($method as $name => $args) {
                $this->$name(...$args);
            }
        }

        if ($this->not && $this->string) {
            $this->string = 'NOT (' . $this->string . ')';
        }

        return $this->string;
    }

    private function _default()
    {
        if (count($this->operand2) !== 1) {
            throw new \UnexpectedValueException("DEFAULT's operand2 must be array contains 1 elements.");
        }
        $this->string = $this->operand1 . ' ' . $this->operator . ' ?';
        $this->params = $this->operand2;
    }

    private function _raw()
    {
        $isnestarray = $this->isarray && array_depth($this->operand2) > 1;
        $isnesting = $this->isarray && ($isnestarray || substr_count($this->operand1, '?') === count($this->operand2, COUNT_RECURSIVE));
        $values = $isnesting ? $this->operand2 : [$this->operand2];

        $maps = [];
        $params = [];
        foreach ($values as $val) {
            $vals = arrayize($val);
            $maps[] = implode(',', array_fill(0, count($vals), '?')) ?: 'NULL';

            foreach ($vals as $v) {
                $params[] = $v;
            }
        }
        $this->string = str_subreplace($this->operand1, '?', $maps);
        $this->params = $params;
    }

    private function _colval()
    {
        if ($this->operand2 === [null]) {
            $this->operator = self::OP_IS_NULL;
            $this->_isnull();
        }
        elseif ($this->isarray) {
            $this->operator = self::OP_IN;
            $this->_in(false);
        }
        else {
            $this->operator = '=';
            $this->_default();
        }
    }

    private function _spaceship()
    {
        if (count($this->operand2) !== 1) {
            throw new \UnexpectedValueException("SPACESHIP's operand2 must be array contains 1 elements.");
        }
        $this->string = $this->platform->getSpaceshipSyntax($this->operand1);
        $this->params = $this->operand2;
    }

    private function _isnull()
    {
        $this->string = $this->operand1 . ' ' . $this->operator;
        $this->params = [];
    }

    private function _between()
    {
        if (count($this->operand2) !== 2) {
            throw new \UnexpectedValueException("BETWEEN's operand2 must be array contains 2 elements.");
        }
        $this->string = $this->operand1 . ' ' . $this->operator . ' ? AND ?';
        $this->params = $this->operand2;
    }

    private function _in($allownull)
    {
        $ph = '?';
        if (array_depth($this->operand2) > 1) {
            $first = reset($this->operand2);
            $ph = $first ? '(' . implode(',', array_fill(0, count($first), '?')) . ')' : '';
        }
        $placeholder = implode(',', array_fill(0, count($this->operand2), $ph)) ?: 'NULL';
        $ORNULL = $allownull && in_array(null, $this->operand2, true) ? " OR {$this->operand1} IS NULL" : '';
        $this->string = $this->operand1 . ' IN (' . $placeholder . ')' . $ORNULL;
        $this->params = array_flatten($this->operand2);
    }

    private function _like($l, $r)
    {
        $this->string = $this->operand1 . ' LIKE ?';
        $this->params = arrayize($l . $this->platform->escapeLike($this->operand2[0]) . $r);
    }

    private function _likein($l, $r)
    {
        $likes = array_fill(0, count($this->operand2), $this->operand1 . ' LIKE ?');
        $this->string = implode(' OR ', $likes);
        $this->params = array_nmap($this->operand2, strcat, 1, $l, $r);
    }

    private function _range($op1, $op2)
    {
        if (count($this->operand2) !== 2) {
            throw new \UnexpectedValueException("RANGE's operand2 must be array length 2.");
        }
        $this->operand2 = array_values($this->operand2);
        $cond = array_each([$op1, $op2], function (&$carry, $op, $k) {
            if (strlen($this->operand2[$k])) {
                $carry[$this->operand1 . " $op ?"] = $this->operand2[$k];
            }
        }, []);
        $this->string = implode(' AND ', array_keys($cond));
        $this->params = array_values($cond);
    }

    /**
     * 否定する
     *
     * @return $this 自分自身
     */
    public function not()
    {
        // string に null を入れて再生成を促す必要がある
        $this->string = null;

        $this->not = true;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getParams()
    {
        // getString で params を入れているので呼ぶ必要がある
        if ($this->string === null) {
            $this->string = $this->_getString();
        }
        return $this->params;
    }

    /**
     * @inheritdoc
     */
    public function getQuery()
    {
        return $this->__toString();
    }

    /**
     * @inheritdoc
     */
    public function merge(&$params)
    {
        foreach ($this->getParams() as $param) {
            $params[] = $param;
        }
        return $this->getQuery();
    }
}
