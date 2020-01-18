<?php

namespace ryunosuke\Test\dbml\Entity;

use ryunosuke\dbml\Entity\Entity;

class EntityTest extends \ryunosuke\Test\AbstractUnitTestCase
{
    function test___call()
    {
        $entity = new Entity();
        $entity->assign([
            'func' => function ($arg) { return strtoupper($arg); },
        ]);
        /** @noinspection PhpUndefinedMethodInspection */
        @$this->assertEquals('XXX', $entity->func('xxx'));
    }

    function test_PropertyAccess()
    {
        $entity = new Entity();
        $this->assertFalse(isset($entity['a']));
        $entity['a'] = 'A';
        $this->assertTrue(isset($entity['a']));
        $this->assertEquals('A', $entity['a']);
        unset($entity['a']);
        $this->assertFalse(isset($entity['a']));
    }

    function test_ArrayAccess()
    {
        $entity = new Entity();
        $this->assertFalse(isset($entity->a));
        $entity->a = 'A';
        $this->assertTrue(isset($entity->a));
        $this->assertEquals('A', $entity->a);
        unset($entity->a);
        $this->assertFalse(isset($entity->a));
    }

    function test_foreach()
    {
        $entity = new Entity();
        $entity->a = 'A';
        $entity->b = 'B';
        $entity->c = 'C';
        $this->assertEquals([
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
        ], iterator_to_array($entity));
    }

    function test_jsonSerialize()
    {
        $entity = new Entity();
        $entity->a = 'A';
        $entity->x = [
            (new Entity())->assign(['x' => 'X']),
            (new Entity())->assign(['y' => 'Y']),
        ];
        $this->assertEquals('{"a":"A","x":[{"x":"X"},{"y":"Y"}]}', json_encode($entity));
    }

    function test_arrayize()
    {
        $child = new Entity();
        $child->assign(['cid' => 1, 'name' => 'this is child']);
        $parent = new Entity();
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
}
