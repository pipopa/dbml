<?php

namespace ryunosuke\Test\dbml\Transaction;

use ryunosuke\dbml\Transaction\Logger;

class LoggerTest extends \ryunosuke\Test\AbstractUnitTestCase
{
    function test_all()
    {
        $logger = new Logger([
            'destination' => new \ArrayObject(),
            'lockmode'    => false,
            'prefix'      => null,
            'format'      => null,
            'method'      => 'escape',
        ]);

        // simple
        $logger->log('select ?', [9]);
        $logger->log('"COMMIT"', []);

        // prefix
        $logger->setOption('prefix', '[prefix]');
        $logger->log('select ?', [9]);
        $logger->setOption('prefix', function () { return '[closure]'; });
        $logger->log('select ?', [9]);

        // format
        $logger->setOption('format', function ($sql) { return preg_replace('#\R#ui', ' ', $sql); });
        $logger->log("select\n?\nfrom\ntable", [9]);

        // method
        $logger->setOption('method', 'keyvalue');
        $logger->log('select ?', [9]);
        $logger->setOption('method', function ($sql, $params) { return serialize([$sql, $params]); });
        $logger->log('select ?', [9]);

        $this->assertEquals([
            'select 9',
            'COMMIT',
            '[prefix]select 9',
            '[closure]select 9',
            '[closure]select 9 from table',
            '[closure]select ?:[9]',
            '[closure]a:2:{i:0;s:8:"select ?";i:1;a:1:{i:0;i:9;}}',
        ], $logger->getLog());
    }

    function test___construct()
    {
        $ao = new \ArrayObject();
        $logger = new Logger($ao, [
            'prefix' => 'pre-pre-pre-',
        ]);
        // destination だけは第1引数でダイレクトに設定できる
        $this->assertSame($ao, $logger->getOption('destination'));
        // その場合オプション配列は第2引数になる
        $this->assertSame('pre-pre-pre-', $logger->getOption('prefix'));
    }

    function test_escape()
    {
        $logger = new Logger([
            'destination' => new \ArrayObject(),
            'method'      => 'escape',
        ]);

        $logger->log('select ?', [9]);
        $logger->log('select ?', [null]);
        $logger->log('select :name', ['name' => 'hoge']);
        $logger->log('select :name1, :name2', []);
        $logger->log('select :evil', ['evil' => '\\a"b\'']);

        $this->assertEquals([
            'select 9',
            'select NULL',
            'select "hoge"',
            'select :name1, :name2',
            'select "\\\\a\"b\\\'"'
        ], $logger->getLog());
    }

    function test_file()
    {
        $logs = sys_get_temp_dir() . '/query.log';
        @unlink($logs);
        $logger = new Logger([
            'destination' => $logs,
            'lockmode'    => true,
            'method'      => 'escape',
        ]);

        $logger->log('select ?', [9]);
        $logger->log('select ?', [9]);

        $this->assertStringEqualsFile($logs, "select 9\nselect 9\n");
    }

    function test_resource()
    {
        $resource = fopen(sys_get_temp_dir() . '/query.log', 'w+');
        $logger = new Logger([
            'destination' => $resource,
            'lockmode'    => true,
            'method'      => 'escape',
        ]);

        $logger->log('select ?', [9]);

        rewind($resource);
        $this->assertEquals("select 9\n", stream_get_contents($resource));
    }

    function test_closure()
    {
        $logs = [];
        $logger = new Logger([
            'destination' => function ($log) use (&$logs) { $logs[] = $log; },
            'lockmode'    => true,
            'method'      => 'escape',
        ]);

        $logger->log('select ?', [9]);

        $this->assertEquals(['select 9'], $logs);
    }

    function test_nosupport()
    {
        $logger = new Logger([
            'destination' => new \stdClass(),
            'lockmode'    => true,
            'method'      => 'escape',
        ]);

        $logger->log('select ?', [9]);
        $this->assertEmpty($logger->getLog());
    }
}
