<?php

namespace ryunosuke\Test\dbml\Generator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use ryunosuke\dbml\Generator\Yielder;
use ryunosuke\Test\Database;

class YielderTest extends \ryunosuke\Test\AbstractUnitTestCase
{
    /**
     * @dataProvider provideDatabase
     * @param Database $database
     */
    function test_statement($database)
    {
        $g = new Yielder(function () {
            return;
        }, $database->getConnection());

        $this->assertException(new \RuntimeException('invalid'), function () use ($g) {
            iterator_to_array($g);
        });
    }

    /**
     * @dataProvider provideDatabase
     * @param Database $database
     */
    function test_all($database)
    {
        $g = new Yielder($database->executeQuery('select * from multiprimary'), $database->getConnection(), 'array');
        $actual = [];
        foreach ($g as $k => $v) {
            $actual[] = [$k => $v];
        }
        $this->assertEquals([
            [0 => ['mainid' => '1', 'subid' => '1', 'name' => 'a',],],
            [1 => ['mainid' => '1', 'subid' => '2', 'name' => 'b',],],
            [2 => ['mainid' => '1', 'subid' => '3', 'name' => 'c',],],
            [3 => ['mainid' => '1', 'subid' => '4', 'name' => 'd',],],
            [4 => ['mainid' => '1', 'subid' => '5', 'name' => 'e',],],
            [5 => ['mainid' => '2', 'subid' => '6', 'name' => 'f',],],
            [6 => ['mainid' => '2', 'subid' => '7', 'name' => 'g',],],
            [7 => ['mainid' => '2', 'subid' => '8', 'name' => 'h',],],
            [8 => ['mainid' => '2', 'subid' => '9', 'name' => 'i',],],
            [9 => ['mainid' => '2', 'subid' => '10', 'name' => 'j',],],
        ], $actual);
    }

    /**
     * @dataProvider provideDatabase
     * @param Database $database
     */
    function test_assoc($database)
    {
        $g = new Yielder($database->executeQuery('select * from multiprimary'), $database->getConnection(), 'assoc');
        $actual = [];
        foreach ($g as $k => $v) {
            $actual[] = [$k => $v];
        }
        $this->assertEquals([
            [1 => ['mainid' => '1', 'subid' => '1', 'name' => 'a',],],
            [2 => ['mainid' => '2', 'subid' => '6', 'name' => 'f',],],
        ], $actual);
    }

    /**
     * @dataProvider provideDatabase
     * @param Database $database
     */
    function test_lists($database)
    {
        $g = new Yielder($database->executeQuery('select * from multiprimary'), $database->getConnection(), 'lists');
        $actual = [];
        foreach ($g as $k => $v) {
            $actual[] = [$k => $v];
        }
        $this->assertEquals([
            [0 => '1',],
            [1 => '1',],
            [2 => '1',],
            [3 => '1',],
            [4 => '1',],
            [5 => '2',],
            [6 => '2',],
            [7 => '2',],
            [8 => '2',],
            [9 => '2',],
        ], $actual);
    }

    /**
     * @dataProvider provideDatabase
     * @param Database $database
     */
    function test_pairs($database)
    {
        $g = new Yielder($database->executeQuery('select * from multiprimary'), $database->getConnection(), 'pairs');
        $actual = [];
        foreach ($g as $k => $v) {
            $actual[] = [$k => $v];
        }
        $this->assertEquals([
            [1 => '1',],
            [2 => '6',],
        ], $actual);
    }

    /**
     * @dataProvider provideDatabase
     * @param Database $database
     */
    function test_misc($database)
    {
        $g = new Yielder(function (Connection $c) {
            return $c->executeQuery('select * from multiprimary');
        }, $database->getConnection(), 'pairs', function ($row) {
            $row['subid'] = $row['subid'] * 10;
            return $row;
        });
        $actual = [];
        foreach ($g as $k => $v) {
            $actual[] = [$k => $v];
        }
        $this->assertEquals([
            [1 => 10,],
            [2 => 60,],
        ], $actual);
    }

    /**
     * @dataProvider provideDatabase
     * @param Database $database
     */
    function test_method($database)
    {
        $g = new Yielder($database->executeQuery('select * from multiprimary'), $database->getConnection());
        $g->setFetchMethod('hoge');
        $ex = new \UnexpectedValueException("method 'hoge' is undefined");
        $this->assertException($ex, L($g)->key());
        $this->assertException($ex, L($g)->current());
    }

    /**
     * @dataProvider provideDatabase
     * @param Database $database
     */
    function test_unique($database)
    {
        $g = new Yielder($database->executeQuery('select * from multiprimary'), $database->getConnection(), 'pairs');
        $g->setEmulationUnique(false);
        $actual = [];
        foreach ($g as $k => $v) {
            $actual[] = [$k => $v];
        }
        $this->assertEquals([
            [1 => '1',],
            [1 => '2',],
            [1 => '3',],
            [1 => '4',],
            [1 => '5',],
            [2 => '6',],
            [2 => '7',],
            [2 => '8',],
            [2 => '9',],
            [2 => '10',],
        ], $actual);
    }

    /**
     * @runInSeparateProcess
     */
    function test_buffered()
    {
        $database = self::getDummyDatabase();
        $database->getConnection()->getSchemaManager()->createTable(
            new Table('t_heavy',
                [
                    new Column('id', Type::getType('integer'), ['autoincrement' => true]),
                    new Column('data', Type::getType('text')),
                ],
                [new Index('PRIMARY', ['id'], true, true)]
            )
        );
        $database->getSchema()->refresh();

        // 100KB のレコードを100行用意（10MB）
        $database->begin();
        $database->insertArray('t_heavy', call_user_func(function () {
            foreach (range(1, 100) as $n) {
                yield ['data' => $n . str_repeat('x', 1024 * 100)];
            }
        }));
        $database->commit();

        // 暖機運転
        $query = $database->select('t_heavy')->limit(10)->queryInto();
        $database->yieldArray($query);
        $database->fetchArray($query);

        // yieldArray なら現在値＋3MB に制限しても落ちない
        gc_collect_cycles();
        ini_set('memory_limit', memory_get_usage(true) + 3000 * 1024);
        $count = 0;
        foreach ($database->yieldArray('select * from t_heavy') as $row) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $dummy = $row;
            $count++;
        }
        $this->assertEquals(100, $count);

        // fetchArray だと落ちる
        gc_collect_cycles();
        ini_set('memory_limit', memory_get_usage(true) + 3000 * 1024);
        $count = 0;
        foreach (@$database->fetchArray('select * from t_heavy') as $row) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $dummy = $row;
            $count++;
        }
        $this->assertEquals(100, $count);

        // Fatal memory のテストをどうやったら良いのか分からん
        // とりあえず @runInSeparateProcess は標準エラーと標準出力で色々しているようなので下手にいじれん
        // が、出力を何も出さなければパスしたとみなされるようなので @ で抑制して到達し得ない箇所で自ら echo するようにした
        echo $this->getName(false) . ' is failed.';
    }
}
