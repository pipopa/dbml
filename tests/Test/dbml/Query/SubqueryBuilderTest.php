<?php

namespace ryunosuke\Test\dbml\Query;

use Doctrine\DBAL\LockMode;
use ryunosuke\dbml\Entity\Entity;
use ryunosuke\dbml\Query\SubqueryBuilder;
use ryunosuke\Test\Database;

class SubqueryBuilderTest extends \ryunosuke\Test\AbstractUnitTestCase
{
    public static function provideQueryBuilder()
    {
        return array_map(function ($v) {
            return [
                new SubqueryBuilder($v[0]),
                $v[0],
            ];
        }, parent::provideDatabase());
    }

    /**
     * @dataProvider provideConnection
     * @param \Doctrine\DBAL\Connection $connection
     */
    function test_getDefaultOptions($connection)
    {
        $database = new Database($connection);
        $builder = new SubqueryBuilder($database);
        $options = $builder::getDefaultOptions();
        foreach ($options as $key => $dummy) {
            $this->assertSame($builder, $builder->{'set' . $key}($key));
        }
        foreach ($options as $key => $dummy) {
            $this->assertSame($key, $builder->{'get' . $key}());
        }
    }

    /**
     * @dataProvider provideQueryBuilder
     * @param SubqueryBuilder $builder
     */
    function test___call($builder)
    {
        $this->assertException(new \UnexpectedValueException("subquery must be lazy mode"), L($builder)->array());

        $builder->reset()->select('*')->from('test1')->lazy('select', 'id');
        $this->assertInstanceOf(get_class($builder), $builder->array());
        $this->assertInstanceOf(get_class($builder), $builder->cast(Entity::class)->tuple());

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertException(new \BadMethodCallException("is undefined"), L($builder)->hogeFuga());
    }

    /**
     * @dataProvider provideQueryBuilder
     * @param SubqueryBuilder $builder
     */
    function test_lazy($builder)
    {
        $builder->lazy('select', 'hoge');
        $this->assertEquals(['hoge' => 'hoge'], self::forcedRead($builder, 'lazyColumns'));

        $builder->lazy('select', ['fuga']);
        $this->assertEquals(['fuga' => 'fuga'], self::forcedRead($builder, 'lazyColumns'));

        $builder->lazy('select', ['fuga' => 'hoge']);
        $this->assertEquals(['fuga' => 'hoge'], self::forcedRead($builder, 'lazyColumns'));

        $this->assertException(new \InvalidArgumentException('$mode is must be'), L($builder)->lazy('dummy', []));
        $this->assertException(new \InvalidArgumentException('is a need to 1 or more'), L($builder)->lazy('select', []));
    }

    /**
     * @dataProvider provideQueryBuilder
     * @param SubqueryBuilder $builder
     */
    function test_setLazyColumnFrom($builder)
    {
        $this->assertEquals([], $builder->reset()->from('foreign_p')->lazy('select', ['id'])->setLazyColumnFrom('foreign_c1'));
        $this->assertEquals(['foreign_p.id' => 'cid'], $builder->reset()->from('foreign_p')->lazy('select', null)->setLazyColumnFrom('foreign_c2'));
        $this->assertEquals(['foreign_c1.id' => 'id'], $builder->reset()->from('foreign_c1')->lazy('select', null)->setLazyColumnFrom('foreign_p'));
        $this->assertEquals(['P.id' => 'cid'], $builder->reset()->from('foreign_p', 'P')->lazy('select', null)->setLazyColumnFrom('foreign_c2'));

        $builder->reset()->lazy('select', null);
        $this->assertException(new \UnexpectedValueException('basetable is not specified'), L($builder)->setLazyColumnFrom(null));

        $builder->reset()->from('foreign_d1')->lazy('select', null);
        $this->assertException(new \UnexpectedValueException('ambiguous foreign keys'), L($builder)->setLazyColumnFrom('foreign_d2'));

        $builder->reset()->from('test1')->lazy('select', null);
        $this->assertException(new \UnexpectedValueException('foreign key is not found'), L($builder)->setLazyColumnFrom('test2'));
    }

    /**
     * @dataProvider provideQueryBuilder
     * @param SubqueryBuilder $builder
     */
    function test_delaying($builder)
    {
        // この段階で getDelayedParams は空配列
        $this->assertEquals([], $builder->getDelayedParams());

        // delay する
        $builder->lazy('select', 'hoge')->delay();
        $this->assertEquals(['hoge' => 'hoge'], self::forcedRead($builder, 'lazyColumns'));

        // その状態で build しても何も行われない
        $builder->build(['column' => 'id']);
        $this->assertEmpty($builder->getQueryPart('select'));

        // その後 build すると build が実行される
        $builder->build(['column' => 'test.name']);
        $this->assertEquals(['test.name'], $builder->getQueryPart('select'));

        // build が終われば getDelayedParams はやはり空配列を返すのみ
        $this->assertEquals([], $builder->getDelayedParams());
    }

    /**
     * @dataProvider provideQueryBuilder
     * @param SubqueryBuilder $builder
     */
    function test_subquery($builder)
    {
        $builder->column('test');
        $builder->lazy('select', ['parent_id' => 'id']);

        // lazy_columns が設定されているはず
        $this->assertEquals(['parent_id' => 'id'], self::forcedRead($builder, 'lazyColumns'));
        // builder のfetch 呼び出しは builder を返すはず
        $this->assertInstanceOf(get_class($builder), $builder->array());
        // connection 経由なら普通に結果を返すはず
        $this->assertIsArray($builder->getDatabase()->fetchArray($builder));
    }

    /**
     * @dataProvider provideQueryBuilder
     * @param SubqueryBuilder $builder
     */
    function test_subquery_misc($builder)
    {
        // 本来連続して呼ぶフローはなく、テスト時に変更が蓄積されていってしまうので clone する
        $clone = function (SubqueryBuilder $builder) {
            return clone $builder;
        };

        // test2.name2 をすべて 'a' にしておく
        $builder->getDatabase()->update('test2', ['name2' => 'a'], []);
        $parents = [['name1' => 'a']];

        $builder->lazy('select', ['name2' => 'name1'])->column('test2')->array();

        $row = $clone($builder->limit(3))->subquery($parents, 'sub');
        $this->assertCount(3, $row[0]['sub']);

        $row = $clone($builder->limit(5))->subquery($parents, 'sub');
        $this->assertCount(5, $row[0]['sub']);

        $row = $clone($builder->limit(null))->subquery($parents, 'sub');
        $this->assertCount(20, $row[0]['sub']);

        $builder->lazy('fetch', ['name2' => 'name1'])->column('test2')->array();

        $row = $clone($builder->limit(3))->subquery($parents, 'sub');
        $this->assertCount(3, $row[0]['sub']);

        $row = $clone($builder->limit(5))->subquery($parents, 'sub');
        $this->assertCount(5, $row[0]['sub']);

        $row = $clone($builder->limit(null))->subquery($parents, 'sub');
        $this->assertCount(20, $row[0]['sub']);

        // 親に比較行が存在しないなら例外のはず
        $this->assertException(new \OutOfBoundsException("is undefined at parent row"), L($builder)->subquery([[]], 's'));
    }

    /**
     * @dataProvider provideQueryBuilder
     * @param SubqueryBuilder $builder
     */
    function test_subquery_select_where($builder)
    {
        $builder->setLazyClonable(false); // lazy化した際のsqlが欲しいので敢えて clone しない

        $builder->column('test1');

        $target = clone $builder;
        $target->lazy('select', 'id')->array()->subquery([['id' => 2], ['id' => 3]], 'sub');
        $this->assertContains("id IN ('2', '3')", "$target");
        $this->assertEquals([], $target->getParams());

        $target = clone $builder;
        $target->lazy('select', ['id', 'name1'])->array()->subquery([['id' => 2, 'name1' => "b"]], 'sub');
        $this->assertContains("(id = '2' AND name1 = 'b')", "$target");
        $this->assertEquals([], $target->getParams());

        $target = clone $builder;
        $target->where('id <> ?')->lazy('select', ['id', 'name1'])->array([-1])->subquery([['id' => 2, 'name1' => "b"]], 'sub');
        $this->assertContains("(id <> ?)", "$target");
        $this->assertContains("(id = '2' AND name1 = 'b')", "$target");
        $this->assertEquals([-1], $target->getParams());
    }

    /**
     * @dataProvider provideQueryBuilder
     * @param SubqueryBuilder $builder
     */
    function test_subquery_fetch_where($builder)
    {
        $builder->setLazyClonable(false); // lazy化した際のsqlが欲しいので敢えて clone しない

        $builder->column('test1');

        $target = clone $builder;
        $target->lazy('fetch', 'id')->array()->subquery([['id' => 2]], 'sub');
        $this->assertContains("id = :id", "$target");

        $target = clone $builder;
        $target->lazy('fetch', ['id', 'name1'])->array()->subquery([['id' => 2, 'name1' => "b"]], 'sub');
        $this->assertContains("(id = :id) AND (name1 = :name1)", "$target");

        $target = clone $builder;
        $target->where('id <> ?')->lazy('fetch', ['id', 'name1'])->array([-1])->subquery([['id' => 3, 'name1' => "c"]], 'sub');
        $this->assertContains("(id <> ?)", "$target");
        $this->assertEquals([-1], $target->getParams());
    }

    /**
     * @dataProvider provideQueryBuilder
     * @param SubqueryBuilder $builder
     */
    function test_subquery_batch($builder)
    {
        $builder->column('test1');
        $parents = $builder->lazy('batch', 'id')->array()->subquery([['id' => 2], ['id' => 3]], 'sub');
        $this->assertInstanceOf(\Generator::class, $parents[0]['sub']);
        $this->assertInstanceOf(\Generator::class, $parents[1]['sub']);
        $this->assertEquals([
            ['id' => '2', 'name1' => 'b']
        ], iterator_to_array($parents[0]['sub']));
        $this->assertEquals([
            ['id' => '3', 'name1' => 'c']
        ], iterator_to_array($parents[1]['sub']));
    }

    /**
     * @dataProvider provideQueryBuilder
     * @param SubqueryBuilder $builder
     */
    function test_subquery_yield($builder)
    {
        $builder->column('test1');
        $parents = $builder->lazy('yield', 'id')->array()->subquery([['id' => 2], ['id' => 3]], 'sub');
        $this->assertInstanceOf(\Generator::class, $parents[0]['sub']);
        $this->assertInstanceOf(\Generator::class, $parents[1]['sub']);
        $this->assertEquals([
            ['id' => '2', 'name1' => 'b']
        ], iterator_to_array($parents[0]['sub']));
        $this->assertEquals([
            ['id' => '3', 'name1' => 'c']
        ], iterator_to_array($parents[1]['sub']));
    }

    /**
     * @dataProvider provideQueryBuilder
     * @param SubqueryBuilder $builder
     */
    function test_subquery_limit($builder)
    {
        $builder->column('multiprimary');

        $expected = [
            [
                'mainid' => '1',
                'subid'  => '3',
                'name'   => 'c',
            ],
            [
                'mainid' => '1',
                'subid'  => '4',
                'name'   => 'd',
            ],
        ];

        // 中間 limit が効くはず
        $result = $builder->lazy('select', 'mainid')->array()->limit(2, 2)->subquery([['mainid' => 1]], 'sub');
        $this->assertEquals($expected, $result[0]['sub']);

        // 中間 limit が効くはず
        $result = $builder->lazy('fetch', 'mainid')->array()->limit(2, 2)->subquery([['mainid' => 1]], 'sub');
        $this->assertEquals($expected, $result[0]['sub']);
    }

    /**
     * @dataProvider provideQueryBuilder
     * @param SubqueryBuilder $builder
     */
    function test_subquery_parent($builder)
    {
        $database = $builder->getDatabase();
        $database->insert('foreign_p', ['id' => 1, 'name' => 'name1']);
        $database->insert('foreign_c1', ['id' => 1, 'seq' => 10, 'name' => 'cname11']);

        $builder->column([
            'foreign_c1' => [
                'seq',
                '..name',
                'parent_name' => '..name',
            ]
        ])->lazy('select', null)->setLazyColumnFrom('foreign_p');

        // array
        $result = $builder->array()->subquery($database->selectArray('foreign_p'), 'sub');
        $this->assertEquals([
            'id'   => 1,
            'name' => 'name1',
            'sub'  => [
                [
                    'seq'         => 10,
                    'name'        => 'name1',
                    'parent_name' => 'name1',
                ],
            ],
        ], $result[0]);

        // tuple
        $result = $builder->tuple()->subquery($database->selectArray('foreign_p'), 'sub');
        $this->assertEquals([
            'id'   => 1,
            'name' => 'name1',
            'sub'  => [
                'seq'         => 10,
                'name'        => 'name1',
                'parent_name' => 'name1',
            ],
        ], $result[0]);

        $this->assertException('reference undefined parent column', L($builder)->subquery($database->selectArray('foreign_p.id, name as aaa'), 'sub'));
    }

    /**
     * @dataProvider provideQueryBuilder
     * @param SubqueryBuilder $builder
     */
    function test_inheritLockMode($builder)
    {
        $platform = $builder->getDatabase()->getPlatform();
        $readlock = trim($platform->getReadLockSQL());
        $readlock = $readlock ?: trim($platform->appendLockHint('', LockMode::PESSIMISTIC_READ));
        $writelock = trim($platform->getWriteLockSQL());
        $writelock = $writelock ?: trim($platform->appendLockHint('', LockMode::PESSIMISTIC_WRITE));

        $builder->setLazyClonable(false);
        $parent = $builder->getDatabase()->createQueryBuilder();

        // 親で lockInShare すれば伝播する
        $parent->reset()->lockInShare()->column([
            'test.*' => [
                'ddd' => $builder->reset()->column('test1')->lazy('select', 'id')->array()
            ],
        ])->limit(1)->tuple();
        $this->assertContains($readlock, "$builder");

        // 親で lockInShare しても子で明示的にしてれば伝播しない
        $parent->reset()->lockInShare()->column([
            'test.*' => [
                'ddd' => $builder->reset()->column('test1')->lazy('select', 'id')->lockForUpdate()->array()
            ],
        ])->limit(1)->tuple();
        $this->assertContains($writelock, "$builder");

        // そもそも propagateLockMode しなければ伝播しない
        // そもそも InheritLockMode しなければ伝播しない
        $parent->reset()->lockForUpdate()->column([
            'test.*' => [
                'ddd' => $builder->setPropagateLockMode(false)->reset()->column('test2')->lazy('select', 'id')->array()
            ],
        ])->limit(1)->tuple();
        $this->assertNotContains($readlock, "$builder");
        $this->assertNotContains($writelock, "$builder");
    }
}
