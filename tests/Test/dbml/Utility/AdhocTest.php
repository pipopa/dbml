<?php

namespace ryunosuke\Test\dbml\Utility;

use ryunosuke\dbml\Utility\Adhoc;

class AdhocTest extends \ryunosuke\Test\AbstractUnitTestCase
{
    function test_argumentize()
    {
        $this->assertEquals([], Adhoc::argumentize([]));
        $this->assertEquals([1, 2, 3], Adhoc::argumentize([1, 2, 3]));
        $this->assertEquals([1, 2, 3], Adhoc::argumentize([[1, 2, 3]]));
    }

    function test_reargument()
    {
        $closure = function ($a = 1, $b = 2, $c = 3) { };

        $arguments = [10, 20];
        $this->assertEquals([], Adhoc::reargument($arguments, $closure));
        $this->assertEquals([10, 20, 3], $arguments);

        $arguments = [10, 'x', 20];
        $this->assertEquals(['oa' => 'x'], Adhoc::reargument($arguments, $closure, [1 => 'oa']));
        $this->assertEquals([10, 20, 3], $arguments);

        $arguments = [10, 'x', 'y', 20, 30];
        $this->assertEquals(['oa' => 'x', 'ob' => 'y'], Adhoc::reargument($arguments, $closure, [1 => 'oa', 2 => 'ob']));
        $this->assertEquals([10, 20, 30], $arguments);

        $arguments = [10, 'x', 20, 30, 'z'];
        $this->assertEquals(['oa' => 'x', 'oc' => 'z'], Adhoc::reargument($arguments, $closure, [1 => 'oa', 4 => 'oc']));
        $this->assertEquals([10, 20, 30], $arguments);

        $arguments = [1];
        $this->assertEquals([], Adhoc::reargument($arguments, function ($a, $b = 20) { }));
        $this->assertEquals([1, 20], $arguments);

        $arguments = [1];
        $this->assertEquals([], Adhoc::reargument($arguments, function ($a, $b = 20, $c = 30) { }));
        $this->assertEquals([1, 20, 30], $arguments);

        $this->assertException('too short', function () {
            $arguments = [];
            Adhoc::reargument($arguments, function ($x) { });
        });
    }

    function test_to_hash()
    {
        $this->assertEquals(['hoge' => 'hoge'], Adhoc::to_hash('hoge'));
        $this->assertEquals(['fuga' => 'fuga'], Adhoc::to_hash(['fuga']));
        $this->assertEquals(['hoge' => 'fuga'], Adhoc::to_hash(['hoge' => 'fuga']));

        $this->assertEquals(['hoge' => 'fuga', 'piyo' => 'PIYO'], Adhoc::to_hash(['hoge' => 'fuga', 'piyo'], function ($v) {
            return strtoupper($v);
        }));
    }

    function test_array_push()
    {
        $target = [];
        $this->assertTrue(Adhoc::array_push($target, 'hoge'));
        $this->assertEquals([0 => 'hoge'], $target);
        $this->assertTrue(Adhoc::array_push($target, 'fuga', 'a'));
        $this->assertEquals([0 => 'hoge', 'a' => 'fuga'], $target);

        $target = [];
        $this->assertFalse(Adhoc::array_push($target, ''));
        $this->assertEquals([], $target);
        $this->assertFalse(Adhoc::array_push($target, null));
        $this->assertEquals([], $target);
        $this->assertFalse(Adhoc::array_push($target, []));
        $this->assertEquals([], $target);
    }

    function test_is_empty()
    {
        $this->assertTrue(Adhoc::is_empty([]));
        $this->assertTrue(Adhoc::is_empty(null));
        $this->assertTrue(Adhoc::is_empty(''));

        $this->assertFalse(Adhoc::is_empty([1]));
        $this->assertFalse(Adhoc::is_empty('0'));
        $this->assertFalse(Adhoc::is_empty(0));
        $this->assertFalse(Adhoc::is_empty(false));

        $this->assertFalse(Adhoc::is_empty(self::getDummyDatabase()->select('t', ['id' => 1])));
        $this->assertFalse(Adhoc::is_empty(self::getDummyDatabase()->select('t', ['!id' => 1])));
        $this->assertTrue(Adhoc::is_empty(self::getDummyDatabase()->select('t', ['!id' => null])));
        $this->assertTrue(Adhoc::is_empty(self::getDummyDatabase()->select('t', ['id' => 1, '!id' => null])));
    }

    function test_parse_ini()
    {
        // 空
        $this->assertSame([], Adhoc::parse_ini(""));

        // 値系
        $this->assertSame(['a' => "1"], Adhoc::parse_ini("a=1"));
        $this->assertSame(['a' => ""], Adhoc::parse_ini("a=null"));
        $this->assertSame(['a' => "1"], Adhoc::parse_ini("a=true"));
        $this->assertSame(['a' => ""], Adhoc::parse_ini("a=false"));
        $this->assertSame(['a' => "1", 'b' => "1"], Adhoc::parse_ini("a=1\nb=on"));
        $this->assertSame(['a' => "on"], Adhoc::parse_ini("a='on'"));
        $this->assertSame(['a' => "o\nn"], Adhoc::parse_ini("a='o\nn'"));

        // 階層
        $this->assertSame(['x' => ['a' => '1', 'b' => '2', 'c' => '3']], Adhoc::parse_ini("x.a=1\nx.b=2\nx.c=3"));
        $this->assertSame(['a' => ['b' => ['c' => 'abc']]], Adhoc::parse_ini("a.b.c=abc"));

        // パースエラー
        $this->assertSame(['valid1' => 'ok1', 'valid2' => 'ok2', 'valid3' => 'ok3'], Adhoc::parse_ini("
valid1=ok1  ;行頭OK
invalid()   ;不正な文字
invalid='aa ;閉じていないクオート
valid2=ok2  ;真ん中OK
invalid=a NO;クオートされていない
valid3=ok3  ;行末OK
"));

        // 例外
        $this->assertException('already exists', function () {
            Adhoc::parse_ini("a=a\na.b=ab");
        });
    }

    function test_wrapParentheses()
    {
        // シンプル配列
        $this->assertEquals([], Adhoc::wrapParentheses([]));
        $this->assertEquals(['hoge'], Adhoc::wrapParentheses(['hoge']));
        $this->assertEquals(['x' => 'hoge'], Adhoc::wrapParentheses(['x' => 'hoge']));
        $this->assertEquals(['(hoge)', '(fuga)'], Adhoc::wrapParentheses(['hoge', 'fuga']));
        $this->assertEquals(['x' => '(hoge)', 'y' => '(fuga)'], Adhoc::wrapParentheses(['x' => 'hoge', 'y' => 'fuga']));

        // 入れ子配列
        $this->assertEquals([
            '(hoge)',
            'x' => [
                '(fuga)',
                'y' => [
                    '(piyo)',
                    'z' => ['zzz']
                ],
            ],
        ], Adhoc::wrapParentheses([
            'hoge',
            'x' => [
                'fuga',
                'y' => [
                    'piyo',
                    'z' => ['zzz']
                ]
            ]
        ]));
    }

    function test_containQueryable()
    {
        $e = self::getDummyDatabase()->raw('column');
        $this->assertFalse(Adhoc::containQueryable(null));
        $this->assertFalse(Adhoc::containQueryable('hoge'));
        $this->assertFalse(Adhoc::containQueryable([]));
        $this->assertFalse(Adhoc::containQueryable(['hoge']));
        $this->assertFalse(Adhoc::containQueryable($e));
        $this->assertTrue(Adhoc::containQueryable([$e]));
        $this->assertTrue(Adhoc::containQueryable([$e, $e]));
        $this->assertTrue(Adhoc::containQueryable([null, $e, 'hoge']));
    }

    function test_modifier()
    {
        $this->assertEquals(['column'], Adhoc::modifier('', ['column']));
        $this->assertEquals(['c' => 'column'], Adhoc::modifier('', ['c' => 'column']));

        $this->assertEquals(['column'], Adhoc::modifier('T', ['column']));
        $this->assertEquals(['UPPER(column)' => 'hoge'], Adhoc::modifier('T', ['UPPER(column)' => 'hoge']));
        $this->assertEquals(['!T.c' => 'column'], Adhoc::modifier('T', ['!c' => 'column']));
        $this->assertEquals(['T.c' => 'column'], Adhoc::modifier('T', ['c' => 'column']));
        $this->assertEquals(['!T.c' => 'column'], Adhoc::modifier('T', ['!c' => 'column']));

        $qb = self::getDummyDatabase()->subexists('t', ['id' => 1]);
        $this->assertEquals(['c' => $qb], Adhoc::modifier('T', ['c' => $qb]));

        $qb = self::getDummyDatabase()->subquery('t', ['id' => 1]);
        $this->assertEquals(['T.c' => $qb], Adhoc::modifier('T', ['c' => $qb]));

        $e = self::getDummyDatabase()->raw('column');
        $this->assertEquals(['T.c' => $e], Adhoc::modifier('T', ['c' => $e]));
        $this->assertEquals(['c' => [$e]], Adhoc::modifier('T', ['c' => [$e]]));
    }

    function test_parseYmdHis_full()
    {
        $this->assertSame([
            'y' => 2000,
            'm' => false,
            'd' => false,
            'h' => false,
            'i' => false,
            's' => false,
        ], Adhoc::parseYmdHis('2000'));

        $this->assertSame([
            'y' => 2000,
            'm' => 12,
            'd' => false,
            'h' => false,
            'i' => false,
            's' => false,
        ], Adhoc::parseYmdHis('2000/12'));

        $this->assertSame([
            'y' => 2000,
            'm' => 12,
            'd' => 24,
            'h' => false,
            'i' => false,
            's' => false,
        ], Adhoc::parseYmdHis('2000/12/24'));

        $this->assertSame([
            'y' => 2000,
            'm' => 12,
            'd' => 24,
            'h' => 23,
            'i' => false,
            's' => false,
        ], Adhoc::parseYmdHis('2000/12/24 23'));

        $this->assertSame([
            'y' => 2000,
            'm' => 12,
            'd' => 24,
            'h' => 23,
            'i' => 34,
            's' => false,
        ], Adhoc::parseYmdHis('2000/12/24 23:34'));

        $this->assertSame([
            'y' => 2000,
            'm' => 12,
            'd' => 24,
            'h' => 23,
            'i' => 34,
            's' => 56,
        ], Adhoc::parseYmdHis('2000/12/24 23:34:56'));
    }

    function test_parseYmdHis_noyear()
    {
        $this->assertSame([
            'y' => false,
            'm' => 1,
            'd' => 2,
            'h' => false,
            'i' => false,
            's' => false,
        ], Adhoc::parseYmdHis('1/2'));

        $this->assertSame([
            'y' => false,
            'm' => 1,
            'd' => 2,
            'h' => false,
            'i' => false,
            's' => false,
        ], Adhoc::parseYmdHis('1/2'));

        $this->assertSame([
            'y' => false,
            'm' => 1,
            'd' => 2,
            'h' => 1,
            'i' => false,
            's' => false,
        ], Adhoc::parseYmdHis('1/2 1'));

        $this->assertSame([
            'y' => false,
            'm' => 1,
            'd' => 2,
            'h' => 1,
            'i' => 2,
            's' => false,
        ], Adhoc::parseYmdHis('1/2 1:2'));

        $this->assertSame([
            'y' => false,
            'm' => 1,
            'd' => 2,
            'h' => 1,
            'i' => 2,
            's' => 9,
        ], Adhoc::parseYmdHis('1/2 1:2:09'));
    }

    function test_parseYmdHis_invalid()
    {
        // ただの文字列はもちろん NG
        $this->assertFalse(Adhoc::parseYmdHis('abcdefg'));

        // 存在しない日時は NG
        $this->assertFalse(Adhoc::parseYmdHis('1999/13/12 12:34:56'));
        $this->assertFalse(Adhoc::parseYmdHis('1999/12/12 25:34:56'));
        $this->assertFalse(Adhoc::parseYmdHis('1999/02/29 12:34:56'));

        // 4桁年しかサポートしない（mysql がサポートしてない）
        $this->assertFalse(Adhoc::parseYmdHis('999'));
        $this->assertFalse(Adhoc::parseYmdHis('99'));
        $this->assertFalse(Adhoc::parseYmdHis('99/12/12'));
    }

    function test_fillYmdHis()
    {
        // 年指定
        $this->assertSame([
            'y' => 1999,
            'm' => 1,
            'd' => 1,
            'h' => 0,
            'i' => 0,
            's' => 0,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('1999'), true));
        $this->assertSame([
            'y' => 1999,
            'm' => 12,
            'd' => 31,
            'h' => 23,
            'i' => 59,
            's' => 59,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('1999'), false));

        // 年月指定
        $this->assertSame([
            'y' => 1999,
            'm' => 9,
            'd' => 1,
            'h' => 0,
            'i' => 0,
            's' => 0,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('1999/9'), true));
        $this->assertSame([
            'y' => 1999,
            'm' => 9,
            'd' => 30,
            'h' => 23,
            'i' => 59,
            's' => 59,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('1999/9'), false));

        // 年月日指定
        $this->assertSame([
            'y' => 1999,
            'm' => 9,
            'd' => 10,
            'h' => 0,
            'i' => 0,
            's' => 0,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('1999/9/10'), true));
        $this->assertSame([
            'y' => 1999,
            'm' => 9,
            'd' => 10,
            'h' => 23,
            'i' => 59,
            's' => 59,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('1999/9/10'), false));

        // 年月日時指定
        $this->assertSame([
            'y' => 1999,
            'm' => 9,
            'd' => 10,
            'h' => 11,
            'i' => 0,
            's' => 0,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('1999/9/10 11'), true));
        $this->assertSame([
            'y' => 1999,
            'm' => 9,
            'd' => 10,
            'h' => 11,
            'i' => 59,
            's' => 59,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('1999/9/10 11'), false));

        // 年月日時分指定
        $this->assertSame([
            'y' => 1999,
            'm' => 9,
            'd' => 10,
            'h' => 11,
            'i' => 22,
            's' => 0,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('1999/9/10 11:22'), true));
        $this->assertSame([
            'y' => 1999,
            'm' => 9,
            'd' => 10,
            'h' => 11,
            'i' => 22,
            's' => 59,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('1999/9/10 11:22'), false));

        // 年月日時分秒指定
        $this->assertSame([
            'y' => 1999,
            'm' => 9,
            'd' => 10,
            'h' => 11,
            'i' => 22,
            's' => 33,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('1999/9/10 11:22:33'), true));
        $this->assertSame([
            'y' => 1999,
            'm' => 9,
            'd' => 10,
            'h' => 11,
            'i' => 22,
            's' => 33,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('1999/9/10 11:22:33'), false));

        // 年未指定
        $this->assertSame([
            'y' => (int) idate('Y'),
            'm' => 9,
            'd' => 10,
            'h' => 0,
            'i' => 0,
            's' => 0,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('9/10'), true));
        $this->assertSame([
            'y' => (int) idate('Y'),
            'm' => 9,
            'd' => 10,
            'h' => 23,
            'i' => 59,
            's' => 59,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('9/10'), false));

        // 月未指定
        $this->assertSame([
            'y' => 2000,
            'm' => 2,
            'd' => 1,
            'h' => 0,
            'i' => 0,
            's' => 0,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('2000/2'), true));
        $this->assertSame([
            'y' => 2000,
            'm' => 2,
            'd' => 29,
            'h' => 23,
            'i' => 59,
            's' => 59,
        ], Adhoc::fillYmdHis(Adhoc::parseYmdHis('2000/2'), false));
    }
}
