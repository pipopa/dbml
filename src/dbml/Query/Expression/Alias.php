<?php

namespace ryunosuke\dbml\Query\Expression;

use function ryunosuke\dbml\concat;

/**
 * カラムエイリアスを表すクラス
 *
 * `new Alias('alias', 'actual')` を select に与えると "actual AS alias" に展開される。
 */
class Alias
{
    /** @var string エイリアス名 */
    private $alias;

    /** @var mixed 実名 */
    private $actual;

    /**
     * インスタンスを返す
     *
     * - new Alias('hoge', 'actual');
     * - Alias::hoge('actual');
     *
     * これらはそれぞれ等価になる。
     *
     * @param string $alias エイリアス名
     * @param array $actuals 実名
     * @return $this エイリアスオブジェクト
     */
    public static function __callStatic($alias, $actuals)
    {
        if (count($actuals) !== 1) {
            throw new \InvalidArgumentException('argument\'s length must be 1.');
        }
        return new Alias($alias, $actuals[0]);
    }

    /**
     * hoge as fuga を分割する
     *
     * @param string $string 分割したい文字列
     * @param string $defaultAlias 分割できなかった場合のデフォルトエイリアス
     * @return array [エイリアス, 実名]
     */
    public static function split($string, $defaultAlias = null)
    {
        $parts = preg_split('#\s+(as\s+)?#ui', $string);
        if (count($parts) === 2) {
            return array_reverse($parts + [1 => $defaultAlias]);
        }
        return [$defaultAlias, $string];
    }

    /**
     * 値を Alias 化して返す
     *
     * 変換できなそうならそのまま返す。
     *
     * @param string $alias エイリアス名
     * @param mixed $actual 実名
     * @return Alias|mixed Alias 化できたら Alias オブジェクト、できなかったら $actual をそのまま返す
     */
    public static function forge($alias, $actual)
    {
        $alen = strlen($alias);

        // エイリアス名が指定されていないならパース
        if ($alen === 0 && is_string($actual)) {
            list($alias, $actual2) = self::split($actual);
            if ($alias !== null) {
                return new self($alias, $actual2);
            }
        }

        // エイリアス名が指定されているならエイリアスとみなす
        if ($alen > 0 && is_string($alias)) {
            return new self($alias, $actual);
        }

        // じゃないなら実部をそのまま返す
        return $actual;
    }

    /**
     * コンストラクタ
     *
     * @param string $alias エイリアス名
     * @param mixed $actual 実名
     */
    public function __construct($alias, $actual)
    {
        $this->alias = $alias;
        $this->actual = $actual;
    }

    /**
     * 文字列表現を返す
     *
     * @return string 文字列表現
     */
    public function __toString()
    {
        return $this->actual . concat(' AS ', $this->alias);
    }

    /**
     * エイリアス名を返す
     *
     * @return string|null 文字列表現
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * 実部名を返す
     *
     * @return string 実部
     */
    public function getActual()
    {
        return $this->actual;
    }
}
