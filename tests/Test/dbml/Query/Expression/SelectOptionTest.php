<?php

namespace ryunosuke\Test\dbml\Query\Expression;

use ryunosuke\dbml\Query\Expression\SelectOption;

class SelectOptionTest extends \ryunosuke\Test\AbstractUnitTestCase
{
    function test___callStatic()
    {
        $actual = SelectOption::DISTINCT();
        $this->assertEquals(new SelectOption('DISTINCT'), $actual);
    }

    function test()
    {
        $so = new SelectOption('hogera');
        $this->assertEquals('hogera', $so);
    }
}
