<?php

# Don't touch this code. This is auto generated.

namespace ryunosuke\dbml;

# constants
if (!defined("ryunosuke\\dbml\\JP_ERA")) {
    /** 和暦 */
    define("ryunosuke\\dbml\\JP_ERA", [
        [
            "name"  => "令和",
            "abbr"  => "R",
            "since" => 1556636400,
        ],
        [
            "name"  => "平成",
            "abbr"  => "H",
            "since" => 600188400,
        ],
        [
            "name"  => "昭和",
            "abbr"  => "S",
            "since" => -1357635600,
        ],
        [
            "name"  => "大正",
            "abbr"  => "T",
            "since" => -1812186000,
        ],
        [
            "name"  => "明治",
            "abbr"  => "M",
            "since" => -3216790800,
        ],
    ]);
}


# functions
if (!isset($excluded_functions["arrayize"]) && (!function_exists("ryunosuke\\dbml\\arrayize") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\arrayize"))->isInternal()))) {
    /**
     * 引数の配列を生成する。
     *
     * 配列以外を渡すと配列化されて追加される。
     * 連想配列は未対応。あくまで普通の配列化のみ。
     * iterable や Traversable は考慮せずあくまで「配列」としてチェックする。
     *
     * Example:
     * ```php
     * assertSame(arrayize(1, 2, 3), [1, 2, 3]);
     * assertSame(arrayize([1], [2], [3]), [1, 2, 3]);
     * $object = new \stdClass();
     * assertSame(arrayize($object, false, [1, 2, 3]), [$object, false, 1, 2, 3]);
     * ```
     *
     * @param mixed $variadic 生成する要素（可変引数）
     * @return array 引数を配列化したもの
     */
    function arrayize(...$variadic)
    {
        $result = [];
        foreach ($variadic as $arg) {
            if (!is_array($arg)) {
                $arg = [$arg];
            }
            $result = array_merge($result, $arg);
        }
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\arrayize") && !defined("ryunosuke\\dbml\\arrayize")) {
    define("ryunosuke\\dbml\\arrayize", "ryunosuke\\dbml\\arrayize");
}

if (!isset($excluded_functions["is_hasharray"]) && (!function_exists("ryunosuke\\dbml\\is_hasharray") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\is_hasharray"))->isInternal()))) {
    /**
     * 配列が連想配列か調べる
     *
     * 空の配列は普通の配列とみなす。
     *
     * Example:
     * ```php
     * assertFalse(is_hasharray([]));
     * assertFalse(is_hasharray([1, 2, 3]));
     * assertTrue(is_hasharray(['x' => 'X']));
     * ```
     *
     * @param array $array 調べる配列
     * @return bool 連想配列なら true
     */
    function is_hasharray(array $array)
    {
        $i = 0;
        foreach ($array as $k => $dummy) {
            if ($k !== $i++) {
                return true;
            }
        }
        return false;
    }
}
if (function_exists("ryunosuke\\dbml\\is_hasharray") && !defined("ryunosuke\\dbml\\is_hasharray")) {
    define("ryunosuke\\dbml\\is_hasharray", "ryunosuke\\dbml\\is_hasharray");
}

if (!isset($excluded_functions["first_key"]) && (!function_exists("ryunosuke\\dbml\\first_key") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\first_key"))->isInternal()))) {
    /**
     * 配列の最初のキーを返す
     *
     * 空の場合は $default を返す。
     *
     * Example:
     * ```php
     * assertSame(first_key(['a', 'b', 'c']), 0);
     * assertSame(first_key([], 999), 999);
     * ```
     *
     * @param iterable $array 対象配列
     * @param mixed $default 無かった場合のデフォルト値
     * @return mixed 最初のキー
     */
    function first_key($array, $default = null)
    {
        if (is_empty($array)) {
            return $default;
        }
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($k, $v) = first_keyvalue($array);
        return $k;
    }
}
if (function_exists("ryunosuke\\dbml\\first_key") && !defined("ryunosuke\\dbml\\first_key")) {
    define("ryunosuke\\dbml\\first_key", "ryunosuke\\dbml\\first_key");
}

if (!isset($excluded_functions["first_value"]) && (!function_exists("ryunosuke\\dbml\\first_value") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\first_value"))->isInternal()))) {
    /**
     * 配列の最初の値を返す
     *
     * 空の場合は $default を返す。
     *
     * Example:
     * ```php
     * assertSame(first_value(['a', 'b', 'c']), 'a');
     * assertSame(first_value([], 999), 999);
     * ```
     *
     * @param iterable $array 対象配列
     * @param mixed $default 無かった場合のデフォルト値
     * @return mixed 最初の値
     */
    function first_value($array, $default = null)
    {
        if (is_empty($array)) {
            return $default;
        }
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($k, $v) = first_keyvalue($array);
        return $v;
    }
}
if (function_exists("ryunosuke\\dbml\\first_value") && !defined("ryunosuke\\dbml\\first_value")) {
    define("ryunosuke\\dbml\\first_value", "ryunosuke\\dbml\\first_value");
}

if (!isset($excluded_functions["first_keyvalue"]) && (!function_exists("ryunosuke\\dbml\\first_keyvalue") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\first_keyvalue"))->isInternal()))) {
    /**
     * 配列の最初のキー/値ペアをタプルで返す
     *
     * 空の場合は $default を返す。
     *
     * Example:
     * ```php
     * assertSame(first_keyvalue(['a', 'b', 'c']), [0, 'a']);
     * assertSame(first_keyvalue([], 999), 999);
     * ```
     *
     * @param iterable $array 対象配列
     * @param mixed $default 無かった場合のデフォルト値
     * @return array [最初のキー, 最初の値]
     */
    function first_keyvalue($array, $default = null)
    {
        foreach ($array as $k => $v) {
            return [$k, $v];
        }
        return $default;
    }
}
if (function_exists("ryunosuke\\dbml\\first_keyvalue") && !defined("ryunosuke\\dbml\\first_keyvalue")) {
    define("ryunosuke\\dbml\\first_keyvalue", "ryunosuke\\dbml\\first_keyvalue");
}

if (!isset($excluded_functions["last_key"]) && (!function_exists("ryunosuke\\dbml\\last_key") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\last_key"))->isInternal()))) {
    /**
     * 配列の最後のキーを返す
     *
     * 空の場合は $default を返す。
     *
     * Example:
     * ```php
     * assertSame(last_key(['a', 'b', 'c']), 2);
     * assertSame(last_key([], 999), 999);
     * ```
     *
     * @param iterable $array 対象配列
     * @param mixed $default 無かった場合のデフォルト値
     * @return mixed 最後のキー
     */
    function last_key($array, $default = null)
    {
        if (is_empty($array)) {
            return $default;
        }
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($k, $v) = last_keyvalue($array);
        return $k;
    }
}
if (function_exists("ryunosuke\\dbml\\last_key") && !defined("ryunosuke\\dbml\\last_key")) {
    define("ryunosuke\\dbml\\last_key", "ryunosuke\\dbml\\last_key");
}

if (!isset($excluded_functions["last_value"]) && (!function_exists("ryunosuke\\dbml\\last_value") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\last_value"))->isInternal()))) {
    /**
     * 配列の最後の値を返す
     *
     * 空の場合は $default を返す。
     *
     * Example:
     * ```php
     * assertSame(last_value(['a', 'b', 'c']), 'c');
     * assertSame(last_value([], 999), 999);
     * ```
     *
     * @param iterable $array 対象配列
     * @param mixed $default 無かった場合のデフォルト値
     * @return mixed 最後の値
     */
    function last_value($array, $default = null)
    {
        if (is_empty($array)) {
            return $default;
        }
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($k, $v) = last_keyvalue($array);
        return $v;
    }
}
if (function_exists("ryunosuke\\dbml\\last_value") && !defined("ryunosuke\\dbml\\last_value")) {
    define("ryunosuke\\dbml\\last_value", "ryunosuke\\dbml\\last_value");
}

if (!isset($excluded_functions["last_keyvalue"]) && (!function_exists("ryunosuke\\dbml\\last_keyvalue") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\last_keyvalue"))->isInternal()))) {
    /**
     * 配列の最後のキー/値ペアをタプルで返す
     *
     * 空の場合は $default を返す。
     *
     * Example:
     * ```php
     * assertSame(last_keyvalue(['a', 'b', 'c']), [2, 'c']);
     * assertSame(last_keyvalue([], 999), 999);
     * ```
     *
     * @param iterable $array 対象配列
     * @param mixed $default 無かった場合のデフォルト値
     * @return array [最後のキー, 最後の値]
     */
    function last_keyvalue($array, $default = null)
    {
        if (is_empty($array)) {
            return $default;
        }
        if (is_array($array)) {
            $v = end($array);
            $k = key($array);
            return [$k, $v];
        }
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        foreach ($array as $k => $v) {
            // dummy
        }
        // $k がセットされてるなら「ループが最低でも1度回った（≠空）」とみなせる
        if (isset($k)) {
            /** @noinspection PhpUndefinedVariableInspection */
            return [$k, $v];
        }
        return $default;
    }
}
if (function_exists("ryunosuke\\dbml\\last_keyvalue") && !defined("ryunosuke\\dbml\\last_keyvalue")) {
    define("ryunosuke\\dbml\\last_keyvalue", "ryunosuke\\dbml\\last_keyvalue");
}

if (!isset($excluded_functions["array_implode"]) && (!function_exists("ryunosuke\\dbml\\array_implode") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_implode"))->isInternal()))) {
    /**
     * 配列の各要素の間に要素を差し込む
     *
     * 歴史的な理由はないが、引数をどちらの順番でも受けつけることが可能。
     * ただし、$glue を先に渡すパターンの場合は配列指定が可変引数渡しになる。
     *
     * 文字キーは保存されるが数値キーは再割り振りされる。
     *
     * Example:
     * ```php
     * // (配列, 要素) の呼び出し
     * assertSame(array_implode(['a', 'b', 'c'], 'X'), ['a', 'X', 'b', 'X', 'c']);
     * // (要素, ...配列) の呼び出し
     * assertSame(array_implode('X', 'a', 'b', 'c'), ['a', 'X', 'b', 'X', 'c']);
     * ```
     *
     * @param iterable|string $array 対象配列
     * @param string $glue 差し込む要素
     * @return array 差し込まれた配列
     */
    function array_implode($array, $glue)
    {
        // 第1引数が回せない場合は引数を入れ替えて可変引数パターン
        if (!is_array($array) && !$array instanceof \Traversable) {
            return array_implode(array_slice(func_get_args(), 1), $array);
        }

        $result = [];
        foreach ($array as $k => $v) {
            if (is_int($k)) {
                $result[] = $v;
            }
            else {
                $result[$k] = $v;
            }
            $result[] = $glue;
        }
        array_pop($result);
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\array_implode") && !defined("ryunosuke\\dbml\\array_implode")) {
    define("ryunosuke\\dbml\\array_implode", "ryunosuke\\dbml\\array_implode");
}

if (!isset($excluded_functions["array_sprintf"]) && (!function_exists("ryunosuke\\dbml\\array_sprintf") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_sprintf"))->isInternal()))) {
    /**
     * キーと値で sprintf する
     *
     * 配列の各要素を文字列化して返すイメージ。
     * $glue を与えるとさらに implode して返す（返り値が文字列になる）。
     *
     * $format は書式文字列（$v, $k）。
     * callable を与えると sprintf ではなくコールバック処理になる（$v, $k）。
     * 省略（null）するとキーを format 文字列、値を引数として **vsprintf** する。
     *
     * Example:
     * ```php
     * $array = ['key1' => 'val1', 'key2' => 'val2'];
     * // key, value を利用した sprintf
     * assertSame(array_sprintf($array, '%2$s=%1$s'), ['key1=val1', 'key2=val2']);
     * // 第3引数を与えるとさらに implode される
     * assertSame(array_sprintf($array, '%2$s=%1$s', ' '), 'key1=val1 key2=val2');
     * // クロージャを与えるとコールバック動作になる
     * $closure = function($v, $k){return "$k=" . strtoupper($v);};
     * assertSame(array_sprintf($array, $closure, ' '), 'key1=VAL1 key2=VAL2');
     * // 省略すると vsprintf になる
     * assertSame(array_sprintf([
     *     'str:%s,int:%d' => ['sss', '3.14'],
     *     'single:%s'     => 'str',
     * ], null, '|'), 'str:sss,int:3|single:str');
     * ```
     *
     * @param iterable $array 対象配列
     * @param string|callable $format 書式文字列あるいはクロージャ
     * @param string $glue 結合文字列。未指定時は implode しない
     * @return array|string sprintf された配列
     */
    function array_sprintf($array, $format = null, $glue = null)
    {
        if (is_callable($format)) {
            $callback = func_user_func_array($format);
        }
        elseif ($format === null) {
            $callback = function ($v, $k) { return vsprintf($k, is_array($v) ? $v : [$v]); };
        }
        else {
            $callback = function ($v, $k) use ($format) { return sprintf($format, $v, $k); };
        }

        $result = [];
        foreach ($array as $k => $v) {
            $result[] = $callback($v, $k);
        }

        if ($glue !== null) {
            return implode($glue, $result);
        }

        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\array_sprintf") && !defined("ryunosuke\\dbml\\array_sprintf")) {
    define("ryunosuke\\dbml\\array_sprintf", "ryunosuke\\dbml\\array_sprintf");
}

if (!isset($excluded_functions["array_strpad"]) && (!function_exists("ryunosuke\\dbml\\array_strpad") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_strpad"))->isInternal()))) {
    /**
     * 配列のキー・要素に文字列を付加する
     *
     * $key_prefix, $val_prefix でそれぞれ「キーに付与する文字列」「値に付与する文字列」が指定できる。
     * 配列を与えると [サフィックス, プレフィックス] という意味になる。
     * デフォルト（ただの文字列）はプレフィックス（値だけに付与したいなら array_map で十分なので）。
     *
     * Example:
     * ```php
     * $array = ['key1' => 'val1', 'key2' => 'val2'];
     * // キーにプレフィックス付与
     * assertSame(array_strpad($array, 'prefix-'), ['prefix-key1' => 'val1', 'prefix-key2' => 'val2']);
     * // 値にサフィックス付与
     * assertSame(array_strpad($array, '', ['-suffix']), ['key1' => 'val1-suffix', 'key2' => 'val2-suffix']);
     * ```
     *
     * @param iterable $array 対象配列
     * @param string|array $key_prefix キー側の付加文字列
     * @param string|array $val_prefix 値側の付加文字列
     * @return array 文字列付与された配列
     */
    function array_strpad($array, $key_prefix, $val_prefix = '')
    {
        $key_suffix = '';
        if (is_array($key_prefix)) {
            list($key_suffix, $key_prefix) = $key_prefix + [1 => ''];
        }
        $val_suffix = '';
        if (is_array($val_prefix)) {
            list($val_suffix, $val_prefix) = $val_prefix + [1 => ''];
        }

        $enable_key = strlen($key_prefix) || strlen($key_suffix);
        $enable_val = strlen($val_prefix) || strlen($val_suffix);

        $result = [];
        foreach ($array as $key => $val) {
            if ($enable_key) {
                $key = $key_prefix . $key . $key_suffix;
            }
            if ($enable_val) {
                $val = $val_prefix . $val . $val_suffix;
            }
            $result[$key] = $val;
        }
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\array_strpad") && !defined("ryunosuke\\dbml\\array_strpad")) {
    define("ryunosuke\\dbml\\array_strpad", "ryunosuke\\dbml\\array_strpad");
}

if (!isset($excluded_functions["array_get"]) && (!function_exists("ryunosuke\\dbml\\array_get") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_get"))->isInternal()))) {
    /**
     * デフォルト値付きの配列値取得
     *
     * 存在しない場合は $default を返す。
     *
     * $key に配列を与えるとそれらの値の配列を返す（lookup 的な動作）。
     * その場合、$default が活きるのは「全て無かった場合」となる。
     *
     * さらに $key が配列の場合に限り、 $default を省略すると空配列として動作する。
     *
     * 同様に、$key にクロージャを与えると、その返り値が true 相当のものを返す。
     * その際、 $default が配列なら一致するものを配列で返し、配列でないなら単値で返す。
     *
     * Example:
     * ```php
     * // 単純取得
     * assertSame(array_get(['a', 'b', 'c'], 1), 'b');
     * // 単純デフォルト
     * assertSame(array_get(['a', 'b', 'c'], 9, 999), 999);
     * // 配列取得
     * assertSame(array_get(['a', 'b', 'c'], [0, 2]), [0 => 'a', 2 => 'c']);
     * // 配列部分取得
     * assertSame(array_get(['a', 'b', 'c'], [0, 9]), [0 => 'a']);
     * // 配列デフォルト（null ではなく [] を返す）
     * assertSame(array_get(['a', 'b', 'c'], [9]), []);
     * // クロージャ指定＆単値（コールバックが true を返す最初の要素）
     * assertSame(array_get(['a', 'b', 'c'], function($v){return in_array($v, ['b', 'c']);}), 'b');
     * // クロージャ指定＆配列（コールバックが true を返すもの）
     * assertSame(array_get(['a', 'b', 'c'], function($v){return in_array($v, ['b', 'c']);}, []), [1 => 'b', 2 => 'c']);
     * ```
     *
     * @param array $array 配列
     * @param string|int|array $key 取得したいキー。配列を与えると全て返す。クロージャの場合は true 相当を返す
     * @param mixed $default 無かった場合のデフォルト値
     * @return mixed 指定したキーの値
     */
    function array_get($array, $key, $default = null)
    {
        if (is_array($key)) {
            $result = [];
            foreach ($key as $k) {
                // 深遠な事情で少しでも高速化したかったので isset || array_keys_exist にしてある
                if (isset($array[$k]) || array_keys_exist($k, $array)) {
                    $result[$k] = $array[$k];
                }
            }
            if (!$result) {
                // 明示的に与えられていないなら [] を使用する
                if (func_num_args() === 2) {
                    $default = [];
                }
                return $default;
            }
            return $result;
        }

        if ($key instanceof \Closure) {
            $result = [];
            foreach ($array as $k => $v) {
                if ($key($v, $k)) {
                    if (func_num_args() === 2) {
                        return $v;
                    }
                    $result[$k] = $v;
                }
            }
            if (!$result) {
                return $default;
            }
            return $result;
        }

        if (array_keys_exist($key, $array)) {
            return $array[$key];
        }
        return $default;
    }
}
if (function_exists("ryunosuke\\dbml\\array_get") && !defined("ryunosuke\\dbml\\array_get")) {
    define("ryunosuke\\dbml\\array_get", "ryunosuke\\dbml\\array_get");
}

if (!isset($excluded_functions["array_set"]) && (!function_exists("ryunosuke\\dbml\\array_set") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_set"))->isInternal()))) {
    /**
     * キー指定の配列値設定
     *
     * 第3引数を省略すると（null を与えると）言語機構を使用して配列の最後に設定する（$array[] = $value）。
     * 第3引数に配列を指定すると潜って設定する。
     *
     * Example:
     * ```php
     * $array = ['a' => 'A', 'B'];
     * // 第3引数省略（最後に連番キーで設定）
     * assertSame(array_set($array, 'Z'), 1);
     * assertSame($array, ['a' => 'A', 'B', 'Z']);
     * // 第3引数でキーを指定
     * assertSame(array_set($array, 'Z', 'z'), 'z');
     * assertSame($array, ['a' => 'A', 'B', 'Z', 'z' => 'Z']);
     * assertSame(array_set($array, 'Z', 'z'), 'z');
     * // 第3引数で配列を指定
     * assertSame(array_set($array, 'Z', ['x', 'y', 'z']), 'z');
     * assertSame($array, ['a' => 'A', 'B', 'Z', 'z' => 'Z', 'x' => ['y' => ['z' => 'Z']]]);
     * ```
     *
     * @param array $array 配列
     * @param mixed $value 設定する値
     * @param array|string|int|null $key 設定するキー
     * @param bool $require_return 返り値が不要なら false を渡す
     * @return string|int 設定したキー
     */
    function array_set(&$array, $value, $key = null, $require_return = true)
    {
        if (is_array($key)) {
            $k = array_shift($key);
            if ($key) {
                if (is_array($array) && array_key_exists($k, $array) && !is_array($array[$k])) {
                    throw new \InvalidArgumentException('$array[$k] is not array.');
                }
                return array_set(...[&$array[$k], $value, $key, $require_return]);
            }
            else {
                return array_set(...[&$array, $value, $k, $require_return]);
            }
        }

        if ($key === null) {
            $array[] = $value;
            if ($require_return === true) {
                $key = last_key($array);
            }
        }
        else {
            $array[$key] = $value;
        }
        return $key;
    }
}
if (function_exists("ryunosuke\\dbml\\array_set") && !defined("ryunosuke\\dbml\\array_set")) {
    define("ryunosuke\\dbml\\array_set", "ryunosuke\\dbml\\array_set");
}

if (!isset($excluded_functions["array_put"]) && (!function_exists("ryunosuke\\dbml\\array_put") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_put"))->isInternal()))) {
    /**
     * キー指定の配列値設定
     *
     * array_set とほとんど同じ。
     * 第3引数を省略すると（null を与えると）言語機構を使用して配列の最後に設定する（$array[] = $value）。
     * また、**int を与えても同様の動作**となる。
     * 第3引数に配列を指定すると潜って設定する。
     *
     * array_set における $require_return は廃止している。
     * これはもともと end や last_key が遅かったのでオプショナルにしていたが、もう改善しているし、7.3 から array_key_last があるので、呼び元で適宜使えば良い。
     *
     * Example:
     * ```php
     * $array = ['a' => 'A', 'B'];
     * // 第3引数 int
     * assertSame(array_put($array, 'Z', 999), 1);
     * assertSame($array, ['a' => 'A', 'B', 'Z']);
     * // 第3引数省略（最後に連番キーで設定）
     * assertSame(array_put($array, 'Z'), 2);
     * assertSame($array, ['a' => 'A', 'B', 'Z', 'Z']);
     * // 第3引数でキーを指定
     * assertSame(array_put($array, 'Z', 'z'), 'z');
     * assertSame($array, ['a' => 'A', 'B', 'Z', 'Z', 'z' => 'Z']);
     * assertSame(array_put($array, 'Z', 'z'), 'z');
     * // 第3引数で配列を指定
     * assertSame(array_put($array, 'Z', ['x', 'y', 'z']), 'z');
     * assertSame($array, ['a' => 'A', 'B', 'Z', 'Z', 'z' => 'Z', 'x' => ['y' => ['z' => 'Z']]]);
     * ```
     *
     * @param array $array 配列
     * @param mixed $value 設定する値
     * @param array|string|int|null $key 設定するキー
     * @return string|int 設定したキー
     */
    function array_put(&$array, $value, $key = null)
    {
        if (is_array($key)) {
            $k = array_shift($key);
            if ($key) {
                if (is_array($array) && array_key_exists($k, $array) && !is_array($array[$k])) {
                    throw new \InvalidArgumentException('$array[$k] is not array.');
                }
                return array_put(...[&$array[$k], $value, $key]);
            }
            else {
                return array_put(...[&$array, $value, $k]);
            }
        }

        if ($key === null || is_int($key)) {
            $array[] = $value;
            // compatible array_key_last under 7.3
            end($array);
            $key = key($array);
        }
        else {
            $array[$key] = $value;
        }
        return $key;
    }
}
if (function_exists("ryunosuke\\dbml\\array_put") && !defined("ryunosuke\\dbml\\array_put")) {
    define("ryunosuke\\dbml\\array_put", "ryunosuke\\dbml\\array_put");
}

if (!isset($excluded_functions["array_unset"]) && (!function_exists("ryunosuke\\dbml\\array_unset") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_unset"))->isInternal()))) {
    /**
     * 伏せると同時にその値を返す
     *
     * $key に配列を与えると全て伏せて配列で返す。
     * その場合、$default が活きるのは「全て無かった場合」となる。
     *
     * さらに $key が配列の場合に限り、 $default を省略すると空配列として動作する。
     *
     * 配列を与えた場合の返り値は与えた配列の順番・キーが活きる。
     * これを利用すると list の展開の利便性が上がったり、連想配列で返すことができる。
     *
     * 同様に、$key にクロージャを与えると、その返り値が true 相当のものを伏せて配列で返す。
     * callable ではなくクロージャのみ対応する。
     *
     * Example:
     * ```php
     * $array = ['a' => 'A', 'b' => 'B'];
     * // ない場合は $default を返す
     * assertSame(array_unset($array, 'x', 'X'), 'X');
     * // 指定したキーを返す。そのキーは伏せられている
     * assertSame(array_unset($array, 'a'), 'A');
     * assertSame($array, ['b' => 'B']);
     *
     * $array = ['a' => 'A', 'b' => 'B', 'c' => 'C'];
     * // 配列を与えるとそれらを返す。そのキーは全て伏せられている
     * assertSame(array_unset($array, ['a', 'b', 'x']), ['A', 'B']);
     * assertSame($array, ['c' => 'C']);
     *
     * $array = ['a' => 'A', 'b' => 'B', 'c' => 'C'];
     * // 配列のキーは返されるキーを表す。順番も維持される
     * assertSame(array_unset($array, ['x2' => 'b', 'x1' => 'a']), ['x2' => 'B', 'x1' => 'A']);
     *
     * $array = ['hoge' => 'HOGE', 'fuga' => 'FUGA', 'piyo' => 'PIYO'];
     * // 値に "G" を含むものを返す。その要素は伏せられている
     * assertSame(array_unset($array, function($v){return strpos($v, 'G') !== false;}), ['hoge' => 'HOGE', 'fuga' => 'FUGA']);
     * assertSame($array, ['piyo' => 'PIYO']);
     * ```
     *
     * @param array $array 配列
     * @param string|int|array|callable $key 伏せたいキー。配列を与えると全て伏せる。クロージャの場合は true 相当を伏せる
     * @param mixed $default 無かった場合のデフォルト値
     * @return mixed 指定したキーの値
     */
    function array_unset(&$array, $key, $default = null)
    {
        if (is_array($key)) {
            $result = [];
            foreach ($key as $rk => $ak) {
                if (array_keys_exist($ak, $array)) {
                    $result[$rk] = $array[$ak];
                    unset($array[$ak]);
                }
            }
            if (!$result) {
                // 明示的に与えられていないなら [] を使用する
                if (func_num_args() === 2) {
                    $default = [];
                }
                return $default;
            }
            return $result;
        }

        if ($key instanceof \Closure) {
            $result = [];
            foreach ($array as $k => $v) {
                if ($key($v, $k)) {
                    $result[$k] = $v;
                    unset($array[$k]);
                }
            }
            if (!$result) {
                return $default;
            }
            return $result;
        }

        if (array_keys_exist($key, $array)) {
            $result = $array[$key];
            unset($array[$key]);
            return $result;
        }
        return $default;
    }
}
if (function_exists("ryunosuke\\dbml\\array_unset") && !defined("ryunosuke\\dbml\\array_unset")) {
    define("ryunosuke\\dbml\\array_unset", "ryunosuke\\dbml\\array_unset");
}

if (!isset($excluded_functions["array_keys_exist"]) && (!function_exists("ryunosuke\\dbml\\array_keys_exist") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_keys_exist"))->isInternal()))) {
    /**
     * array_key_exists の複数版
     *
     * 指定キーが全て存在するなら true を返す。
     * 配列ではなく単一文字列を与えても動作する（array_key_exists と全く同じ動作になる）。
     *
     * $keys に空を与えると例外を投げる。
     * $keys に配列を与えるとキーで潜ってチェックする（Example 参照）。
     *
     * Example:
     * ```php
     * // すべて含むので true
     * assertTrue(array_keys_exist(['a', 'b', 'c'], ['a' => 'A', 'b' => 'B', 'c' => 'C']));
     * // N は含まないので false
     * assertFalse(array_keys_exist(['a', 'b', 'N'], ['a' => 'A', 'b' => 'B', 'c' => 'C']));
     * // 配列を与えると潜る（日本語で言えば「a というキーと、x というキーとその中に x1, x2 というキーがあるか？」）
     * assertTrue(array_keys_exist(['a', 'x' => ['x1', 'x2']], ['a' => 'A', 'x' => ['x1' => 'X1', 'x2' => 'X2']]));
     * ```
     *
     * @param array|string $keys 調べるキー
     * @param array|\ArrayAccess $array 調べる配列
     * @return bool 指定キーが全て存在するなら true
     */
    function array_keys_exist($keys, $array)
    {
        $keys = is_iterable($keys) ? $keys : [$keys];
        if (is_empty($keys)) {
            throw new \InvalidArgumentException('$keys is empty.');
        }

        $is_arrayaccess = $array instanceof \ArrayAccess;

        foreach ($keys as $k => $key) {
            if (is_array($key)) {
                // まずそのキーをチェックして
                if (!array_keys_exist($k, $array)) {
                    return false;
                }
                // あるなら再帰する
                if (!array_keys_exist($key, $array[$k])) {
                    return false;
                }
            }
            elseif ($is_arrayaccess) {
                if (!$array->offsetExists($key)) {
                    return false;
                }
            }
            elseif (!array_key_exists($key, $array)) {
                return false;
            }
        }
        return true;
    }
}
if (function_exists("ryunosuke\\dbml\\array_keys_exist") && !defined("ryunosuke\\dbml\\array_keys_exist")) {
    define("ryunosuke\\dbml\\array_keys_exist", "ryunosuke\\dbml\\array_keys_exist");
}

if (!isset($excluded_functions["array_find"]) && (!function_exists("ryunosuke\\dbml\\array_find") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_find"))->isInternal()))) {
    /**
     * array_search のクロージャ版のようなもの
     *
     * コールバックの返り値が true 相当のものを返す。
     * $is_key に true を与えるとそのキーを返す（デフォルトの動作）。
     * $is_key に false を与えるとコールバックの結果を返す。
     *
     * この関数は論理値 FALSE を返す可能性がありますが、FALSE として評価される値を返す可能性もあります。
     *
     * Example:
     * ```php
     * // 最初に見つかったキーを返す
     * assertSame(array_find(['a', 'b', '9'], 'ctype_digit'), 2);
     * assertSame(array_find(['a', 'b', '9'], function($v){return $v === 'b';}), 1);
     * // 最初に見つかったコールバック結果を返す（最初の数字の2乗を返す）
     * $ifnumeric2power = function($v){return ctype_digit($v) ? $v * $v : false;};
     * assertSame(array_find(['a', 'b', '9'], $ifnumeric2power, false), 81);
     * ```
     *
     * @param iterable $array 調べる配列
     * @param callable $callback 評価コールバック
     * @param bool $is_key キーを返すか否か
     * @return mixed コールバックが true を返した最初のキー。存在しなかったら false
     */
    function array_find($array, $callback, $is_key = true)
    {
        $callback = func_user_func_array($callback);

        foreach ($array as $k => $v) {
            $result = $callback($v, $k);
            if ($result) {
                if ($is_key) {
                    return $k;
                }
                return $result;
            }
        }
        return false;
    }
}
if (function_exists("ryunosuke\\dbml\\array_find") && !defined("ryunosuke\\dbml\\array_find")) {
    define("ryunosuke\\dbml\\array_find", "ryunosuke\\dbml\\array_find");
}

if (!isset($excluded_functions["array_map_key"]) && (!function_exists("ryunosuke\\dbml\\array_map_key") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_map_key"))->isInternal()))) {
    /**
     * キーをマップして変換する
     *
     * $callback が null を返すとその要素は取り除かれる。
     *
     * Example:
     * ```php
     * assertSame(array_map_key(['a' => 'A', 'b' => 'B'], 'strtoupper'), ['A' => 'A', 'B' => 'B']);
     * assertSame(array_map_key(['a' => 'A', 'b' => 'B'], function(){}), []);
     * ```
     *
     * @param iterable $array 対象配列
     * @param callable $callback 評価クロージャ
     * @return array キーが変換された新しい配列
     */
    function array_map_key($array, $callback)
    {
        $result = [];
        foreach ($array as $k => $v) {
            $k2 = $callback($k);
            if ($k2 !== null) {
                $result[$k2] = $v;
            }
        }
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\array_map_key") && !defined("ryunosuke\\dbml\\array_map_key")) {
    define("ryunosuke\\dbml\\array_map_key", "ryunosuke\\dbml\\array_map_key");
}

if (!isset($excluded_functions["array_map_filter"]) && (!function_exists("ryunosuke\\dbml\\array_map_filter") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_map_filter"))->isInternal()))) {
    /**
     * array_map + array_filter する
     *
     * コールバックを適用して、結果が true 相当の要素のみ取り出す。
     * $strict に true を与えると「null でない」要素のみ返される。
     *
     * $callback が要求するならキーも渡ってくる。
     *
     * Example:
     * ```php
     * assertSame(array_map_filter([' a ', ' b ', ''], 'trim'), ['a', 'b']);
     * assertSame(array_map_filter([' a ', ' b ', ''], 'trim', true), ['a', 'b', '']);
     * ```
     *
     * @param iterable $array 対象配列
     * @param callable $callback 評価クロージャ
     * @param bool $strict 厳密比較フラグ。 true だと null のみが偽とみなされる
     * @return array $callback が真を返した新しい配列
     */
    function array_map_filter($array, $callback, $strict = false)
    {
        $callback = func_user_func_array($callback);
        $result = [];
        foreach ($array as $k => $v) {
            $vv = $callback($v, $k);
            if (($strict && $vv !== null) || (!$strict && $vv)) {
                $result[$k] = $vv;
            }
        }
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\array_map_filter") && !defined("ryunosuke\\dbml\\array_map_filter")) {
    define("ryunosuke\\dbml\\array_map_filter", "ryunosuke\\dbml\\array_map_filter");
}

if (!isset($excluded_functions["array_maps"]) && (!function_exists("ryunosuke\\dbml\\array_maps") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_maps"))->isInternal()))) {
    /**
     * 複数コールバックを指定できる array_map
     *
     * 指定したコールバックで複数回回してマップする。
     * `array_maps($array, $f, $g)` は `array_map($g, array_map($f, $array))` とほぼ等しい。
     * ただし、引数は順番が違う（可変引数のため）し、コールバックが要求するならキーも渡ってくる。
     *
     * 少し変わった仕様として、コールバックに [$method => $args] を付けるとそれはメソッド呼び出しになる。
     * つまり各要素 $v に対して `$v->$method(...$args)` がマップ結果になる。
     * さらに引数が不要なら `@method` とするだけで良い。
     *
     * Example:
     * ```php
     * // 値を3乗したあと16進表記にして大文字化する
     * assertSame(array_maps([1, 2, 3, 4, 5], rbind('pow', 3), 'dechex', 'strtoupper'), ['1', '8', '1B', '40', '7D']);
     * // キーも渡ってくる
     * assertSame(array_maps(['a' => 'A', 'b' => 'B'], function($v, $k){return "$k:$v";}), ['a' => 'a:A', 'b' => 'b:B']);
     * // メソッドコールもできる（引数不要なら `@method` でも同じ）
     * assertSame(array_maps([new \Exception('a'), new \Exception('b')], ['getMessage' => []]), ['a', 'b']);
     * assertSame(array_maps([new \Exception('a'), new \Exception('b')], '@getMessage'), ['a', 'b']);
     * ```
     *
     * @param iterable $array 対象配列
     * @param callable[] $callbacks 評価クロージャ配列
     * @return array 評価クロージャを通した新しい配列
     */
    function array_maps($array, ...$callbacks)
    {
        $result = arrayval($array, false);
        foreach ($callbacks as $callback) {
            if (is_string($callback) && $callback[0] === '@') {
                $margs = [];
                $callback = substr($callback, 1);
            }
            elseif (is_array($callback) && count($callback) === 1) {
                $margs = reset($callback);
                $callback = key($callback);
            }
            else {
                $margs = null;
                $callback = func_user_func_array($callback);
            }
            foreach ($result as $k => $v) {
                if (isset($margs)) {
                    $result[$k] = ([$v, $callback])(...$margs);
                }
                else {
                    $result[$k] = $callback($v, $k);
                }
            }
        }
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\array_maps") && !defined("ryunosuke\\dbml\\array_maps")) {
    define("ryunosuke\\dbml\\array_maps", "ryunosuke\\dbml\\array_maps");
}

if (!isset($excluded_functions["array_kmap"]) && (!function_exists("ryunosuke\\dbml\\array_kmap") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_kmap"))->isInternal()))) {
    /**
     * キーも渡ってくる array_map
     *
     * `array_map($callback, $array, array_keys($array))` とほとんど変わりはない。
     * 違いは下記。
     *
     * - 引数の順番が異なる（$array が先）
     * - キーが死なない（array_map は複数配列を与えるとキーが死ぬ）
     * - 配列だけでなく Traversable も受け入れる
     * - callback の第3引数に 0 からの連番が渡ってくる
     *
     * Example:
     * ```php
     * // キー・値をくっつけるシンプルな例
     * assertSame(array_kmap([
     *     'k1' => 'v1',
     *     'k2' => 'v2',
     *     'k3' => 'v3',
     * ], function($v, $k){return "$k:$v";}), [
     *     'k1' => 'k1:v1',
     *     'k2' => 'k2:v2',
     *     'k3' => 'k3:v3',
     * ]);
     * ```
     *
     * @param iterable $array 対象配列
     * @param callable $callback 評価クロージャ
     * @return array $callback を通した新しい配列
     */
    function array_kmap($array, $callback)
    {
        $callback = func_user_func_array($callback);

        $n = 0;
        $result = [];
        foreach ($array as $k => $v) {
            $result[$k] = $callback($v, $k, $n++);
        }
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\array_kmap") && !defined("ryunosuke\\dbml\\array_kmap")) {
    define("ryunosuke\\dbml\\array_kmap", "ryunosuke\\dbml\\array_kmap");
}

if (!isset($excluded_functions["array_nmap"]) && (!function_exists("ryunosuke\\dbml\\array_nmap") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_nmap"))->isInternal()))) {
    /**
     * 要素値を $callback の n 番目(0ベース)に適用して array_map する
     *
     * 引数 $n に配列を与えると [キー番目 => 値番目] とみなしてキー・値も渡される（Example 参照）。
     * その際、「挿入後の番目」ではなく、単純に「元の引数の番目」であることに留意。キー・値が同じ位置を指定している場合はキーが先にくる。
     *
     * Example:
     * ```php
     * // 1番目に値を渡して map
     * $sprintf = function(){return vsprintf('%s%s%s', func_get_args());};
     * assertSame(array_nmap(['a', 'b'], $sprintf, 1, 'prefix-', '-suffix'), ['prefix-a-suffix', 'prefix-b-suffix']);
     * // 1番目にキー、2番目に値を渡して map
     * $sprintf = function(){return vsprintf('%s %s %s %s %s', func_get_args());};
     * assertSame(array_nmap(['k' => 'v'], $sprintf, [1 => 2], 'a', 'b', 'c'), ['k' => 'a k b v c']);
     * ```
     *
     * @param iterable $array 対象配列
     * @param callable $callback 評価クロージャ
     * @param int|array $n 要素値を入れる引数番目。配列を渡すとキー・値の両方を指定でき、両方が渡ってくる
     * @param mixed $variadic $callback に渡され、改変される引数（可変引数）
     * @return array 評価クロージャを通した新しい配列
     */
    function array_nmap($array, $callback, $n, ...$variadic)
    {
        /** @var $kn */
        /** @var $vn */

        $is_array = is_array($n);
        $args = $variadic;

        // 配列が来たら [キー番目 => 値番目] とみなす
        if ($is_array) {
            if (empty($n)) {
                throw new \InvalidArgumentException('array $n is empty.');
            }
            list($kn, $vn) = first_keyvalue($n);

            // array_insert は負数も受け入れられるが、それを考慮しだすともう収拾がつかない
            if ($kn < 0 || $vn < 0) {
                throw new \InvalidArgumentException('$kn, $vn must be positive.');
            }

            // どちらが大きいかで順番がズレるので分岐しなければならない
            if ($kn <= $vn) {
                $args = array_insert($args, null, $kn);
                $args = array_insert($args, null, ++$vn);// ↑で挿入してるので+1
            }
            else {
                $args = array_insert($args, null, $vn);
                $args = array_insert($args, null, ++$kn);// ↑で挿入してるので+1
            }
        }
        else {
            $args = array_insert($args, null, $n);
        }

        $result = [];
        foreach ($array as $k => $v) {
            // キー値モードなら両方埋める
            if ($is_array) {
                $args[$kn] = $k;
                $args[$vn] = $v;
            }
            // 値のみなら値だけ
            else {
                $args[$n] = $v;
            }
            $result[$k] = $callback(...$args);
        }
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\array_nmap") && !defined("ryunosuke\\dbml\\array_nmap")) {
    define("ryunosuke\\dbml\\array_nmap", "ryunosuke\\dbml\\array_nmap");
}

if (!isset($excluded_functions["array_rmap"]) && (!function_exists("ryunosuke\\dbml\\array_rmap") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_rmap"))->isInternal()))) {
    /**
     * 要素値を $callback の最右に適用して array_map する
     *
     * Example:
     * ```php
     * $sprintf = function(){return vsprintf('%s%s', func_get_args());};
     * assertSame(array_rmap(['a', 'b'], $sprintf, 'prefix-'), ['prefix-a', 'prefix-b']);
     * ```
     *
     * @param iterable $array 対象配列
     * @param callable $callback 評価クロージャ
     * @param mixed $variadic $callback に渡され、改変される引数（可変引数）
     * @return array 評価クロージャを通した新しい配列
     */
    function array_rmap($array, $callback, ...$variadic)
    {
        return array_nmap(...array_insert(func_get_args(), func_num_args() - 2, 2));
    }
}
if (function_exists("ryunosuke\\dbml\\array_rmap") && !defined("ryunosuke\\dbml\\array_rmap")) {
    define("ryunosuke\\dbml\\array_rmap", "ryunosuke\\dbml\\array_rmap");
}

if (!isset($excluded_functions["array_each"]) && (!function_exists("ryunosuke\\dbml\\array_each") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_each"))->isInternal()))) {
    /**
     * array_reduce の参照版（のようなもの）
     *
     * 配列をループで回し、その途中経過、値、キー、連番をコールバック引数で渡して最終的な結果を返り値として返す。
     * array_reduce と少し似てるが、下記の点が異なる。
     *
     * - いわゆる $carry は返り値で表すのではなく、参照引数で表す
     * - 値だけでなくキー、連番も渡ってくる
     * - 巨大配列の場合でも速度劣化が少ない（array_reduce に巨大配列を渡すと実用にならないレベルで遅くなる）
     *
     * $callback の引数は `($value, $key, $n)` （$n はキーとは関係がない 0 ～ 要素数-1 の通し連番）。
     *
     * 返り値ではなく参照引数なので return する必要はない（ワンライナーが書きやすくなる）。
     * 返り値が空くのでループ制御に用いる。
     * 今のところ $callback が false を返すとそこで break するのみ。
     *
     * 第3引数を省略した場合、**クロージャの第1引数のデフォルト値が使われる**。
     * これは特筆すべき動作で、不格好な第3引数を完全に省略することができる（サンプルコードを参照）。
     * ただし「php の文法違反（今のところエラーにはならないし、全てにデフォルト値をつければ一応回避可能）」「リフレクションを使う（ほんの少し遅くなる）」などの弊害が有るので推奨はしない。
     * （ただ、「意図していることをコードで表す」といった観点ではこの記法の方が正しいとも思う）。
     *
     * Example:
     * ```php
     * // 全要素を文字列的に足し合わせる
     * assertSame(array_each([1, 2, 3, 4, 5], function(&$carry, $v){$carry .= $v;}, ''), '12345');
     * // 値をキーにして要素を2乗値にする
     * assertSame(array_each([1, 2, 3, 4, 5], function(&$carry, $v){$carry[$v] = $v * $v;}, []), [
     *     1 => 1,
     *     2 => 4,
     *     3 => 9,
     *     4 => 16,
     *     5 => 25,
     * ]);
     * // 上記と同じ。ただし、3 で break する
     * assertSame(array_each([1, 2, 3, 4, 5], function(&$carry, $v, $k){
     *     if ($k === 3) return false;
     *     $carry[$v] = $v * $v;
     * }, []), [
     *     1 => 1,
     *     2 => 4,
     *     3 => 9,
     * ]);
     *
     * // 下記は完全に同じ（第3引数の代わりにデフォルト引数を使っている）
     * assertSame(
     *     array_each([1, 2, 3], function(&$carry = [], $v) {
     *         $carry[$v] = $v * $v;
     *     }),
     *     array_each([1, 2, 3], function(&$carry, $v) {
     *         $carry[$v] = $v * $v;
     *     }, [])
     *     // 個人的に↑のようなぶら下がり引数があまり好きではない（クロージャを最後の引数にしたい）
     * );
     * ```
     *
     * @param iterable $array 対象配列
     * @param callable $callback 評価クロージャ。(&$carry, $key, $value) を受ける
     * @param mixed $default ループの最初や空の場合に適用される値
     * @return mixed each した結果
     */
    function array_each($array, $callback, $default = null)
    {
        if (func_num_args() === 2) {
            /** @var \ReflectionFunction $ref */
            $ref = reflect_callable($callback);
            $params = $ref->getParameters();
            if ($params[0]->isDefaultValueAvailable()) {
                $default = $params[0]->getDefaultValue();
            }
        }

        $n = 0;
        foreach ($array as $k => $v) {
            $return = $callback($default, $v, $k, $n++);
            if ($return === false) {
                break;
            }
        }
        return $default;
    }
}
if (function_exists("ryunosuke\\dbml\\array_each") && !defined("ryunosuke\\dbml\\array_each")) {
    define("ryunosuke\\dbml\\array_each", "ryunosuke\\dbml\\array_each");
}

if (!isset($excluded_functions["array_depth"]) && (!function_exists("ryunosuke\\dbml\\array_depth") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_depth"))->isInternal()))) {
    /**
     * 配列の次元数を返す
     *
     * フラット配列は 1 と定義する。
     * つまり、配列を与える限りは 0 以下を返すことはない。
     *
     * 第2引数 $max_depth を与えるとその階層になった時点で走査を打ち切る。
     * 「1階層のみか？」などを調べるときは指定したほうが高速に動作する。
     *
     * Example:
     * ```php
     * assertSame(array_depth([]), 1);
     * assertSame(array_depth(['hoge']), 1);
     * assertSame(array_depth([['nest1' => ['nest2']]]), 3);
     * ```
     *
     * @param array $array 調べる配列
     * @param int|null $max_depth 最大階層数
     * @return int 次元数。素のフラット配列は 1
     */
    function array_depth($array, $max_depth = null)
    {
        assert((is_null($max_depth)) || $max_depth > 0);

        $main = function ($array, $depth) use (&$main, $max_depth) {
            // $max_depth を超えているなら打ち切る
            if ($max_depth !== null && $depth >= $max_depth) {
                return 1;
            }

            // 配列以外に興味はない
            $arrays = array_filter($array, 'is_array');

            // ネストしない配列は 1 と定義
            if (!$arrays) {
                return 1;
            }

            // 配下の内で最大を返す
            return 1 + max(array_map(function ($v) use ($main, $depth) { return $main($v, $depth + 1); }, $arrays));
        };

        return $main($array, 1);
    }
}
if (function_exists("ryunosuke\\dbml\\array_depth") && !defined("ryunosuke\\dbml\\array_depth")) {
    define("ryunosuke\\dbml\\array_depth", "ryunosuke\\dbml\\array_depth");
}

if (!isset($excluded_functions["array_insert"]) && (!function_exists("ryunosuke\\dbml\\array_insert") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_insert"))->isInternal()))) {
    /**
     * 配列・連想配列を問わず任意の位置に値を挿入する
     *
     * $position を省略すると最後に挿入される（≒ array_push）。
     * $position に負数を与えると後ろから数えられる。
     * $value には配列も与えられるが、その場合数値キーは振り直される
     *
     * Example:
     * ```php
     * assertSame(array_insert([1, 2, 3], 'x'), [1, 2, 3, 'x']);
     * assertSame(array_insert([1, 2, 3], 'x', 1), [1, 'x', 2, 3]);
     * assertSame(array_insert([1, 2, 3], 'x', -1), [1, 2, 'x', 3]);
     * assertSame(array_insert([1, 2, 3], ['a' => 'A', 'b' => 'B'], 1), [1, 'a' => 'A', 'b' => 'B', 2, 3]);
     * ```
     *
     * @param array $array 対象配列
     * @param mixed $value 挿入値
     * @param int|null $position 挿入位置
     * @return array 挿入された新しい配列
     */
    function array_insert($array, $value, $position = null)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $position = is_null($position) ? count($array) : intval($position);

        $sarray = array_splice($array, 0, $position);
        return array_merge($sarray, $value, $array);
    }
}
if (function_exists("ryunosuke\\dbml\\array_insert") && !defined("ryunosuke\\dbml\\array_insert")) {
    define("ryunosuke\\dbml\\array_insert", "ryunosuke\\dbml\\array_insert");
}

if (!isset($excluded_functions["array_all"]) && (!function_exists("ryunosuke\\dbml\\array_all") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_all"))->isInternal()))) {
    /**
     * 全要素が true になるなら true を返す（1つでも false なら false を返す）
     *
     * $callback が要求するならキーも渡ってくる。
     *
     * Example:
     * ```php
     * assertTrue(array_all([true, true]));
     * assertFalse(array_all([true, false]));
     * assertFalse(array_all([false, false]));
     * ```
     *
     * @param iterable 対象配列
     * @param callable $callback 評価クロージャ。 null なら値そのもので評価
     * @param bool|mixed $default 空配列の場合のデフォルト値
     * @return bool 全要素が true なら true
     */
    function array_all($array, $callback = null, $default = true)
    {
        if (is_empty($array)) {
            return $default;
        }

        $callback = func_user_func_array($callback);

        foreach ($array as $k => $v) {
            if (!$callback($v, $k)) {
                return false;
            }
        }
        return true;
    }
}
if (function_exists("ryunosuke\\dbml\\array_all") && !defined("ryunosuke\\dbml\\array_all")) {
    define("ryunosuke\\dbml\\array_all", "ryunosuke\\dbml\\array_all");
}

if (!isset($excluded_functions["array_order"]) && (!function_exists("ryunosuke\\dbml\\array_order") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_order"))->isInternal()))) {
    /**
     * 配列を $orders に従って並べ替える
     *
     * データベースからフェッチしたような連想配列の配列を想定しているが、スカラー配列(['key' => 'value'])にも対応している。
     * その場合 $orders に配列ではなく直値を渡せば良い。
     *
     * $orders には下記のような配列を渡す。
     *
     * ```php
     * $orders = [
     *     'col1' => true,                               // true: 昇順, false: 降順。照合は型に依存
     *     'col2' => SORT_NATURAL,                       // SORT_NATURAL, SORT_REGULAR などで照合。正数で昇順、負数で降順
     *     'col3' => ['sort', 'this', 'order'],          // 指定した配列順で昇順
     *     'col4' => function($v) {return $v;},          // クロージャを通した値で昇順。照合は返り値の型に依存
     *     'col5' => function($a, $b) {return $a - $b;}, // クロージャで比較して昇順（いわゆる比較関数を渡す）
     * ];
     * ```
     *
     * Example:
     * ```php
     * $v1 = ['id' => '1', 'no' => 'a03', 'name' => 'yyy'];
     * $v2 = ['id' => '2', 'no' => 'a4',  'name' => 'yyy'];
     * $v3 = ['id' => '3', 'no' => 'a12', 'name' => 'xxx'];
     * // name 昇順, no 自然降順
     * assertSame(array_order([$v1, $v2, $v3], ['name' => true, 'no' => -SORT_NATURAL]), [$v3, $v2, $v1]);
     * ```
     *
     * @param array $array 対象配列
     * @param mixed $orders ソート順
     * @param bool $preserve_keys キーを保存するか。 false の場合数値キーは振り直される
     * @return array 並び替えられた配列
     */
    function array_order(array $array, $orders, $preserve_keys = false)
    {
        if (count($array) <= 1) {
            return $array;
        }

        if (!is_array($orders) || !is_hasharray($orders)) {
            $orders = [$orders];
        }

        // 配列内の位置をマップして返すクロージャ
        $position = function ($columns, $order) {
            return array_map(function ($v) use ($order) {
                $ndx = array_search($v, $order, true);
                return $ndx === false ? count($order) : $ndx;
            }, $columns);
        };

        // 全要素は舐めてられないので最初の要素を代表選手としてピックアップ
        $first = reset($array);
        $is_scalar = is_scalar($first) || is_null($first);

        // array_multisort 用の配列を生成
        $args = [];
        foreach ($orders as $key => $order) {
            if ($is_scalar) {
                $firstval = reset($array);
                $columns = $array;
            }
            else {
                if ($key !== '' && !array_key_exists($key, $first)) {
                    throw new \InvalidArgumentException("$key is undefined.");
                }
                if ($key === '') {
                    $columns = array_keys($array);
                    $firstval = reset($columns);
                }
                else {
                    $firstval = $first[$key];
                    $columns = array_column($array, $key);
                }
            }

            // bool は ASC, DESC
            if (is_bool($order)) {
                $args[] = $columns;
                $args[] = $order ? SORT_ASC : SORT_DESC;
                $args[] = is_string($firstval) ? SORT_STRING : SORT_NUMERIC;
            }
            // int は SORT_*****
            elseif (is_int($order)) {
                $args[] = $columns;
                $args[] = $order > 0 ? SORT_ASC : SORT_DESC;
                $args[] = abs($order);
            }
            // 配列はその並び
            elseif (is_array($order)) {
                $args[] = $position($columns, $order);
                $args[] = SORT_ASC;
                $args[] = SORT_NUMERIC;
            }
            // クロージャは色々
            elseif ($order instanceof \Closure) {
                $ref = new \ReflectionFunction($order);
                // 引数2個なら比較関数
                if ($ref->getNumberOfRequiredParameters() === 2) {
                    $map = $columns;
                    usort($map, $order);
                    $args[] = $position($columns, $map);
                    $args[] = SORT_ASC;
                    $args[] = SORT_NUMERIC;
                }
                // でないなら通した値で比較
                else {
                    $arg = array_map($order, $columns);
                    $type = $ref->hasReturnType() ? (string) $ref->getReturnType() : gettype(reset($arg));
                    $args[] = $arg;
                    $args[] = SORT_ASC;
                    $args[] = $type === 'string' ? SORT_STRING : SORT_NUMERIC;
                }
            }
            else {
                throw new \DomainException('$order is invalid.');
            }
        }

        // array_multisort はキーを保持しないので、ソートされる配列にキー配列を加えて後で combine する
        if ($preserve_keys) {
            $keys = array_keys($array);
            $args[] = &$array;
            $args[] = &$keys;
            array_multisort(...$args);
            return array_combine($keys, $array);
        }
        // キーを保持しないなら単純呼び出しで OK
        else {
            $args[] = &$array;
            array_multisort(...$args);
            return $array;
        }
    }
}
if (function_exists("ryunosuke\\dbml\\array_order") && !defined("ryunosuke\\dbml\\array_order")) {
    define("ryunosuke\\dbml\\array_order", "ryunosuke\\dbml\\array_order");
}

if (!isset($excluded_functions["array_pickup"]) && (!function_exists("ryunosuke\\dbml\\array_pickup") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_pickup"))->isInternal()))) {
    /**
     * キーを指定してそれだけの配列にする
     *
     * `array_intersect_key($array, array_flip($keys))` とほぼ同義。
     * 違いは Traversable を渡せることと、結果配列の順番が $keys に従うこと。
     *
     * $keys に連想配列を渡すとキーを読み替えて動作する（Example を参照）。
     *
     * Example:
     * ```php
     * $array = ['a' => 'A', 'b' => 'B', 'c' => 'C'];
     * // a と c を取り出す
     * assertSame(array_pickup($array, ['a', 'c']), ['a' => 'A', 'c' => 'C']);
     * // 順番は $keys 基準になる
     * assertSame(array_pickup($array, ['c', 'a']), ['c' => 'C', 'a' => 'A']);
     * // 連想配列を渡すと読み替えて返す
     * assertSame(array_pickup($array, ['c' => 'cX', 'a' => 'aX']), ['cX' => 'C', 'aX' => 'A']);
     * ```
     *
     * @param iterable $array 対象配列
     * @param array $keys 取り出すキー
     * @return array 新しい配列
     */
    function array_pickup($array, $keys)
    {
        $array = arrayval($array, false);

        $result = [];
        foreach (arrayval($keys, false) as $k => $key) {
            if (is_int($k)) {
                if (array_key_exists($key, $array)) {
                    $result[$key] = $array[$key];
                }
            }
            else {
                if (array_key_exists($k, $array)) {
                    $result[$key] = $array[$k];
                }
            }
        }
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\array_pickup") && !defined("ryunosuke\\dbml\\array_pickup")) {
    define("ryunosuke\\dbml\\array_pickup", "ryunosuke\\dbml\\array_pickup");
}

if (!isset($excluded_functions["array_lookup"]) && (!function_exists("ryunosuke\\dbml\\array_lookup") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_lookup"))->isInternal()))) {
    /**
     * キー保存可能な array_column
     *
     * array_column は キーを保存することが出来ないが、この関数は引数を2つだけ与えるとキーはそのままで array_column 相当の配列を返す。
     *
     * Example:
     * ```php
     * $array = [
     *     11 => ['id' => 1, 'name' => 'name1'],
     *     12 => ['id' => 2, 'name' => 'name2'],
     *     13 => ['id' => 3, 'name' => 'name3'],
     * ];
     * // 第3引数を渡せば array_column と全く同じ
     * assertSame(array_lookup($array, 'name', 'id'), array_column($array, 'name', 'id'));
     * assertSame(array_lookup($array, 'name', null), array_column($array, 'name', null));
     * // 省略すればキーが保存される
     * assertSame(array_lookup($array, 'name'), [
     *     11 => 'name1',
     *     12 => 'name2',
     *     13 => 'name3',
     * ]);
     * ```
     *
     * @param iterable $array 対象配列
     * @param string|null $column_key 値となるキー
     * @param string|null $index_key キーとなるキー
     * @return array 新しい配列
     */
    function array_lookup($array, $column_key = null, $index_key = null)
    {
        if (func_num_args() === 3) {
            return array_column(arrayval($array, false), $column_key, $index_key);
        }

        // null 対応できないし、php7 からオブジェクトに対応してるらしいので止め。ベタにやる
        // return array_map(array_of($column_keys), $array);

        // 実質的にはこれで良いはずだが、オブジェクト対応が救えないので止め。ベタにやる
        // return array_combine(array_keys($array), array_column($array, $column_key));

        $result = [];
        foreach ($array as $k => $v) {
            if ($column_key === null) {
                $result[$k] = $v;
            }
            elseif (is_array($v) && array_key_exists($column_key, $v)) {
                $result[$k] = $v[$column_key];
            }
            elseif (is_object($v) && (isset($v->$column_key) || property_exists($v, $column_key))) {
                $result[$k] = $v->$column_key;
            }
        }
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\array_lookup") && !defined("ryunosuke\\dbml\\array_lookup")) {
    define("ryunosuke\\dbml\\array_lookup", "ryunosuke\\dbml\\array_lookup");
}

if (!isset($excluded_functions["array_uncolumns"]) && (!function_exists("ryunosuke\\dbml\\array_uncolumns") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_uncolumns"))->isInternal()))) {
    /**
     * array_columns のほぼ逆で [キー => [要素]] 配列から連想配列の配列を生成する
     *
     * $template を指定すると「それに含まれる配列かつ値がデフォルト」になる（要するに $default みたいなもの）。
     * キーがバラバラな配列を指定する場合は指定したほうが良い。が、null を指定すると最初の要素が使われるので大抵の場合は null で良い。
     *
     * Example:
     * ```php
     * assertSame(array_uncolumns([
     *     'id'   => [1, 2],
     *     'name' => ['A', 'B'],
     * ]), [
     *     ['id' => 1, 'name' => 'A'],
     *     ['id' => 2, 'name' => 'B'],
     * ]);
     * ```
     *
     * @param array $array 対象配列
     * @param array $template 抽出要素とそのデフォルト値
     * @return array 新しい配列
     */
    function array_uncolumns($array, $template = null)
    {
        // 指定されていないなら生のまま
        if (func_num_args() === 1) {
            $template = false;
        }
        // null なら最初の要素のキー・null
        if ($template === null) {
            $template = array_fill_keys(array_keys(first_value($array)), null);
        }

        $result = [];
        foreach ($array as $key => $vals) {
            if ($template !== false) {
                $vals = array_intersect_key($vals + $template, $template);
            }
            foreach ($vals as $n => $val) {
                $result[$n][$key] = $val;
            }
        }
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\array_uncolumns") && !defined("ryunosuke\\dbml\\array_uncolumns")) {
    define("ryunosuke\\dbml\\array_uncolumns", "ryunosuke\\dbml\\array_uncolumns");
}

if (!isset($excluded_functions["array_convert"]) && (!function_exists("ryunosuke\\dbml\\array_convert") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_convert"))->isInternal()))) {
    /**
     * 配列の各要素に再帰的にコールバックを適用して変換する
     *
     * $callback は下記の仕様。
     *
     * 引数は (キー, 値, 今まで処理したキー配列) で渡ってくる。
     * 返り値は新しいキーを返す。
     *
     * - 文字列や数値を返すとそれがキーとして使われる
     * - null を返すと元のキーがそのまま使われる
     * - true を返すと数値連番が振られる
     * - false を返すとその要素は無かったことになる
     * - 配列を返すとその配列で完全に置換される
     *
     * $apply_array=false で要素が配列の場合は再帰され、コールバックが適用されない（array_walk_recursive と同じ仕様）。
     *
     * $apply_array=true だと配列かは問わず全ての要素にコールバックが適用される。
     * 配列も渡ってきてしまうのでコールバック内部で is_array 判定が必要になる場合がある。
     *
     * 「map も filter も可能でキー変更可能かつ再帰的」というとてもマッチョな関数。
     * 複雑だが実質的には「キーも設定できる array_walk_recursive」のように振る舞う（そしてそのような使い方を想定している）。
     *
     * Example:
     * ```php
     * $array = [
     *    'k1' => 'v1',
     *    'k2' => [
     *        'k21' => 'v21',
     *        'k22' => [
     *            'k221' => 'v221',
     *            'k222' => 'v222',
     *        ],
     *        'k23' => 'v23',
     *    ],
     * ];
     * // 全要素に 'prefix-' を付与する。キーには '_' をつける。ただし 'k21' はそのままとする。さらに 'k22' はまるごと伏せる。 'k23' は数値キーになる
     * $callback = function($k, &$v){
     *     if ($k === 'k21') return null;
     *     if ($k === 'k22') return false;
     *     if ($k === 'k23') return true;
     *     if (!is_array($v)) $v = "prefix-$v";
     *     return "_$k";
     * };
     * assertSame(array_convert($array, $callback, true), [
     *     '_k1' => 'prefix-v1',
     *     '_k2' => [
     *         'k21' => 'v21',
     *         0     => 'v23',
     *     ],
     * ]);
     * ```
     *
     * @param array $array 対象配列
     * @param callable $callback 適用するコールバック
     * @param bool $apply_array 配列要素にもコールバックを適用するか
     * @return array 変換された配列
     */
    function array_convert($array, $callback, $apply_array = false)
    {
        $recursive = function (&$result, $array, $history, $callback) use (&$recursive, $apply_array) {
            $sequences = [];
            foreach ($array as $key => $value) {
                $is_array = is_array($value);
                $newkey = $key;
                // 配列で $apply_array あるいは非配列の場合にコールバック適用
                if (($is_array && $apply_array) || !$is_array) {
                    $newkey = $callback($key, $value, $history);
                }
                // 配列は置換
                if (is_array($newkey)) {
                    foreach ($newkey as $k => $v) {
                        $result[$k] = $v;
                    }
                    continue;
                }
                // false はスルー
                if ($newkey === false) {
                    continue;
                }
                // true は数値連番
                if ($newkey === true) {
                    if ($is_array) {
                        $sequences["_$key"] = $value;
                    }
                    else {
                        $sequences[] = $value;
                    }
                    continue;
                }
                // null は元のキー
                if ($newkey === null) {
                    $newkey = $key;
                }
                // 配列と非配列で代入の仕方が異なる
                if ($is_array) {
                    $history[] = $key;
                    $result[$newkey] = [];
                    $recursive($result[$newkey], $value, $history, $callback);
                    array_pop($history);
                }
                else {
                    $result[$newkey] = $value;
                }
            }
            // 数値連番は上書きを防ぐためにあとでやる
            foreach ($sequences as $key => $value) {
                if (is_string($key)) {
                    $history[] = substr($key, 1);
                    $v = [];
                    $result[] = &$v;
                    $recursive($v, $value, $history, $callback);
                    array_pop($history);
                    unset($v);
                }
                else {
                    $result[] = $value;
                }
            }
        };

        $result = [];
        $recursive($result, $array, [], $callback);
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\array_convert") && !defined("ryunosuke\\dbml\\array_convert")) {
    define("ryunosuke\\dbml\\array_convert", "ryunosuke\\dbml\\array_convert");
}

if (!isset($excluded_functions["array_flatten"]) && (!function_exists("ryunosuke\\dbml\\array_flatten") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_flatten"))->isInternal()))) {
    /**
     * 多階層配列をフラットに展開する
     *
     * 巷にあふれている実装と違って、 ["$pkey.$ckey" => $value] 形式の配列でも返せる。
     * $delimiter で区切り文字を指定した場合にそのようになる。
     * $delimiter = null の場合に本当の配列で返す（巷の実装と同じ）。
     *
     * Example:
     * ```php
     * $array = [
     *    'k1' => 'v1',
     *    'k2' => [
     *        'k21' => 'v21',
     *        'k22' => [
     *            'k221' => 'v221',
     *            'k222' => 'v222',
     *            'k223' => [1, 2, 3],
     *        ],
     *    ],
     * ];
     * // 区切り文字指定なし
     * assertSame(array_flatten($array), [
     *    0 => 'v1',
     *    1 => 'v21',
     *    2 => 'v221',
     *    3 => 'v222',
     *    4 => 1,
     *    5 => 2,
     *    6 => 3,
     * ]);
     * // 区切り文字指定
     * assertSame(array_flatten($array, '.'), [
     *    'k1'            => 'v1',
     *    'k2.k21'        => 'v21',
     *    'k2.k22.k221'   => 'v221',
     *    'k2.k22.k222'   => 'v222',
     *    'k2.k22.k223.0' => 1,
     *    'k2.k22.k223.1' => 2,
     *    'k2.k22.k223.2' => 3,
     * ]);
     * ```
     *
     * @param iterable $array 対象配列
     * @param string|null $delimiter キーの区切り文字。 null を与えると連番になる
     * @return array フラット化された配列
     */
    function array_flatten($array, $delimiter = null)
    {
        // 要素追加について、 array_set だと目に見えて速度低下したのでベタに if else で分岐する
        $core = function ($array, $delimiter) use (&$core) {
            $result = [];
            foreach ($array as $k => $v) {
                if (is_iterable($v)) {
                    foreach ($core($v, $delimiter) as $ik => $iv) {
                        if ($delimiter === null) {
                            $result[] = $iv;
                        }
                        else {
                            $result[$k . $delimiter . $ik] = $iv;
                        }
                    }
                }
                else {
                    if ($delimiter === null) {
                        $result[] = $v;
                    }
                    else {
                        $result[$k] = $v;
                    }
                }
            }
            return $result;
        };

        return $core($array, $delimiter);
    }
}
if (function_exists("ryunosuke\\dbml\\array_flatten") && !defined("ryunosuke\\dbml\\array_flatten")) {
    define("ryunosuke\\dbml\\array_flatten", "ryunosuke\\dbml\\array_flatten");
}

if (!isset($excluded_functions["array_nest"]) && (!function_exists("ryunosuke\\dbml\\array_nest") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\array_nest"))->isInternal()))) {
    /**
     * シンプルな [キー => 値] な配列から階層配列を生成する
     *
     * 定義的に array_flatten の逆関数のような扱いになる。
     * $delimiter で階層を表現する。
     *
     * 同名とみなされるキーは上書きされるか例外が飛ぶ。具体的には Example を参照。
     *
     * Example:
     * ```php
     * // 単純な階層展開
     * $array = [
     *    'k1'            => 'v1',
     *    'k2.k21'        => 'v21',
     *    'k2.k22.k221'   => 'v221',
     *    'k2.k22.k222'   => 'v222',
     *    'k2.k22.k223.0' => 1,
     *    'k2.k22.k223.1' => 2,
     *    'k2.k22.k223.2' => 3,
     * ];
     * assertSame(array_nest($array), [
     *    'k1' => 'v1',
     *    'k2' => [
     *        'k21' => 'v21',
     *        'k22' => [
     *            'k221' => 'v221',
     *            'k222' => 'v222',
     *            'k223' => [1, 2, 3],
     *        ],
     *    ],
     * ]);
     * // 同名になるようなキーは上書きされる
     * $array = [
     *    'k1.k2' => 'v1', // この時点で 'k1' は配列になるが・・・
     *    'k1'    => 'v2', // この時点で 'k1' は文字列として上書きされる
     * ];
     * assertSame(array_nest($array), [
     *    'k1' => 'v2',
     * ]);
     * // 上書きすら出来ない場合は例外が飛ぶ
     * $array = [
     *    'k1'    => 'v1', // この時点で 'k1' は文字列になるが・・・
     *    'k1.k2' => 'v2', // この時点で 'k1' にインデックスアクセスすることになるので例外が飛ぶ
     * ];
     * try {
     *     array_nest($array);
     * }
     * catch (\Exception $e) {
     *     assertInstanceof(\InvalidArgumentException::class, $e);
     * }
     * ```
     *
     * @param iterable $array 対象配列
     * @param string $delimiter キーの区切り文字
     * @return array 階層化された配列
     */
    function array_nest($array, $delimiter = '.')
    {
        $result = [];
        foreach ($array as $k => $v) {
            $keys = explode($delimiter, $k);
            $rkeys = [];
            $tmp = &$result;
            foreach ($keys as $key) {
                $rkeys[] = $key;
                if (isset($tmp[$key]) && !is_array($tmp[$key])) {
                    throw new \InvalidArgumentException("'" . implode($delimiter, $rkeys) . "' of '$k' is already exists.");
                }
                $tmp = &$tmp[$key];
            }
            $tmp = $v;
            unset($tmp);
        }
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\array_nest") && !defined("ryunosuke\\dbml\\array_nest")) {
    define("ryunosuke\\dbml\\array_nest", "ryunosuke\\dbml\\array_nest");
}

if (!isset($excluded_functions["stdclass"]) && (!function_exists("ryunosuke\\dbml\\stdclass") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\stdclass"))->isInternal()))) {
    /**
     * 初期フィールド値を与えて stdClass を生成する
     *
     * 手元にある配列でサクッと stdClass を作りたいことがまれによくあるはず。
     *
     * object キャストでもいいんだが、 Iterator/Traversable とかも stdClass 化したいかもしれない。
     * それにキャストだとコールバックで呼べなかったり、数値キーが死んだりして微妙に使いづらいところがある。
     *
     * Example:
     * ```php
     * // 基本的には object キャストと同じ
     * $fields = ['a' => 'A', 'b' => 'B'];
     * assertEquals(stdclass($fields), (object) $fields);
     * // ただしこういうことはキャストでは出来ない
     * assertEquals(array_map('stdclass', [$fields]), [(object) $fields]); // コールバックとして利用する
     * assertTrue(property_exists(stdclass(['a', 'b']), '0')); // 数値キー付きオブジェクトにする
     * ```
     *
     * @param iterable $fields フィールド配列
     * @return \stdClass 生成した stdClass インスタンス
     */
    function stdclass(iterable $fields = [])
    {
        $stdclass = new \stdClass();
        foreach ($fields as $key => $value) {
            $stdclass->$key = $value;
        }
        return $stdclass;
    }
}
if (function_exists("ryunosuke\\dbml\\stdclass") && !defined("ryunosuke\\dbml\\stdclass")) {
    define("ryunosuke\\dbml\\stdclass", "ryunosuke\\dbml\\stdclass");
}

if (!isset($excluded_functions["class_shorten"]) && (!function_exists("ryunosuke\\dbml\\class_shorten") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\class_shorten"))->isInternal()))) {
    /**
     * クラスの名前空間部分を除いた短い名前を取得する
     *
     * Example:
     * ```php
     * assertSame(class_shorten('vendor\\namespace\\ClassName'), 'ClassName');
     * ```
     *
     * @param string|object $class 対象クラス・オブジェクト
     * @return string クラスの短い名前
     */
    function class_shorten($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $parts = explode('\\', $class);
        return array_pop($parts);
    }
}
if (function_exists("ryunosuke\\dbml\\class_shorten") && !defined("ryunosuke\\dbml\\class_shorten")) {
    define("ryunosuke\\dbml\\class_shorten", "ryunosuke\\dbml\\class_shorten");
}

if (!isset($excluded_functions["date_timestamp"]) && (!function_exists("ryunosuke\\dbml\\date_timestamp") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\date_timestamp"))->isInternal()))) {
    /**
     * 日時文字列をよしなにタイムスタンプに変換する
     *
     * マイクロ秒にも対応している。つまり返り値は int か float になる。
     * また、相対指定の +1 month の月末問題は起きないようにしてある。
     *
     * かなり適当に和暦にも対応している。
     *
     * Example:
     * ```php
     * // 普通の日時文字列
     * assertSame(date_timestamp('2014/12/24 12:34:56'), strtotime('2014/12/24 12:34:56'));
     * // 和暦
     * assertSame(date_timestamp('昭和31年12月24日 12時34分56秒'), strtotime('1956/12/24 12:34:56'));
     * // 相対指定
     * assertSame(date_timestamp('2012/01/31 +1 month'), strtotime('2012/02/29'));
     * assertSame(date_timestamp('2012/03/31 -1 month'), strtotime('2012/02/29'));
     * // マイクロ秒
     * assertSame(date_timestamp('2014/12/24 12:34:56.789'), 1419392096.789);
     * ```
     *
     * @param string|int|float $datetimedata 日時データ
     * @return int|float|null タイムスタンプ。パース失敗時は null
     */
    function date_timestamp($datetimedata)
    {
        // 全角を含めた trim
        $chars = "[\\x0-\x20\x7f\xc2\xa0\xe3\x80\x80]";
        $datetimedata = preg_replace("/\A{$chars}++|{$chars}++\z/u", '', $datetimedata);

        // 和暦を西暦に置換
        $jpnames = array_merge(array_column(JP_ERA, 'name'), array_column(JP_ERA, 'abbr'));
        $datetimedata = preg_replace_callback('/^(' . implode('|', $jpnames) . ')(\d{1,2}|元)/u', function ($matches) {
            list(, $era, $year) = $matches;
            $eratime = array_find(JP_ERA, function ($v) use ($era) {
                if (in_array($era, [$v['name'], $v['abbr']], true)) {
                    return $v['since'];
                }
            }, false);
            return idate('Y', $eratime) + ($year === '元' ? 1 : $year) - 1;
        }, $datetimedata);

        // 単位文字列を置換
        $datetimedata = strtr($datetimedata, [
            '　'  => ' ',
            '西暦' => '',
            '年'  => '-',
            '月'  => '-',
            '日'  => ' ',
            '時'  => ':',
            '分'  => ':',
            '秒'  => '',
        ]);
        $datetimedata = trim($datetimedata, " \t\n\r\0\x0B:-");

        // 数値4桁は年と解釈されるように
        if (preg_match('/^[0-9]{4}$/', $datetimedata)) {
            $datetimedata .= '-01-01';
        }

        // 数値系はタイムスタンプとみなす
        if (ctype_digit("$datetimedata")) {
            return (int) $datetimedata;
        }
        if (is_numeric($datetimedata)) {
            return (float) $datetimedata;
        }

        // date_parse してみる
        $parts = date_parse($datetimedata);
        if (!$parts) {
            // ドキュメントに「成功した場合に日付情報を含む配列、失敗した場合に FALSE を返します」とあるが、失敗する気配がない
            return null; // @codeCoverageIgnore
        }
        if ($parts['error_count']) {
            return null;
        }

        if (!checkdate($parts['month'], $parts['day'], $parts['year'])) {
            return null;
        }

        if (isset($parts['relative'])) {
            $relative = $parts['relative'];
            $parts['year'] += $relative['year'];
            $parts['month'] += $relative['month'];
            // php の相対指定は割と腐っているので補正する（末日を超えても月は変わらないようにする）
            if ($parts['month'] > 12) {
                $parts['year'] += intdiv($parts['month'], 12);
                $parts['month'] = $parts['month'] % 12;
            }
            if ($parts['month'] < 1) {
                $parts['year'] += intdiv(-12 + $parts['month'], 12);
                $parts['month'] = 12 + $parts['month'] % 12;
            }
            if (!checkdate($parts['month'], $parts['day'], $parts['year'])) {
                $parts['day'] = idate('t', mktime(12, 12, 12, $parts['month'], 1, $parts['year']));
            }
            $parts['day'] += $relative['day'];
            $parts['hour'] += $relative['hour'];
            $parts['minute'] += $relative['minute'];
            $parts['second'] += $relative['second'];
        }

        // ドキュメントに「引数が不正な場合、 この関数は FALSE を返します」とあるが、 date_parse の結果を与える分には失敗しないはず
        $time = mktime($parts['hour'], $parts['minute'], $parts['second'], $parts['month'], $parts['day'], $parts['year']);
        if ($parts['fraction']) {
            // 1970 以前なら減算、以降なら加算じゃないと帳尻が合わなくなる
            $time += $time >= 0 ? $parts['fraction'] : -$parts['fraction'];
        }

        return $time;
    }
}
if (function_exists("ryunosuke\\dbml\\date_timestamp") && !defined("ryunosuke\\dbml\\date_timestamp")) {
    define("ryunosuke\\dbml\\date_timestamp", "ryunosuke\\dbml\\date_timestamp");
}

if (!isset($excluded_functions["date_convert"]) && (!function_exists("ryunosuke\\dbml\\date_convert") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\date_convert"))->isInternal()))) {
    /**
     * 日時文字列をよしなに別のフォーマットに変換する
     *
     * マイクロ秒にも対応している。
     * かなり適当に和暦にも対応している。
     *
     * Example:
     * ```php
     * // 和暦を Y/m/d H:i:s に変換
     * assertSame(date_convert('Y/m/d H:i:s', '昭和31年12月24日 12時34分56秒'), '1956/12/24 12:34:56');
     * // 単純に「マイクロ秒が使える date」としても使える
     * $now = 1234567890.123; // テストがしづらいので固定時刻にする
     * assertSame(date_convert('Y/m/d H:i:s.u', $now), '2009/02/14 08:31:30.123000');
     * ```
     *
     * @param string $format フォーマット
     * @param string|int|float|\DateTime $datetimedata 日時データ。省略時は microtime
     * @return string 日時文字列
     */
    function date_convert($format, $datetimedata = null)
    {
        // 省略時は microtime
        if ($datetimedata === null) {
            $timestamp = microtime(true);
        }
        elseif ($datetimedata instanceof \DateTime) {
            // @fixme DateTime オブジェクトって timestamp を float で得られないの？
            $timestamp = (float) $datetimedata->format('U.u');
        }
        else {
            $timestamp = date_timestamp($datetimedata);
            if ($timestamp === null) {
                throw new \InvalidArgumentException("parse failed '$datetimedata'");
            }
        }

        $replace = function ($string, $char, $replace) {
            $string = preg_replace('/(?<!\\\)' . $char . '/', '${1}' . $replace, $string);
            return preg_replace('/\\\\' . $char . '/', $char, $string);
        };

        if (preg_match('/[JbKk]/', $format)) {
            $era = array_find(JP_ERA, function ($v) use ($timestamp) {
                if ($v['since'] <= $timestamp) {
                    return $v;
                }
            }, false);
            if ($era === false) {
                throw new \InvalidArgumentException("notfound JP_ERA '$datetimedata'");
            }

            $y = idate('Y', $timestamp) - idate('Y', $era['since']) + 1;
            $format = $replace($format, 'J', $era['name']);
            $format = $replace($format, 'b', $era['abbr']);
            $format = $replace($format, 'K', $y === 1 ? '元' : $y);
            $format = $replace($format, 'k', $y);
        }

        $format = $replace($format, 'x', ['日', '月', '火', '水', '木', '金', '土'][idate('w', $timestamp)]);

        if (is_float($timestamp)) {
            list($second, $micro) = explode('.', $timestamp) + [1 => '000000'];
            $datetime = \DateTime::createFromFormat('Y/m/d H:i:s.u', date('Y/m/d H:i:s.', $second) . $micro);
            return $datetime->format($format);
        }
        return date($format, $timestamp);
    }
}
if (function_exists("ryunosuke\\dbml\\date_convert") && !defined("ryunosuke\\dbml\\date_convert")) {
    define("ryunosuke\\dbml\\date_convert", "ryunosuke\\dbml\\date_convert");
}

if (!isset($excluded_functions["file_set_contents"]) && (!function_exists("ryunosuke\\dbml\\file_set_contents") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\file_set_contents"))->isInternal()))) {
    /**
     * ディレクトリも掘る file_put_contents
     *
     * 書き込みは一時ファイルと rename を使用してアトミックに行われる。
     *
     * Example:
     * ```php
     * file_set_contents(sys_get_temp_dir() . '/not/filename.ext', 'hoge');
     * assertSame(file_get_contents(sys_get_temp_dir() . '/not/filename.ext'), 'hoge');
     * ```
     *
     * @param string $filename 書き込むファイル名
     * @param string $data 書き込む内容
     * @param int $umask ディレクトリを掘る際の umask
     * @return int 書き込まれたバイト数
     */
    function file_set_contents($filename, $data, $umask = 0002)
    {
        if (func_num_args() === 2) {
            $umask = umask();
        }

        $filename = path_normalize($filename);

        if (!is_dir($dirname = dirname($filename))) {
            if (!@mkdir_p($dirname, $umask)) {
                throw new \RuntimeException("failed to mkdir($dirname)");
            }
        }

        $tempnam = tempnam($dirname, 'tmp');
        if (($result = file_put_contents($tempnam, $data)) !== false) {
            if (rename($tempnam, $filename)) {
                @chmod($filename, 0666 & ~$umask);
                return $result;
            }
            unlink($tempnam);
        }
        return false;
    }
}
if (function_exists("ryunosuke\\dbml\\file_set_contents") && !defined("ryunosuke\\dbml\\file_set_contents")) {
    define("ryunosuke\\dbml\\file_set_contents", "ryunosuke\\dbml\\file_set_contents");
}

if (!isset($excluded_functions["mkdir_p"]) && (!function_exists("ryunosuke\\dbml\\mkdir_p") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\mkdir_p"))->isInternal()))) {
    /**
     * ディレクトリを再帰的に掘る
     *
     * 既に存在する場合は何もしない（エラーも出さない）。
     *
     * @param string $dirname ディレクトリ名
     * @param int $umask ディレクトリを掘る際の umask
     * @return bool 作成したら true
     */
    function mkdir_p($dirname, $umask = 0002)
    {
        if (func_num_args() === 1) {
            $umask = umask();
        }

        if (file_exists($dirname)) {
            return false;
        }

        return mkdir($dirname, 0777 & (~$umask), true);
    }
}
if (function_exists("ryunosuke\\dbml\\mkdir_p") && !defined("ryunosuke\\dbml\\mkdir_p")) {
    define("ryunosuke\\dbml\\mkdir_p", "ryunosuke\\dbml\\mkdir_p");
}

if (!isset($excluded_functions["fnmatch_or"]) && (!function_exists("ryunosuke\\dbml\\fnmatch_or") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\fnmatch_or"))->isInternal()))) {
    /**
     * fnmatch の OR 版
     *
     * $patterns のうちどれか一つでもマッチしたら true を返す。
     * $patterns が空だと例外を投げる。
     *
     * Example:
     * ```php
     * // aaa にマッチするので true
     * assertTrue(fnmatch_or(['*aaa*', '*bbb*'], 'aaaX'));
     * // どれともマッチしないので false
     * assertFalse(fnmatch_or(['*aaa*', '*bbb*'], 'cccX'));
     * ```
     *
     * @param array|string $patterns パターン配列（単一文字列可）
     * @param string $string 調べる文字列
     * @param int $flags FNM_***
     * @return bool どれかにマッチしたら true
     */
    function fnmatch_or($patterns, $string, $flags = 0)
    {
        $patterns = is_iterable($patterns) ? $patterns : [$patterns];
        if (is_empty($patterns)) {
            throw new \InvalidArgumentException('$patterns must be not empty.');
        }

        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $string, $flags)) {
                return true;
            }
        }
        return false;
    }
}
if (function_exists("ryunosuke\\dbml\\fnmatch_or") && !defined("ryunosuke\\dbml\\fnmatch_or")) {
    define("ryunosuke\\dbml\\fnmatch_or", "ryunosuke\\dbml\\fnmatch_or");
}

if (!isset($excluded_functions["path_normalize"]) && (!function_exists("ryunosuke\\dbml\\path_normalize") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\path_normalize"))->isInternal()))) {
    /**
     * パスを正規化する
     *
     * 具体的には ./ や ../ を取り除いたり連続したディレクトリ区切りをまとめたりする。
     * realpath ではない。のでシンボリックリンクの解決などはしない。その代わりファイルが存在しなくても使用することができる。
     *
     * Example:
     * ```php
     * $DS = DIRECTORY_SEPARATOR;
     * assertSame(path_normalize('/path/to/something'), "{$DS}path{$DS}to{$DS}something");
     * assertSame(path_normalize('/path/through/../something'), "{$DS}path{$DS}something");
     * assertSame(path_normalize('./path/current/./through/../something'), "path{$DS}current{$DS}something");
     * ```
     *
     * @param string $path パス文字列
     * @return string 正規化されたパス
     */
    function path_normalize($path)
    {
        $ds = '/';
        if (DIRECTORY_SEPARATOR === '\\') {
            $ds .= '\\\\';
        }

        $result = [];
        foreach (preg_split("#[$ds]#u", $path) as $n => $part) {
            if ($n > 0 && $part === '') {
                continue;
            }
            if ($part === '.') {
                continue;
            }
            if ($part === '..') {
                if (empty($result)) {
                    throw new \InvalidArgumentException("'$path' is invalid as path string.");
                }
                array_pop($result);
                continue;
            }
            $result[] = $part;
        }
        return implode(DIRECTORY_SEPARATOR, $result);
    }
}
if (function_exists("ryunosuke\\dbml\\path_normalize") && !defined("ryunosuke\\dbml\\path_normalize")) {
    define("ryunosuke\\dbml\\path_normalize", "ryunosuke\\dbml\\path_normalize");
}

if (!isset($excluded_functions["delegate"]) && (!function_exists("ryunosuke\\dbml\\delegate") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\delegate"))->isInternal()))) {
    /**
     * 指定 callable を指定クロージャで実行するクロージャを返す
     *
     * ほぼ内部向けで外から呼ぶことはあまり想定していない。
     *
     * @param \Closure $invoker クロージャを実行するためのクロージャ（実処理）
     * @param callable $callable 最終的に実行したいクロージャ
     * @param int $arity 引数の数
     * @return \Closure $callable を実行するクロージャ
     */
    function delegate($invoker, $callable, $arity = null)
    {
        // 「delegate 経由で作成されたクロージャ」であることをマーキングするための use 変数
        $__rfunc_delegate_marker = true;
        assert($__rfunc_delegate_marker === true); // phpstorm の警告解除

        if ($arity === null) {
            $arity = parameter_length($callable, true, true);
        }

        if (is_infinite($arity)) {
            return eval('return function (...$_) use ($__rfunc_delegate_marker, $invoker, $callable) {
                return $invoker($callable, func_get_args());
            };');
        }

        $arity = abs($arity);
        switch ($arity) {
            case 0:
                return function () use ($__rfunc_delegate_marker, $invoker, $callable) {
                    return $invoker($callable, func_get_args());
                };
            case 1:
                return function ($_1) use ($__rfunc_delegate_marker, $invoker, $callable) {
                    return $invoker($callable, func_get_args());
                };
            case 2:
                return function ($_1, $_2) use ($__rfunc_delegate_marker, $invoker, $callable) {
                    return $invoker($callable, func_get_args());
                };
            case 3:
                return function ($_1, $_2, $_3) use ($__rfunc_delegate_marker, $invoker, $callable) {
                    return $invoker($callable, func_get_args());
                };
            case 4:
                return function ($_1, $_2, $_3, $_4) use ($__rfunc_delegate_marker, $invoker, $callable) {
                    return $invoker($callable, func_get_args());
                };
            case 5:
                return function ($_1, $_2, $_3, $_4, $_5) use ($__rfunc_delegate_marker, $invoker, $callable) {
                    return $invoker($callable, func_get_args());
                };
            default:
                $argstring = array_map(function ($v) { return '$_' . $v; }, range(1, $arity));
                return eval('return function (' . implode(', ', $argstring) . ') use ($__rfunc_delegate_marker, $invoker, $callable) {
                    return $invoker($callable, func_get_args());
                };');
        }
    }
}
if (function_exists("ryunosuke\\dbml\\delegate") && !defined("ryunosuke\\dbml\\delegate")) {
    define("ryunosuke\\dbml\\delegate", "ryunosuke\\dbml\\delegate");
}

if (!isset($excluded_functions["nbind"]) && (!function_exists("ryunosuke\\dbml\\nbind") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\nbind"))->isInternal()))) {
    /**
     * $callable の指定位置に引数を束縛したクロージャを返す
     *
     * Example:
     * ```php
     * $bind = nbind('sprintf', 2, 'X');
     * assertSame($bind('%s%s%s', 'N', 'N'), 'NXN');
     * ```
     *
     * @param callable $callable 対象 callable
     * @param int $n 挿入する引数位置
     * @param mixed $variadic 本来の引数（可変引数）
     * @return \Closure 束縛したクロージャ
     */
    function nbind($callable, $n, ...$variadic)
    {
        return delegate(function ($callable, $args) use ($variadic, $n) {
            return $callable(...array_insert($args, $variadic, $n));
        }, $callable, parameter_length($callable, true) - count($variadic));
    }
}
if (function_exists("ryunosuke\\dbml\\nbind") && !defined("ryunosuke\\dbml\\nbind")) {
    define("ryunosuke\\dbml\\nbind", "ryunosuke\\dbml\\nbind");
}

if (!isset($excluded_functions["rbind"]) && (!function_exists("ryunosuke\\dbml\\rbind") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\rbind"))->isInternal()))) {
    /**
     * $callable の最右に引数を束縛した callable を返す
     *
     * Example:
     * ```php
     * $bind = rbind('sprintf', 'X');
     * assertSame($bind('%s%s', 'N'), 'NX');
     * ```
     *
     * @param callable $callable 対象 callable
     * @param mixed $variadic 本来の引数（可変引数）
     * @return \Closure 束縛したクロージャ
     */
    function rbind($callable, ...$variadic)
    {
        return nbind(...array_insert(func_get_args(), null, 1));
    }
}
if (function_exists("ryunosuke\\dbml\\rbind") && !defined("ryunosuke\\dbml\\rbind")) {
    define("ryunosuke\\dbml\\rbind", "ryunosuke\\dbml\\rbind");
}

if (!isset($excluded_functions["reflect_callable"]) && (!function_exists("ryunosuke\\dbml\\reflect_callable") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\reflect_callable"))->isInternal()))) {
    /**
     * callable から ReflectionFunctionAbstract を生成する
     *
     * Example:
     * ```php
     * assertInstanceof(\ReflectionFunction::class, reflect_callable('sprintf'));
     * assertInstanceof(\ReflectionMethod::class, reflect_callable('\Closure::bind'));
     * ```
     *
     * @param callable $callable 対象 callable
     * @return \ReflectionFunction|\ReflectionMethod リフレクションインスタンス
     */
    function reflect_callable($callable)
    {
        // callable チェック兼 $call_name 取得
        if (!is_callable($callable, true, $call_name)) {
            throw new \InvalidArgumentException("'$call_name' is not callable");
        }

        if ($callable instanceof \Closure || strpos($call_name, '::') === false) {
            return new \ReflectionFunction($callable);
        }
        else {
            list($class, $method) = explode('::', $call_name, 2);
            // for タイプ 5: 相対指定による静的クラスメソッドのコール (PHP 5.3.0 以降)
            if (strpos($method, 'parent::') === 0) {
                list(, $method) = explode('::', $method);
                return (new \ReflectionClass($class))->getParentClass()->getMethod($method);
            }
            return new \ReflectionMethod($class, $method);
        }
    }
}
if (function_exists("ryunosuke\\dbml\\reflect_callable") && !defined("ryunosuke\\dbml\\reflect_callable")) {
    define("ryunosuke\\dbml\\reflect_callable", "ryunosuke\\dbml\\reflect_callable");
}

if (!isset($excluded_functions["call_safely"]) && (!function_exists("ryunosuke\\dbml\\call_safely") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\call_safely"))->isInternal()))) {
    /**
     * エラーを例外に変換するブロックでコールバックを実行する
     *
     * Example:
     * ```php
     * try {
     *     call_safely(function(){return $v;});
     * }
     * catch (\Exception $ex) {
     *     assertSame($ex->getMessage(), 'Undefined variable: v');
     * }
     * ```
     *
     * @param callable $callback 実行するコールバック
     * @param mixed $variadic $callback に渡される引数（可変引数）
     * @return mixed $callback の返り値
     */
    function call_safely($callback, ...$variadic)
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            if (error_reporting() === 0) {
                return false;
            }
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        try {
            return $callback(...$variadic);
        }
        finally {
            restore_error_handler();
        }
    }
}
if (function_exists("ryunosuke\\dbml\\call_safely") && !defined("ryunosuke\\dbml\\call_safely")) {
    define("ryunosuke\\dbml\\call_safely", "ryunosuke\\dbml\\call_safely");
}

if (!isset($excluded_functions["parameter_length"]) && (!function_exists("ryunosuke\\dbml\\parameter_length") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\parameter_length"))->isInternal()))) {
    /**
     * callable の引数の数を返す
     *
     * クロージャはキャッシュされない。毎回リフレクションを生成し、引数の数を調べてそれを返す。
     * （クロージャには一意性がないので key-value なキャッシュが適用できない）。
     * ので、ループ内で使ったりすると目に見えてパフォーマンスが低下するので注意。
     *
     * Example:
     * ```php
     * // trim の引数は2つ
     * assertSame(parameter_length('trim'), 2);
     * // trim の必須引数は1つ
     * assertSame(parameter_length('trim', true), 1);
     * ```
     *
     * @param callable $callable 対象 callable
     * @param bool $require_only true を渡すと必須パラメータの数を返す
     * @param bool $thought_variadic 可変引数を考慮するか。 true を渡すと可変引数の場合に無限長を返す
     * @return int 引数の数
     */
    function parameter_length($callable, $require_only = false, $thought_variadic = false)
    {
        // クロージャの $call_name には一意性がないのでキャッシュできない（spl_object_hash でもいいが、かなり重複するので完全ではない）
        if ($callable instanceof \Closure) {
            /** @var \ReflectionFunctionAbstract $ref */
            $ref = reflect_callable($callable);
            if ($thought_variadic && $ref->isVariadic()) {
                return INF;
            }
            elseif ($require_only) {
                return $ref->getNumberOfRequiredParameters();
            }
            else {
                return $ref->getNumberOfParameters();
            }
        }

        // $call_name 取得
        is_callable($callable, false, $call_name);

        $cache = cache($call_name, function () use ($callable) {
            /** @var \ReflectionFunctionAbstract $ref */
            $ref = reflect_callable($callable);
            return [
                '00' => $ref->getNumberOfParameters(),
                '01' => $ref->isVariadic() ? INF : $ref->getNumberOfParameters(),
                '10' => $ref->getNumberOfRequiredParameters(),
                '11' => $ref->isVariadic() ? INF : $ref->getNumberOfRequiredParameters(),
            ];
        }, __FUNCTION__);
        return $cache[(int) $require_only . (int) $thought_variadic];
    }
}
if (function_exists("ryunosuke\\dbml\\parameter_length") && !defined("ryunosuke\\dbml\\parameter_length")) {
    define("ryunosuke\\dbml\\parameter_length", "ryunosuke\\dbml\\parameter_length");
}

if (!isset($excluded_functions["func_user_func_array"]) && (!function_exists("ryunosuke\\dbml\\func_user_func_array") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\func_user_func_array"))->isInternal()))) {
    /**
     * パラメータ定義数に応じて呼び出し引数を可変にしてコールする
     *
     * デフォルト引数はカウントされない。必須パラメータの数で呼び出す。
     * もちろん可変引数は未対応。
     *
     * $callback に null を与えると例外的に「第1引数を返すクロージャ」を返す。
     *
     * php の標準関数は定義数より多い引数を投げるとエラーを出すのでそれを抑制したい場合に使う。
     *
     * Example:
     * ```php
     * // strlen に2つの引数を渡してもエラーにならない
     * $strlen = func_user_func_array('strlen');
     * assertSame($strlen('abc', null), 3);
     * ```
     *
     * @param callable $callback 呼び出すクロージャ
     * @return \Closure 引数ぴったりで呼び出すクロージャ
     */
    function func_user_func_array($callback)
    {
        // null は第1引数を返す特殊仕様
        if ($callback === null) {
            return function ($v) { return $v; };
        }
        // クロージャはユーザ定義しかありえないので調べる必要がない
        if ($callback instanceof \Closure) {
            // が、組み込みをバイパスする delegate はクロージャなのでそれだけは除外
            $uses = reflect_callable($callback)->getStaticVariables();
            if (!isset($uses['__rfunc_delegate_marker'])) {
                return $callback;
            }
        }

        // 上記以外は「引数ぴったりで削ぎ落としてコールするクロージャ」を返す
        $plength = parameter_length($callback, true, true);
        return delegate(function ($callback, $args) use ($plength) {
            if (is_infinite($plength)) {
                return $callback(...$args);
            }
            return $callback(...array_slice($args, 0, $plength));
        }, $callback, $plength);
    }
}
if (function_exists("ryunosuke\\dbml\\func_user_func_array") && !defined("ryunosuke\\dbml\\func_user_func_array")) {
    define("ryunosuke\\dbml\\func_user_func_array", "ryunosuke\\dbml\\func_user_func_array");
}

if (!isset($excluded_functions["func_method"]) && (!function_exists("ryunosuke\\dbml\\func_method") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\func_method"))->isInternal()))) {
    /**
     * 指定メソッドを呼び出すクロージャを返す
     *
     * この関数を呼ぶとメソッドのクロージャを返す。
     * そのクロージャにオブジェクトを与えて呼び出すとそれはメソッド呼び出しとなる。
     *
     * オプションでデフォルト引数を設定できる（Example を参照）。
     *
     * Example:
     * ```php
     * // 与えられた引数を結合して返すメソッド hoge を持つクラス
     * $object = new class()
     * {
     *     function hoge(...$args) { return implode(',', $args); }
     * };
     * // hoge を呼び出すクロージャ
     * $hoge = func_method('hoge');
     * // ↑を使用して $object の hoge を呼び出す
     * assertSame($hoge($object, 1, 2, 3), '1,2,3');
     *
     * // デフォルト値付きで hoge を呼び出すクロージャ
     * $hoge789 = func_method('hoge', 7, 8, 9);
     * // ↑を使用して $object の hoge を呼び出す（引数指定してるので結果は同じ）
     * assertSame($hoge789($object, 1, 2, 3), '1,2,3');
     * // 同上（一部デフォルト値）
     * assertSame($hoge789($object, 1, 2), '1,2,9');
     * // 同上（全部デフォルト値）
     * assertSame($hoge789($object), '7,8,9');
     * ```
     *
     * @param string $methodname メソッド名
     * @param array $defaultargs メソッドのデフォルト引数
     * @return \Closure メソッドを呼び出すクロージャ
     */
    function func_method($methodname, ...$defaultargs)
    {
        if ($methodname === '__construct') {
            return function ($object, ...$args) use ($defaultargs) {
                return new $object(...$args + $defaultargs);
            };
        }
        return function ($object, ...$args) use ($methodname, $defaultargs) {
            return ([$object, $methodname])(...$args + $defaultargs);
        };
    }
}
if (function_exists("ryunosuke\\dbml\\func_method") && !defined("ryunosuke\\dbml\\func_method")) {
    define("ryunosuke\\dbml\\func_method", "ryunosuke\\dbml\\func_method");
}

if (!isset($excluded_functions["strcat"]) && (!function_exists("ryunosuke\\dbml\\strcat") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\strcat"))->isInternal()))) {
    /**
     * 文字列結合の関数版
     *
     * Example:
     * ```php
     * assertSame(strcat('a', 'b', 'c'), 'abc');
     * ```
     *
     * @param mixed $variadic 結合する文字列（可変引数）
     * @return string 結合した文字列
     */
    function strcat(...$variadic)
    {
        return implode('', $variadic);
    }
}
if (function_exists("ryunosuke\\dbml\\strcat") && !defined("ryunosuke\\dbml\\strcat")) {
    define("ryunosuke\\dbml\\strcat", "ryunosuke\\dbml\\strcat");
}

if (!isset($excluded_functions["concat"]) && (!function_exists("ryunosuke\\dbml\\concat") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\concat"))->isInternal()))) {
    /**
     * strcat の空文字回避版
     *
     * 基本は strcat と同じ。ただし、**引数の内1つでも空文字を含むなら空文字を返す**。
     *
     * 「プレフィックスやサフィックスを付けたいんだけど、空文字の場合はそのままで居て欲しい」という状況はまれによくあるはず。
     * コードで言えば `strlen($string) ? 'prefix-' . $string : '';` のようなもの。
     * 可変引数なので 端的に言えば mysql の CONCAT みたいな動作になる（あっちは NULL だが）。
     *
     * ```php
     * assertSame(concat('prefix-', 'middle', '-suffix'), 'prefix-middle-suffix');
     * assertSame(concat('prefix-', '', '-suffix'), '');
     * ```
     *
     * @param mixed $variadic 結合する文字列（可変引数）
     * @return string 結合した文字列
     */
    function concat(...$variadic)
    {
        $result = '';
        foreach ($variadic as $s) {
            if (strlen($s) === 0) {
                return '';
            }
            $result .= $s;
        }
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\concat") && !defined("ryunosuke\\dbml\\concat")) {
    define("ryunosuke\\dbml\\concat", "ryunosuke\\dbml\\concat");
}

if (!isset($excluded_functions["split_noempty"]) && (!function_exists("ryunosuke\\dbml\\split_noempty") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\split_noempty"))->isInternal()))) {
    /**
     * 空文字を除外する文字列分割
     *
     * - 空文字を任意の区切り文字で分割しても常に空配列
     * - キーは連番で返す（歯抜けがないただの配列）
     *
     * $triming を指定した場合、結果配列にも影響する。
     * つまり「除外は trim したいが結果配列にはしたくない」はできない。
     *
     * Example:
     * ```php
     * assertSame(split_noempty(',', 'a, b, c'), ['a', 'b', 'c']);
     * assertSame(split_noempty(',', 'a, , , b, c'), ['a', 'b', 'c']);
     * assertSame(split_noempty(',', 'a, , , b, c', false), ['a', ' ', ' ', ' b', ' c']);
     * ```
     *
     * @param string $delimiter 区切り文字
     * @param string $string 対象文字
     * @param string|bool $trimchars 指定した文字を trim する。true を指定すると trim する
     * @return array 指定文字で分割して空文字を除いた配列
     */
    function split_noempty($delimiter, $string, $trimchars = true)
    {
        // trim しないなら preg_split(PREG_SPLIT_NO_EMPTY) で十分
        if (strlen($trimchars) === 0) {
            return preg_split('#' . preg_quote($delimiter, '#') . '#u', $string, -1, PREG_SPLIT_NO_EMPTY);
        }

        // trim するなら preg_split だと無駄にややこしくなるのでベタにやる
        $trim = ($trimchars === true) ? 'trim' : rbind('trim', $trimchars);
        $parts = explode($delimiter, $string);
        $parts = array_map($trim, $parts);
        $parts = array_filter($parts, 'strlen');
        $parts = array_values($parts);
        return $parts;
    }
}
if (function_exists("ryunosuke\\dbml\\split_noempty") && !defined("ryunosuke\\dbml\\split_noempty")) {
    define("ryunosuke\\dbml\\split_noempty", "ryunosuke\\dbml\\split_noempty");
}

if (!isset($excluded_functions["quoteexplode"]) && (!function_exists("ryunosuke\\dbml\\quoteexplode") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\quoteexplode"))->isInternal()))) {
    /**
     * エスケープやクオートに対応した explode
     *
     * $enclosures は配列で開始・終了文字が別々に指定できるが、実装上の都合で今のところ1文字ずつのみ。
     *
     * Example:
     * ```php
     * // シンプルな例
     * assertSame(quoteexplode(',', 'a,b,c\\,d,"e,f"'), [
     *     'a', // 普通に分割される
     *     'b', // 普通に分割される
     *     'c\\,d', // \\ でエスケープしているので区切り文字とみなされない
     *     '"e,f"', // "" でクオートされているので区切り文字とみなされない
     * ]);
     *
     * // $enclosures で囲い文字の開始・終了文字を明示できる
     * assertSame(quoteexplode(',', 'a,b,{e,f}', ['{' => '}']), [
     *     'a', // 普通に分割される
     *     'b', // 普通に分割される
     *     '{e,f}', // { } で囲まれているので区切り文字とみなされない
     * ]);
     * ```
     *
     * @param string|array $delimiter 分割文字列
     * @param string $string 対象文字列
     * @param array|string $enclosures 囲い文字。 ["start" => "end"] で開始・終了が指定できる
     * @param string $escape エスケープ文字
     * @return array 分割された配列
     */
    function quoteexplode($delimiter, $string, $enclosures = "'\"", $escape = '\\')
    {
        if (is_string($enclosures)) {
            $chars = str_split($enclosures);
            $enclosures = array_combine($chars, $chars);
        }

        $delimiters = arrayize($delimiter);
        $starts = implode('', array_keys($enclosures));
        $ends = implode('', $enclosures);
        $enclosing = [];
        $current = 0;
        $result = [];
        for ($i = 0, $l = strlen($string); $i < $l; $i++) {
            if ($i !== 0 && $string[$i - 1] === $escape) {
                continue;
            }
            if (strpos($ends, $string[$i]) !== false) {
                if ($enclosing && $enclosures[$enclosing[count($enclosing) - 1]] === $string[$i]) {
                    array_pop($enclosing);
                    continue;
                }
            }
            if (strpos($starts, $string[$i]) !== false) {
                $enclosing[] = $string[$i];
                continue;
            }
            if (empty($enclosing)) {
                foreach ($delimiters as $delimiter) {
                    $delimiterlen = strlen($delimiter);
                    if (substr_compare($string, $delimiter, $i, $delimiterlen) === 0) {
                        $result[] = substr($string, $current, $i - $current);
                        $current = $i + $delimiterlen;
                        break;
                    }
                }
            }
        }
        $result[] = substr($string, $current, $i);
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\quoteexplode") && !defined("ryunosuke\\dbml\\quoteexplode")) {
    define("ryunosuke\\dbml\\quoteexplode", "ryunosuke\\dbml\\quoteexplode");
}

if (!isset($excluded_functions["str_contains"]) && (!function_exists("ryunosuke\\dbml\\str_contains") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\str_contains"))->isInternal()))) {
    /**
     * 指定文字列を含むか返す
     *
     * Example:
     * ```php
     * assertTrue(str_contains('abc', 'b'));
     * assertTrue(str_contains('abc', 'B', true));
     * assertTrue(str_contains('abc', ['b', 'x'], false, false));
     * assertFalse(str_contains('abc', ['b', 'x'], false, true));
     * ```
     *
     * @param string $haystack 対象文字列
     * @param string|array $needle 調べる文字列
     * @param bool $case_insensitivity 大文字小文字を無視するか
     * @param bool $and_flag すべて含む場合に true を返すか
     * @return bool $needle を含むなら true
     */
    function str_contains($haystack, $needle, $case_insensitivity = false, $and_flag = false)
    {
        if (!is_array($needle)) {
            $needle = [$needle];
        }

        // あくまで文字列としての判定に徹する（strpos の第2引数は闇が深い気がする）
        $haystack = (string) $haystack;
        $needle = array_map('strval', $needle);

        foreach ($needle as $str) {
            if ($str === '') {
                continue;
            }
            $pos = $case_insensitivity ? stripos($haystack, $str) : strpos($haystack, $str);
            if ($and_flag && $pos === false) {
                return false;
            }
            if (!$and_flag && $pos !== false) {
                return true;
            }
        }
        return !!$and_flag;
    }
}
if (function_exists("ryunosuke\\dbml\\str_contains") && !defined("ryunosuke\\dbml\\str_contains")) {
    define("ryunosuke\\dbml\\str_contains", "ryunosuke\\dbml\\str_contains");
}

if (!isset($excluded_functions["str_chop"]) && (!function_exists("ryunosuke\\dbml\\str_chop") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\str_chop"))->isInternal()))) {
    /**
     * 先頭・末尾の指定文字列を削ぎ落とす
     *
     * Example:
     * ```php
     * // 文字列からパス文字列と拡張子を削ぎ落とす
     * $PATH = '/path/to/something';
     * assertSame(str_chop("$PATH/hoge.php", "$PATH/", '.php'), 'hoge');
     * ```
     *
     * @param string $string 対象文字列
     * @param string $prefix 削ぎ落とす先頭文字列
     * @param string $suffix 削ぎ落とす末尾文字列
     * @param bool $case_insensitivity 大文字小文字を無視するか
     * @return string 削ぎ落とした文字列
     */
    function str_chop($string, $prefix = null, $suffix = null, $case_insensitivity = false)
    {
        $pattern = [];
        if (strlen($prefix)) {
            $pattern[] = '(\A' . preg_quote($prefix, '#') . ')';
        }
        if (strlen($suffix)) {
            $pattern[] = '(' . preg_quote($suffix, '#') . '\z)';
        }
        $flag = 'u' . ($case_insensitivity ? 'i' : '');
        return preg_replace('#' . implode('|', $pattern) . '#' . $flag, '', $string);
    }
}
if (function_exists("ryunosuke\\dbml\\str_chop") && !defined("ryunosuke\\dbml\\str_chop")) {
    define("ryunosuke\\dbml\\str_chop", "ryunosuke\\dbml\\str_chop");
}

if (!isset($excluded_functions["str_lchop"]) && (!function_exists("ryunosuke\\dbml\\str_lchop") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\str_lchop"))->isInternal()))) {
    /**
     * 先頭の指定文字列を削ぎ落とす
     *
     * Example:
     * ```php
     * // 文字列からパス文字列を削ぎ落とす
     * $PATH = '/path/to/something';
     * assertSame(str_lchop("$PATH/hoge.php", "$PATH/"), 'hoge.php');
     * ```
     *
     * @param string $string 対象文字列
     * @param string $prefix 削ぎ落とす先頭文字列
     * @param bool $case_insensitivity 大文字小文字を無視するか
     * @return string 削ぎ落とした文字列
     */
    function str_lchop($string, $prefix, $case_insensitivity = false)
    {
        return str_chop($string, $prefix, null, $case_insensitivity);
    }
}
if (function_exists("ryunosuke\\dbml\\str_lchop") && !defined("ryunosuke\\dbml\\str_lchop")) {
    define("ryunosuke\\dbml\\str_lchop", "ryunosuke\\dbml\\str_lchop");
}

if (!isset($excluded_functions["str_subreplace"]) && (!function_exists("ryunosuke\\dbml\\str_subreplace") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\str_subreplace"))->isInternal()))) {
    /**
     * 指定文字列を置換する
     *
     * $subject 内の $search を $replaces に置換する。
     * str_replace とは「N 番目のみ置換できる」点で異なる。
     * つまり、$search='hoge', $replace=[2 => 'fuga'] とすると「2 番目の 'hoge' が 'fuga' に置換される」という動作になる（0 ベース）。
     *
     * $replace に 非配列を与えた場合は配列化される。
     * つまり `$replaces = 'hoge'` は `$replaces = [0 => 'hoge']` と同じ（最初のマッチを置換する）。
     *
     * $replace に空配列を与えると何もしない。
     * 負数キーは後ろから数える動作となる。
     * また、置換後の文字列は置換対象にはならない。
     *
     * N 番目の検索文字列が見つからない場合は例外を投げる。
     * ただし、文字自体が見つからない場合は投げない。
     *
     * Example:
     * ```php
     * // 1番目（0ベースなので2番目）の x を X に置換
     * assertSame(str_subreplace('xxx', 'x', [1 => 'X']), 'xXx');
     * // 0番目（最前列）の x を Xa に、-1番目（最後尾）の x を Xz に置換
     * assertSame(str_subreplace('!xxx!', 'x', [0 => 'Xa', -1 => 'Xz']), '!XaxXz!');
     * // 置換結果は置換対象にならない
     * assertSame(str_subreplace('xxx', 'x', [0 => 'xxx', 1 => 'X']), 'xxxXx');
     * ```
     *
     * @param string $subject 対象文字列
     * @param string $search 検索文字列
     * @param array|string $replaces 置換文字列配列（単一指定は配列化される）
     * @param bool $case_insensitivity 大文字小文字を無視するか
     * @return string 置換された文字列
     */
    function str_subreplace($subject, $search, $replaces, $case_insensitivity = false)
    {
        $replaces = is_iterable($replaces) ? $replaces : [$replaces];

        // 空はそのまま返す
        if (is_empty($replaces)) {
            return $subject;
        }

        // 負数対応のために逆数計算（ついでに整数チェック）
        $subcount = $case_insensitivity ? substr_count(strtolower($subject), strtolower($search)) : substr_count($subject, $search);
        if ($subcount === 0) {
            return $subject;
        }
        $mapping = [];
        foreach ($replaces as $n => $replace) {
            $origN = $n;
            if (!is_int($n)) {
                throw new \InvalidArgumentException('$replaces key must be integer.');
            }
            if ($n < 0) {
                $n += $subcount;
            }
            if (!(0 <= $n && $n < $subcount)) {
                throw new \InvalidArgumentException("notfound search string '$search' of {$origN}th.");
            }
            $mapping[$n] = $replace;
        }
        $maxseq = max(array_keys($mapping));
        $offset = 0;
        for ($n = 0; $n <= $maxseq; $n++) {
            $pos = $case_insensitivity ? stripos($subject, $search, $offset) : strpos($subject, $search, $offset);
            if (isset($mapping[$n])) {
                $subject = substr_replace($subject, $mapping[$n], $pos, strlen($search));
                $offset = $pos + strlen($mapping[$n]);
            }
            else {
                $offset = $pos + strlen($search);
            }
        }
        return $subject;
    }
}
if (function_exists("ryunosuke\\dbml\\str_subreplace") && !defined("ryunosuke\\dbml\\str_subreplace")) {
    define("ryunosuke\\dbml\\str_subreplace", "ryunosuke\\dbml\\str_subreplace");
}

if (!isset($excluded_functions["str_between"]) && (!function_exists("ryunosuke\\dbml\\str_between") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\str_between"))->isInternal()))) {
    /**
     * 指定文字で囲まれた文字列を取得する
     *
     * $from, $to で指定した文字間を取得する（$from, $to 自体は結果に含まれない）。
     * ネストしている場合、一番外側の文字間を返す。
     *
     * $enclosure で「特定文字に囲まれている」場合を無視することができる。
     * $escape で「特定文字が前にある」場合を無視することができる。
     *
     * $position を与えた場合、その場所から走査を開始する。
     * さらに結果があった場合、 $position には「次の走査開始位置」が代入される。
     * これを使用すると連続で「次の文字, 次の文字, ...」と言った動作が可能になる。
     *
     * Example:
     * ```php
     * // $position を利用して "first", "second", "third" を得る（"で囲まれた "blank" は返ってこない）。
     * assertSame(str_between('{first} and {second} and "{blank}" and {third}', '{', '}', $n), 'first');
     * assertSame(str_between('{first} and {second} and "{blank}" and {third}', '{', '}', $n), 'second');
     * assertSame(str_between('{first} and {second} and "{blank}" and {third}', '{', '}', $n), 'third');
     * // ネストしている場合は最も外側を返す
     * assertSame(str_between('{nest1{nest2{nest3}}}', '{', '}'), 'nest1{nest2{nest3}}');
     * ```
     *
     * @param string $string 対象文字列
     * @param string $from 開始文字列
     * @param string $to 終了文字列
     * @param int $position 開始位置。渡した場合次の開始位置が設定される
     * @param string $enclosure 囲い文字。この文字中にいる $from, $to 文字は走査外になる
     * @param string $escape エスケープ文字。この文字が前にある $from, $to 文字は走査外になる
     * @return string|bool $from, $to で囲まれた文字。見つからなかった場合は false
     */
    function str_between($string, $from, $to, &$position = 0, $enclosure = '\'"', $escape = '\\')
    {
        $strlen = strlen($string);
        $fromlen = strlen($from);
        $tolen = strlen($to);
        $position = intval($position);
        $enclosing = null;
        $nesting = 0;
        $start = null;
        for ($i = $position; $i < $strlen; $i++) {
            if ($i !== 0 && $string[$i - 1] === $escape) {
                continue;
            }
            if (strpos($enclosure, $string[$i]) !== false) {
                if ($enclosing === null) {
                    $enclosing = $string[$i];
                }
                elseif ($enclosing === $string[$i]) {
                    $enclosing = null;
                }
                continue;
            }

            // 開始文字と終了文字が重複している可能性があるので $to からチェックする
            if ($enclosing === null && substr_compare($string, $to, $i, $tolen) === 0) {
                if (--$nesting === 0) {
                    $position = $i + $tolen;
                    return substr($string, $start, $i - $start);
                }
                // いきなり終了文字が来た場合は無視する
                if ($nesting < 0) {
                    $nesting = 0;
                }
            }
            if ($enclosing === null && substr_compare($string, $from, $i, $fromlen) === 0) {
                if ($nesting++ === 0) {
                    $start = $i + $fromlen;
                }
            }
        }
        return false;
    }
}
if (function_exists("ryunosuke\\dbml\\str_between") && !defined("ryunosuke\\dbml\\str_between")) {
    define("ryunosuke\\dbml\\str_between", "ryunosuke\\dbml\\str_between");
}

if (!isset($excluded_functions["preg_splice"]) && (!function_exists("ryunosuke\\dbml\\preg_splice") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\preg_splice"))->isInternal()))) {
    /**
     * キャプチャも行える preg_replace
     *
     * 「置換を行いつつ、キャプチャ文字列が欲しい」状況はまれによくあるはず。
     *
     * $replacement に callable を渡すと preg_replace_callback がコールされる。
     * callable とはいっても単純文字列 callble （"strtoupper" など）は callable とはみなされない。
     * 配列形式の callable や クロージャのみ preg_replace_callback になる。
     *
     * Example:
     * ```php
     * // 数字を除去しつつその除去された数字を得る
     * assertSame(preg_splice('#\\d+#', '', 'abc123', $m), 'abc');
     * assertSame($m, ['123']);
     *
     * // callable だと preg_replace_callback が呼ばれる
     * assertSame(preg_splice('#[a-z]+#', function($m){return strtoupper($m[0]);}, 'abc123', $m), 'ABC123');
     * assertSame($m, ['abc']);
     *
     * // ただし、 文字列 callable は文字列として扱う
     * assertSame(preg_splice('#[a-z]+#', 'strtoupper', 'abc123', $m), 'strtoupper123');
     * assertSame($m, ['abc']);
     * ```
     *
     * @param string $pattern 正規表現
     * @param string|callable $replacement 置換文字列
     * @param string $subject 対象文字列
     * @param array $matches キャプチャ配列が格納される
     * @return string 置換された文字列
     */
    function preg_splice($pattern, $replacement, $subject, &$matches = [])
    {
        if (preg_match($pattern, $subject, $matches)) {
            if (!is_string($replacement) && is_callable($replacement)) {
                $subject = preg_replace_callback($pattern, $replacement, $subject);
            }
            else {
                $subject = preg_replace($pattern, $replacement, $subject);
            }
        }
        return $subject;
    }
}
if (function_exists("ryunosuke\\dbml\\preg_splice") && !defined("ryunosuke\\dbml\\preg_splice")) {
    define("ryunosuke\\dbml\\preg_splice", "ryunosuke\\dbml\\preg_splice");
}

if (!isset($excluded_functions["optional"]) && (!function_exists("ryunosuke\\dbml\\optional") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\optional"))->isInternal()))) {
    /**
     * オブジェクトならそれを、オブジェクトでないなら NullObject を返す
     *
     * null を返すかもしれないステートメントを一時変数を介さずワンステートメントで呼ぶことが可能になる。
     *
     * NullObject は 基本的に null を返すが、return type が規約されている場合は null 以外を返すこともある。
     * 取得系呼び出しを想定しているので、設定系呼び出しは行うべきではない。
     * __set のような明らかに設定が意図されているものは例外が飛ぶ。
     *
     * Example:
     * ```php
     * // null を返すかもしれないステートメント
     * $getobject = function () {return null;};
     * // メソッド呼び出しは null を返す
     * assertSame(optional($getobject())->method(), null);
     * // プロパティアクセスは null を返す
     * assertSame(optional($getobject())->property, null);
     * // empty は true を返す
     * assertSame(empty(optional($getobject())->nothing), true);
     * // __isset は false を返す
     * assertSame(isset(optional($getobject())->nothing), false);
     * // __toString は '' を返す
     * assertSame(strval(optional($getobject())), '');
     * // __invoke は null を返す
     * assertSame(call_user_func(optional($getobject())), null);
     * // 配列アクセスは null を返す
     * assertSame($getobject()['hoge'], null);
     * // 空イテレータを返す
     * assertSame(iterator_to_array(optional($getobject())), []);
     *
     * // $expected を与えるとその型以外は NullObject を返す（\ArrayObject はオブジェクトだが stdClass ではない）
     * assertSame(optional(new \ArrayObject([1]), 'stdClass')->count(), null);
     * ```
     *
     * @param object|null $object オブジェクト
     * @param string $expected 期待するクラス名。指定した場合は is_a される
     * @return mixed $object がオブジェクトならそのまま返し、違うなら NullObject を返す
     */
    function optional($object, $expected = null)
    {
        if (is_object($object)) {
            if ($expected === null || is_a($object, $expected)) {
                return $object;
            }
        }

        static $nullobject = null;
        if ($nullobject === null) {
            $nullobject = new class implements \ArrayAccess, \IteratorAggregate
            {
                // @formatter:off
                public function __isset($name) { return false; }
                public function __get($name) { return null; }
                public function __set($name, $value) { throw new \DomainException('called NullObject#' . __FUNCTION__); }
                public function __unset($name) { throw new \DomainException('called NullObject#' . __FUNCTION__); }
                public function __call($name, $arguments) { return null; }
                public function __invoke() { return null; }
                public function __toString() { return ''; }
                public function offsetExists($offset) { return false; }
                public function offsetGet($offset) { return null; }
                public function offsetSet($offset, $value) { throw new \DomainException('called NullObject#' . __FUNCTION__); }
                public function offsetUnset($offset) { throw new \DomainException('called NullObject#' . __FUNCTION__); }
                public function getIterator() { return new \ArrayIterator([]); }
                // @formatter:on
            };
        }
        return $nullobject;
    }
}
if (function_exists("ryunosuke\\dbml\\optional") && !defined("ryunosuke\\dbml\\optional")) {
    define("ryunosuke\\dbml\\optional", "ryunosuke\\dbml\\optional");
}

if (!isset($excluded_functions["throws"]) && (!function_exists("ryunosuke\\dbml\\throws") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\throws"))->isInternal()))) {
    /**
     * throw の関数版
     *
     * hoge() or throw などしたいことがまれによくあるはず。
     *
     * Example:
     * ```php
     * try {
     *     throws(new \Exception('throws'));
     * }
     * catch (\Exception $ex) {
     *     assertSame($ex->getMessage(), 'throws');
     * }
     * ```
     *
     * @param \Exception $ex 投げる例外
     * @return mixed （`return hoge or throws` のようなコードで警告が出るので抑止用）
     */
    function throws($ex)
    {
        throw $ex;
    }
}
if (function_exists("ryunosuke\\dbml\\throws") && !defined("ryunosuke\\dbml\\throws")) {
    define("ryunosuke\\dbml\\throws", "ryunosuke\\dbml\\throws");
}

if (!isset($excluded_functions["try_finally"]) && (!function_exists("ryunosuke\\dbml\\try_finally") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\try_finally"))->isInternal()))) {
    /**
     * try ～ finally 構文の関数版
     *
     * 例外は投げっぱなす。例外機構構文が冗長なことがまれによくあるはず。
     *
     * Example:
     * ```php
     * $finally_count = 0;
     * $finally = function()use(&$finally_count){$finally_count++;};
     * // 例外が飛ぼうと飛ぶまいと $finally は実行される
     * $try = function($a, $b, $c){return [$a, $b, $c];};
     * assertSame(try_finally($try, $finally, 1, 2, 3), [1, 2, 3]);
     * assertSame($finally_count, 1); // 呼ばれている
     * // 例外は投げっぱなすが、 $finally は実行される
     * $try = function(){throw new \Exception('tried');};
     * try {try_finally($try, $finally, 1, 2, 3);} catch(\Exception $e){};
     * assertSame($finally_count, 2); // 呼ばれている
     * ```
     *
     * @param callable $try try ブロッククロージャ
     * @param callable $finally finally ブロッククロージャ
     * @param array $variadic $try に渡る引数
     * @return \Exception|mixed 例外が飛ばなかったら $try ブロックの返り値、飛んだなら $catch の返り値（デフォルトで例外オブジェクト）
     */
    function try_finally($try, $finally = null, ...$variadic)
    {
        return try_catch_finally($try, throws, $finally, ...$variadic);
    }
}
if (function_exists("ryunosuke\\dbml\\try_finally") && !defined("ryunosuke\\dbml\\try_finally")) {
    define("ryunosuke\\dbml\\try_finally", "ryunosuke\\dbml\\try_finally");
}

if (!isset($excluded_functions["try_catch_finally"]) && (!function_exists("ryunosuke\\dbml\\try_catch_finally") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\try_catch_finally"))->isInternal()))) {
    /**
     * try ～ catch ～ finally 構文の関数版
     *
     * 例外機構構文が冗長なことがまれによくあるはず。
     *
     * Example:
     * ```php
     * $finally_count = 0;
     * $finally = function()use(&$finally_count){$finally_count++;};
     * // 例外が飛ぼうと飛ぶまいと $finally は実行される
     * $try = function($a, $b, $c){return [$a, $b, $c];};
     * assertSame(try_catch_finally($try, null, $finally, 1, 2, 3), [1, 2, 3]);
     * assertSame($finally_count, 1); // 呼ばれている
     * // 例外を投げるが、 $catch で握りつぶす
     * $try = function(){throw new \Exception('tried');};
     * assertSame(try_catch_finally($try, null, $finally, 1, 2, 3)->getMessage(), 'tried');
     * assertSame($finally_count, 2); // 呼ばれている
     * ```
     *
     * @param callable $try try ブロッククロージャ
     * @param callable $catch catch ブロッククロージャ
     * @param callable $finally finally ブロッククロージャ
     * @param array $variadic $try に渡る引数
     * @return \Exception|mixed 例外が飛ばなかったら $try ブロックの返り値、飛んだなら $catch の返り値（デフォルトで例外オブジェクト）
     */
    function try_catch_finally($try, $catch = null, $finally = null, ...$variadic)
    {
        if ($catch === null) {
            $catch = function ($v) { return $v; };
        }

        try {
            return $try(...$variadic);
        }
        catch (\Exception $tried_ex) {
            try {
                return $catch($tried_ex);
            }
            catch (\Exception $catched_ex) {
                throw $catched_ex;
            }
        }
        finally {
            if ($finally !== null) {
                $finally();
            }
        }
    }
}
if (function_exists("ryunosuke\\dbml\\try_catch_finally") && !defined("ryunosuke\\dbml\\try_catch_finally")) {
    define("ryunosuke\\dbml\\try_catch_finally", "ryunosuke\\dbml\\try_catch_finally");
}

if (!isset($excluded_functions["cachedir"]) && (!function_exists("ryunosuke\\dbml\\cachedir") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\cachedir"))->isInternal()))) {
    /**
     * 本ライブラリで使用するキャッシュディレクトリを設定する
     *
     * @param string|null $dirname キャッシュディレクトリ。省略時は返すのみ
     * @return string 設定前のキャッシュディレクトリ
     */
    function cachedir($dirname = null)
    {
        static $cachedir;
        if ($dirname === null) {
            return $cachedir = $cachedir ?? sys_get_temp_dir();
        }

        if (!file_exists($dirname)) {
            @mkdir($dirname, 0777 & (~umask()), true);
        }
        $result = $cachedir;
        $cachedir = realpath($dirname);
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\cachedir") && !defined("ryunosuke\\dbml\\cachedir")) {
    define("ryunosuke\\dbml\\cachedir", "ryunosuke\\dbml\\cachedir");
}

if (!isset($excluded_functions["cache"]) && (!function_exists("ryunosuke\\dbml\\cache") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\cache"))->isInternal()))) {
    /**
     * シンプルにキャッシュする
     *
     * この関数は get/set/delete を兼ねる。
     * キャッシュがある場合はそれを返し、ない場合は $provider を呼び出してその結果をキャッシュしつつそれを返す。
     *
     * $provider に null を与えるとキャッシュの削除となる。
     *
     * Example:
     * ```php
     * $provider = function(){return rand();};
     * // 乱数を返す処理だが、キャッシュされるので同じ値になる
     * $rand1 = cache('rand', $provider);
     * $rand2 = cache('rand', $provider);
     * assertSame($rand1, $rand2);
     * // $provider に null を与えると削除される
     * cache('rand', null);
     * $rand3 = cache('rand', $provider);
     * assertNotSame($rand1, $rand3);
     * ```
     *
     * @param string $key キャッシュのキー
     * @param callable $provider キャッシュがない場合にコールされる callable
     * @param string $namespace 名前空間
     * @return mixed キャッシュ
     */
    function cache($key, $provider, $namespace = null)
    {
        static $cacheobject;
        $cacheobject = $cacheobject ?? new class(cachedir())
            {
                const CACHE_EXT = '.php-cache';

                /** @var string キャッシュディレクトリ */
                private $cachedir;

                /** @var array 内部キャッシュ */
                private $cache;

                /** @var array 変更感知配列 */
                private $changed;

                public function __construct($cachedir)
                {
                    $this->cachedir = $cachedir;
                    $this->cache = [];
                    $this->changed = [];
                }

                public function __destruct()
                {
                    // 変更されているもののみ保存
                    foreach ($this->changed as $namespace => $dummy) {
                        $filepath = $this->cachedir . '/' . rawurlencode($namespace) . self::CACHE_EXT;
                        $content = "<?php\nreturn " . var_export($this->cache[$namespace], true) . ";\n";

                        $temppath = tempnam(sys_get_temp_dir(), 'cache');
                        if (file_put_contents($temppath, $content) !== false) {
                            @chmod($temppath, 0644);
                            if (!@rename($temppath, $filepath)) {
                                @unlink($temppath);
                            }
                        }
                    }
                }

                public function has($namespace, $key)
                {
                    // ファイルから読み込む必要があるので get しておく
                    $this->get($namespace, $key);
                    return array_key_exists($key, $this->cache[$namespace]);
                }

                public function get($namespace, $key)
                {
                    // 名前空間自体がないなら作る or 読む
                    if (!isset($this->cache[$namespace])) {
                        $nsarray = [];
                        $cachpath = $this->cachedir . '/' . rawurldecode($namespace) . self::CACHE_EXT;
                        if (file_exists($cachpath)) {
                            $nsarray = require $cachpath;
                        }
                        $this->cache[$namespace] = $nsarray;
                    }

                    return $this->cache[$namespace][$key] ?? null;
                }

                public function set($namespace, $key, $value)
                {
                    // 新しい値が来たら変更フラグを立てる
                    if (!isset($this->cache[$namespace]) || !array_key_exists($key, $this->cache[$namespace]) || $this->cache[$namespace][$key] !== $value) {
                        $this->changed[$namespace] = true;
                    }

                    $this->cache[$namespace][$key] = $value;
                }

                public function delete($namespace, $key)
                {
                    $this->changed[$namespace] = true;
                    unset($this->cache[$namespace][$key]);
                }

                public function clear()
                {
                    // インメモリ情報をクリアして・・・
                    $this->cache = [];
                    $this->changed = [];

                    // ファイルも消す
                    foreach (glob($this->cachedir . '/*' . self::CACHE_EXT) as $file) {
                        unlink($file);
                    }
                }
            };

        // flush (for test)
        if ($key === null) {
            $cacheobject = null;
            return;
        }

        $namespace = $namespace ?? __FILE__;

        $exist = $cacheobject->has($namespace, $key);
        if ($provider === null) {
            $cacheobject->delete($namespace, $key);
            return $exist;
        }
        if (!$exist) {
            $cacheobject->set($namespace, $key, $provider());
        }
        return $cacheobject->get($namespace, $key);
    }
}
if (function_exists("ryunosuke\\dbml\\cache") && !defined("ryunosuke\\dbml\\cache")) {
    define("ryunosuke\\dbml\\cache", "ryunosuke\\dbml\\cache");
}

if (!isset($excluded_functions["arrayval"]) && (!function_exists("ryunosuke\\dbml\\arrayval") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\arrayval"))->isInternal()))) {
    /**
     * array キャストの関数版
     *
     * intval とか strval とかの array 版。
     * ただキャストするだけだが、関数なのでコールバックとして使える。
     *
     * $recursive を true にすると再帰的に適用する（デフォルト）。
     * 入れ子オブジェクトを配列化するときなどに使える。
     *
     * Example:
     * ```php
     * // キャストなので基本的には配列化される
     * assertSame(arrayval(123), [123]);
     * assertSame(arrayval('str'), ['str']);
     * assertSame(arrayval([123]), [123]); // 配列は配列のまま
     *
     * // $recursive = false にしない限り再帰的に適用される
     * $stdclass = stdclass(['key' => 'val']);
     * assertSame(arrayval([$stdclass], true), [['key' => 'val']]); // true なので中身も配列化される
     * assertSame(arrayval([$stdclass], false), [$stdclass]);       // false なので中身は変わらない
     * ```
     *
     * @param mixed $var array 化する値
     * @param bool $recursive 再帰的に行うなら true
     * @return array array 化した配列
     */
    function arrayval($var, $recursive = true)
    {
        // return json_decode(json_encode($var), true);

        // 無駄なループを回したくないので非再帰で配列の場合はそのまま返す
        if (!$recursive && is_array($var)) {
            return $var;
        }

        if (is_primitive($var)) {
            return (array) $var;
        }

        $result = [];
        foreach ($var as $k => $v) {
            if ($recursive && !is_primitive($v)) {
                $v = arrayval($v, $recursive);
            }
            $result[$k] = $v;
        }
        return $result;
    }
}
if (function_exists("ryunosuke\\dbml\\arrayval") && !defined("ryunosuke\\dbml\\arrayval")) {
    define("ryunosuke\\dbml\\arrayval", "ryunosuke\\dbml\\arrayval");
}

if (!isset($excluded_functions["is_empty"]) && (!function_exists("ryunosuke\\dbml\\is_empty") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\is_empty"))->isInternal()))) {
    /**
     * 値が空か検査する
     *
     * `empty` とほぼ同じ。ただし
     *
     * - string: "0"
     * - countable でない object
     * - countable である object で count() > 0
     *
     * は false 判定する。
     * ただし、 $empty_stcClass に true を指定すると「フィールドのない stdClass」も true を返すようになる。
     * これは stdClass の立ち位置はかなり特殊で「フィールドアクセスできる組み込み配列」のような扱いをされることが多いため。
     * （例えば `json_decode('{}')` は stdClass を返すが、このような状況は空判定したいことが多いだろう）。
     *
     * なお、関数の仕様上、未定義変数を true 判定することはできない。
     * 未定義変数をチェックしたい状況は大抵の場合コードが悪いが `$array['key1']['key2']` を調べたいことはある。
     * そういう時には使えない（?? する必要がある）。
     *
     * 「 `if ($var) {}` で十分なんだけど "0" が…」という状況はまれによくあるはず。
     *
     * Example:
     * ```php
     * // この辺は empty と全く同じ
     * assertTrue(is_empty(null));
     * assertTrue(is_empty(false));
     * assertTrue(is_empty(0));
     * assertTrue(is_empty(''));
     * // この辺だけが異なる
     * assertFalse(is_empty('0'));
     * assertFalse(is_empty(new \SimpleXMLElement('<foo></foo>')));
     * // 第2引数に true を渡すと空の stdClass も empty 判定される
     * $stdclass = new \stdClass();
     * assertTrue(is_empty($stdclass, true));
     * // フィールドがあれば empty ではない
     * $stdclass->hoge = 123;
     * assertFalse(is_empty($stdclass, true));
     * ```
     *
     * @param mixed $var 判定する値
     * @param bool $empty_stdClass 空の stdClass を空とみなすか
     * @return bool 空なら true
     */
    function is_empty($var, $empty_stdClass = false)
    {
        // object は is_countable 次第
        if (is_object($var)) {
            // が、 stdClass だけは特別扱い（stdClass は継承もできるので、クラス名で判定する（継承していたらそれはもう stdClass ではないと思う））
            if ($empty_stdClass && get_class($var) === 'stdClass') {
                return !(array) $var;
            }
            if (is_countable($var)) {
                return !count($var);
            }
            return false;
        }

        // "0" は false
        if ($var === '0') {
            return false;
        }

        // 上記以外は empty に任せる
        return empty($var);
    }
}
if (function_exists("ryunosuke\\dbml\\is_empty") && !defined("ryunosuke\\dbml\\is_empty")) {
    define("ryunosuke\\dbml\\is_empty", "ryunosuke\\dbml\\is_empty");
}

if (!isset($excluded_functions["is_primitive"]) && (!function_exists("ryunosuke\\dbml\\is_primitive") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\is_primitive"))->isInternal()))) {
    /**
     * 値が複合型でないか検査する
     *
     * 「複合型」とはオブジェクトと配列のこと。
     * つまり
     *
     * - is_scalar($var) || is_null($var) || is_resource($var)
     *
     * と同義（!is_array($var) && !is_object($var) とも言える）。
     *
     * Example:
     * ```php
     * assertTrue(is_primitive(null));
     * assertTrue(is_primitive(false));
     * assertTrue(is_primitive(123));
     * assertTrue(is_primitive(STDIN));
     * assertFalse(is_primitive(new \stdClass));
     * assertFalse(is_primitive(['array']));
     * ```
     *
     * @param mixed $var 調べる値
     * @return bool 複合型なら false
     */
    function is_primitive($var)
    {
        return is_scalar($var) || is_null($var) || is_resource($var);
    }
}
if (function_exists("ryunosuke\\dbml\\is_primitive") && !defined("ryunosuke\\dbml\\is_primitive")) {
    define("ryunosuke\\dbml\\is_primitive", "ryunosuke\\dbml\\is_primitive");
}

if (!isset($excluded_functions["is_iterable"]) && (!function_exists("ryunosuke\\dbml\\is_iterable") || (!true && (new \ReflectionFunction("ryunosuke\\dbml\\is_iterable"))->isInternal()))) {
    /**
     * 変数が foreach で回せるか調べる
     *
     * オブジェクトの場合は \Traversable のみ。
     * 要するに {@link http://php.net/manual/function.is-iterable.php is_iterable} の polyfill。
     *
     * Example:
     * ```php
     * assertTrue(is_iterable([1, 2, 3]));
     * assertTrue(is_iterable((function () { yield 1; })()));
     * assertFalse(is_iterable(1));
     * assertFalse(is_iterable(new \stdClass()));
     * ```
     *
     * @polyfill
     *
     * @param mixed $var 調べる値
     * @return bool foreach で回せるなら true
     */
    function is_iterable($var)
    {
        return is_array($var) || $var instanceof \Traversable;
    }
}
if (function_exists("ryunosuke\\dbml\\is_iterable") && !defined("ryunosuke\\dbml\\is_iterable")) {
    define("ryunosuke\\dbml\\is_iterable", "ryunosuke\\dbml\\is_iterable");
}

if (!isset($excluded_functions["is_countable"]) && (!function_exists("ryunosuke\\dbml\\is_countable") || (!true && (new \ReflectionFunction("ryunosuke\\dbml\\is_countable"))->isInternal()))) {
    /**
     * 変数が count でカウントできるか調べる
     *
     * 要するに {@link http://php.net/manual/function.is-countable.php is_countable} の polyfill。
     *
     * Example:
     * ```php
     * assertTrue(is_countable([1, 2, 3]));
     * assertTrue(is_countable(new \ArrayObject()));
     * assertFalse(is_countable((function () { yield 1; })()));
     * assertFalse(is_countable(1));
     * assertFalse(is_countable(new \stdClass()));
     * ```
     *
     * @polyfill
     *
     * @param mixed $var 調べる値
     * @return bool count でカウントできるなら true
     */
    function is_countable($var)
    {
        return is_array($var) || $var instanceof \Countable;
    }
}
if (function_exists("ryunosuke\\dbml\\is_countable") && !defined("ryunosuke\\dbml\\is_countable")) {
    define("ryunosuke\\dbml\\is_countable", "ryunosuke\\dbml\\is_countable");
}

if (!isset($excluded_functions["var_apply"]) && (!function_exists("ryunosuke\\dbml\\var_apply") || (!false && (new \ReflectionFunction("ryunosuke\\dbml\\var_apply"))->isInternal()))) {
    /**
     * 値にコールバックを適用する
     *
     * 普通のスカラー値であれば `$callback($var)` と全く同じ。
     * この関数は「$var が配列だったら中身に適用して返す（再帰）」という点で上記とは異なる。
     *
     * 「配列が与えられたら要素に適用して配列で返す、配列じゃないなら直に適用してそれを返す」という状況はまれによくあるはず。
     *
     * Example:
     * ```php
     * // 素の値は素の呼び出しと同じ
     * assertSame(var_apply(' x ', 'trim'), 'x');
     * // 配列は中身に適用して配列で返す（再帰）
     * assertSame(var_apply([' x ', ' y ', [' z ']], 'trim'), ['x', 'y', ['z']]);
     * // 第3引数以降は残り引数を意味する
     * assertSame(var_apply(['!x!', '!y!'], 'trim', '!'), ['x', 'y']);
     * // 「まれによくある」の具体例
     * assertSame(var_apply(['<x>', ['<y>']], 'htmlspecialchars', ENT_QUOTES, 'utf-8'), ['&lt;x&gt;', ['&lt;y&gt;']]);
     * ```
     *
     * @param mixed $var $callback を適用する値
     * @param callable $callback 値変換コールバック
     * @param array $args $callback の残り引数（可変引数）
     * @return mixed|array $callback が適用された値。元が配列なら配列で返す
     */
    function var_apply($var, $callback, ...$args)
    {
        $iterable = is_iterable($var);
        if ($iterable) {
            $result = [];
            foreach ($var as $k => $v) {
                $result[$k] = var_apply($v, $callback, ...$args);
            }
            return $result;
        }

        return $callback($var, ...$args);
    }
}
if (function_exists("ryunosuke\\dbml\\var_apply") && !defined("ryunosuke\\dbml\\var_apply")) {
    define("ryunosuke\\dbml\\var_apply", "ryunosuke\\dbml\\var_apply");
}
