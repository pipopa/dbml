<?php

namespace ryunosuke\Test\dbml\Mixin;

use ryunosuke\dbml\Mixin\DebugInfoTrait;

class DebugInfoTraitTest extends \ryunosuke\Test\AbstractUnitTestCase
{
    function test___debugInfo()
        /** @noinspection PhpUndefinedFieldInspection */
    {
        $object = new DebugInfoTest();
        $object->dummy = 'dummy';
        $object->db = self::getDummyDatabase();
        $this->assertArrayHasKey('dummy', $object->__debugInfo());
        $this->assertArrayNotHasKey('db', $object->__debugInfo());
    }
}

class DebugInfoTest
{
    use DebugInfoTrait;
}
