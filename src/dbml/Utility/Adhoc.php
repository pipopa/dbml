<?php

namespace ryunosuke\dbml\Utility;

use ryunosuke\dbml\Query\QueryBuilder;
use function ryunosuke\dbml\array_nest;
use function ryunosuke\dbml\arrayize;

/**
 * 比較的固有な処理を記述する Utility クラス
 */
class Adhoc
{
    /**
     * 引数の配列化
     *
     * @param array $func_get_args
     * @return array
     */
    public static function argumentize($func_get_args)
    {
        if (count($func_get_args) === 0) {
            return [];
        }
        $first = $func_get_args[0];
        return is_array($first) ? $first : $func_get_args;
    }

    /**
     * 普通の配列を連想配列に変換
     *
     * 数値キーは値がキーになる
     * 値は $callable が通される（省略時はそのまま）
     *
     * @param array|string $array
     * @param callable|null $callable
     * @return array
     */
    public static function to_hash($array, $callable = null)
    {
        $array = arrayize($array);

        $result = [];
        foreach ($array as $k => $v) {
            if (is_int($k)) {
                $k = $v;
                if (isset($callable)) {
                    $v = $callable($v);
                }
            }
            $result[$k] = $v;
        }
        return $result;
    }

    /**
     * 指定要素が空でない時に配列に新しく要素を追加する
     *
     * @param array $array 対象配列
     * @param mixed $newelement 追加要素
     * @param string|null $newkey 新キー
     * @return bool 追加したら true
     */
    public static function array_push(&$array, $newelement, $newkey = null)
    {
        if (!Adhoc::is_empty($newelement)) {
            if (func_num_args() === 3) {
                $array[$newkey] = $newelement;
            }
            else {
                $array[] = $newelement;
            }
            return true;
        }

        return false;
    }


    /**
     * 値が「空」なら true を返す
     *
     * @param mixed $value
     * @return bool
     */
    public static function is_empty($value)
    {
        if ($value === null) {
            return true;
        }
        if ($value === []) {
            return true;
        }
        if ($value === '') {
            return true;
        }
        if ($value instanceof QueryBuilder && $value->isEmptyCondition()) {
            return true;
        }

        return false;
    }

    /**
     * 非常に緩く parse_ini_string して可能な限り配列で返す
     *
     * @param string $inistring
     * @return array
     */
    public static function parse_ini($inistring)
    {
        $entries = @parse_ini_string($inistring);
        if ($entries === false) {
            $entries = [];
            // エラー起因の行を吹き飛ばして再帰（なんかここまでするなら自前パースのほうが楽な気が・・・）
            $le = error_get_last();
            if (preg_match('#on line (\d+)#i', $le['message'], $m)) {
                $lines = preg_split('#\R#u', $inistring);
                unset($lines[$m[1] - 1]);
                $entries = Adhoc::parse_ini(implode("\n", $lines));
            }
            return $entries;
        }

        return array_nest($entries, '.');
    }

    /**
     * 指定配列を個数に応じて再帰的に小括弧()で包む
     *
     * @param array $array
     * @return array
     */
    public static function wrapParentheses($array)
    {
        $count = count($array);
        $result = [];
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $result[$k] = Adhoc::wrapParentheses($v);
            }
            else {
                if ($count === 1) {
                    $result[$k] = $v;
                }
                else {
                    $result[$k] = "($v)";
                }
            }
        }
        return $result;
    }

    /**
     * テーブル修飾子を付与する
     *
     * @param string $tablename テーブル名
     * @param array $array 修飾する配列
     * @return array 修飾された配列
     */
    public static function modifier($tablename, $array)
    {
        if (!strlen($tablename)) {
            return $array;
        }

        $result = [];
        foreach ($array as $key => $val) {
            // QueryBuilder で submethod ならプレフィックスを付けない
            if ($val instanceof QueryBuilder && $val->getSubmethod() !== null) {
                $result[$key] = $val;
                continue;
            }
            if (is_string($key) && strpos($key, '.') === false) {
                if ($key[0] === '!') {
                    $key = '!' . $tablename . '.' . ltrim($key, '!');
                }
                else {
                    $key = $tablename . '.' . $key;
                }
            }
            if (is_array($val)) {
                $val = self::modifier($tablename, $val);
            }
            $result[$key] = $val;
        }
        return $result;
    }

    /**
     * 文字列を非常に緩くパースしてそれっぽい ymdhis 配列を返す
     *
     * 存在しない日時や繰り上げなどは日時と見なさない。
     * 年は省略できるが（年月とみなす）、時は省略できない。
     *
     * @param string $string
     * @return array|bool
     */
    public static function parseYmdHis($string)
    {
        // 空白文字で日・時を分割
        list($date, $time) = preg_split('#[\s　]#', preg_replace('#^[\s　]+|[\s　]+$#', '', $string)) + [1 => ''];

        // 日の数値のみで分割
        list($y, $m, $d) = preg_split('#[^\d]+#', $date, -1, PREG_SPLIT_NO_EMPTY) + [0 => false, 1 => false, 2 => false];
        // ただし "2014/12" と "12/24" の区別はつかないので字数で判断
        if (strlen($y) <= 2) {
            list($y, $m, $d) = [false, $y, $m];
        }
        // 4桁年しかサポートしない（mysql がサポートしてない）
        if ($y !== false && strlen($y) < 4) {
            return false;
        }

        // 時の数値のみで分割
        list($h, $i, $s) = preg_split('#[^\d]+#', $time, -1, PREG_SPLIT_NO_EMPTY) + [0 => false, 1 => false, 2 => false];

        // 全部 false なら NG
        if ($y === false && $m === false && $d === false && $h === false && $i === false && $s === false) {
            return false;
        }

        // 文字列表現を作っておく
        $strtime = sprintf('%04d-%02d-%02d %02d:%02d:%02d',
            $y === false ? 1000 : $y,
            $m === false ? 1 : $m,
            $d === false ? 1 : $d,
            $h === false ? 1 : $h,
            $i === false ? 1 : $i,
            $s === false ? 1 : $s
        );

        // 不正あるいは文字列表現が一致しないなら NG
        $datetime = date_create_from_format('Y-m-d H:i:s', $strtime);
        if (!$datetime || $datetime->format('Y-m-d H:i:s') !== $strtime) {
            return false;
        }

        // 数値化して返す
        return array_map(function ($v) { return $v === false ? false : (int) $v; }, compact('y', 'm', 'd', 'h', 'i', 's'));
    }

    /**
     * parseDateTime でパースした日時の false を $firstOrLast に従って埋めて返す
     *
     * @param array $datetimeArray
     * @param bool $firstOrLast
     * @return array
     */
    public static function fillYmdHis($datetimeArray, $firstOrLast)
    {
        // 年省略時は今年とするが、年またぎ呼び出しを考慮して最初の値を保持する
        static $thisyear = null;
        if ($thisyear === null) {
            $thisyear = idate('Y');
        }

        // 何度か配列キー指定誤りで事故ったのでクロージャで埋める
        $fill = function (&$v1, $v2, $v3) {
            $v1 = $v1 === $v2 ? $v3 : $v1;
        };

        $dta = $datetimeArray;
        $fill($dta['y'], false, $thisyear);
        $fill($dta['m'], false, $firstOrLast ? 1 : 12);
        $fill($dta['d'], false, $firstOrLast ? 1 : (int) date_create($dta['y'] . '/' . $dta['m'] . '/01')->format('t'));
        $fill($dta['h'], false, $firstOrLast ? 0 : 23);
        $fill($dta['i'], false, $firstOrLast ? 0 : 59);
        $fill($dta['s'], false, $firstOrLast ? 0 : 59);
        return $dta;
    }
}
