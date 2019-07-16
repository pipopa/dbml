<?php

namespace ryunosuke\Test\dbml\Query\Expression;

use ryunosuke\dbml\Query\Expression\Alias;
use ryunosuke\dbml\Query\Expression\PhpExpression;

class PhpExpressionTest extends \ryunosuke\Test\AbstractUnitTestCase
{
    function test___construct()
    {
        $phpe = new PhpExpression(function () { }, new Alias('a', 'c'));
        $this->assertEquals(['a' => new Alias('a', 'c')], $phpe->getDependColumns());

        $phpe = new PhpExpression(function () { }, 'T.c');
        $this->assertEquals(['c' => new Alias('c', 'T.c')], $phpe->getDependColumns());

        $phpe = new PhpExpression(function () { }, 'c');
        $this->assertEquals(['c' => new Alias(null, 'c')], $phpe->getDependColumns());

        $phpe = new PhpExpression(function () { }, ['a' => 'c']);
        $this->assertEquals(['a' => new Alias('a', 'c')], $phpe->getDependColumns());
    }

    function test_forge()
    {
        $phpe = PhpExpression::forge(function ($hoge = ['a' => 'c']) { });
        $this->assertEquals(['a' => new Alias('a', 'c')], $phpe->getDependColumns());

        $phpe = PhpExpression::forge(function ($hoge = null) { }, 'hoge');
        $this->assertEquals([], $phpe->getDependColumns());

        $phpe = PhpExpression::forge(123);
        $this->assertEquals(123, $phpe);

        $phpe = PhpExpression::forge('strlen');
        $this->assertEquals('strlen', $phpe);

        $this->assertException('can not be derived', function () {
            PhpExpression::forge(function ($hoge, $fuga) { }, 'c');
        });
    }

    function test___invoke()
    {
        $phpe = new PhpExpression(null);
        $this->assertEquals(null, $phpe(null));

        $phpe = new PhpExpression(123);
        $this->assertEquals(123, $phpe(null));

        $phpe = new PhpExpression('str');
        $this->assertEquals('str', $phpe(null));

        $phpe = new PhpExpression([1, 2, 3]);
        $this->assertEquals([1, 2, 3], $phpe(null));

        $phpe = new PhpExpression(new \stdClass());
        $this->assertEquals(new \stdClass(), $phpe(null));

        $phpe = new PhpExpression('strlen');
        $this->assertEquals(3, $phpe('abc'));

        $phpe = PhpExpression::forge(function ($a = 'a', $b = 'b', $c = 'c') {
            return "$a-$b-$c";
        });
        $this->assertEquals('A-B-C', $phpe(['a' => 'A', 'b' => 'B', 'c' => 'C']));
    }

    function test_deffunc()
    {
        $phpe = PhpExpression::forge(PhpExpression::explode(','), 'v');
        $this->assertEquals(null, $phpe(['v' => null]));
        $this->assertEquals(['1', '2', '3', '4'], $phpe(['v' => '1,2,,3,,4']));

        $phpe = PhpExpression::forge(PhpExpression::sprintf('%04d'), 'v');
        $this->assertEquals(null, $phpe(['v' => null]));
        $this->assertEquals('0123', $phpe(['v' => '123']));

        $phpe = PhpExpression::forge(PhpExpression::date('YmdHis'), 'v');
        $this->assertEquals(null, $phpe(['v' => null]));
        $this->assertEquals('20121212121212', $phpe(['v' => '2012/12/12 12:12:12']));
        $this->assertEquals('20090214083130', $phpe(['v' => '1234567890']));

        // 既存の上書き
        PhpExpression::define('explode', function ($v, $x) {
            return explode($x, $v);
        });

        // 新規追加
        PhpExpression::define('trimx', function ($v, $x) {
            return trim($v, $x);
        });

        $phpe = PhpExpression::forge(PhpExpression::explode('-'), 'v');
        $this->assertEquals(['1', '2', '', '3', '4'], $phpe(['v' => '1-2--3-4']));

        /** @noinspection PhpUndefinedMethodInspection */
        $phpe = PhpExpression::forge(PhpExpression::trimx('_'), 'v');
        $this->assertEquals('a', $phpe(['v' => '__a__']));

        // 存在しない
        $this->assertException('not defined', function () {
            /** @noinspection PhpUndefinedMethodInspection */
            PhpExpression::notfound();
        });
    }

    function test_args()
    {
        $row = [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
            'e' => 'E',
        ];

        $phpe = new PhpExpression(function ($a) { return $a; }, 'a');
        $this->assertEquals('A', $phpe($row));

        $phpe = new PhpExpression(function ($a, $b) { return $a . $b; }, 'a', 'b');
        $this->assertEquals('AB', $phpe($row));

        $phpe = new PhpExpression(function ($a, $b, $c) { return $a . $b . $c; }, 'a', 'b', 'c');
        $this->assertEquals('ABC', $phpe($row));

        $phpe = new PhpExpression(function ($a, $b, $c, $d) { return $a . $b . $c . $d; }, 'a', 'b', 'c', 'd');
        $this->assertEquals('ABCD', $phpe($row));

        $phpe = new PhpExpression(function ($a, $b, $c, $d, $e) { return $a . $b . $c . $d . $e; }, 'a', 'b', 'c', 'd', 'e');
        $this->assertEquals('ABCDE', $phpe($row));
    }
}
