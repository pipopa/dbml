<?php

namespace ryunosuke\Test\dbml\Entity;

use ryunosuke\dbml\Entity\Entity;

class EntityTest extends \ryunosuke\Test\AbstractUnitTestCase
{
    function test___call()
    {
        $entity = new Entity(self::getDummyDatabase());
        $entity->assign([
            'func' => function ($arg) { return strtoupper($arg); },
        ]);
        /** @noinspection PhpUndefinedMethodInspection */
        @$this->assertEquals('XXX', $entity->func('xxx'));
    }

    function test_getDatabase()
    {
        $db = self::getDummyDatabase();
        $entity = new Entity($db);
        $this->assertSame($db, $entity->getDatabase());

        $cdb = self::getDummyDatabase()->dryrun();
        $entity = new Entity($cdb);
        $this->assertSame($db, $entity->getDatabase());
    }

    function test_arrayize()
    {
        $child = new Entity(self::getDummyDatabase());
        $child->assign(['cid' => 1, 'name' => 'this is child']);
        $parent = new Entity(self::getDummyDatabase());
        $parent->assign(['pid' => 1, 'name' => 'this is parent', 'children' => [$child]]);
        $this->assertEquals([
            'pid'      => 1,
            'name'     => 'this is parent',
            'children' => [
                [
                    'cid'  => 1,
                    'name' => 'this is child',
                ],
            ],
        ], $parent->arrayize());
    }

    function test_offsetExists()
    {
        $entity = new Entity(self::getDummyDatabase());
        $entity->a = 'A';
        $this->assertTrue(isset($entity['a']));
        $this->assertFalse(isset($entity['X']));
    }

    function test_offsetGet()
    {
        $entity = new Entity(self::getDummyDatabase());
        $entity->a = 'A';
        $this->assertEquals('A', $entity['a']);
        @$this->assertEquals(null, $entity['X']);
    }

    function test_offsetSet()
    {
        $entity = new Entity(self::getDummyDatabase());
        $entity->a = 'A';
        $entity['a'] = 'AA';
        $entity['b'] = 'BB';
        $this->assertEquals('AA', $entity['a']);
        $this->assertEquals('BB', $entity['b']);
    }

    function test_offsetUnset()
    {
        $entity = new Entity(self::getDummyDatabase());
        $entity->a = 'A';
        unset($entity['a']);
        @$this->assertEquals(null, $entity['a']);
    }
}
