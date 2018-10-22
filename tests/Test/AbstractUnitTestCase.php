<?php

namespace ryunosuke\Test;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\View;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\TestCase;
use function ryunosuke\dbml\class_shorten;

abstract class AbstractUnitTestCase extends TestCase
{
    /** @var Connection[] */
    private static $connections = [];

    /** @var Database 接続が必要なときに使う汎用インスタンス */
    protected static $database;

    public static function createConnection($dbms, $init = false)
    {
        $getconst = function ($cname) {
            if (!defined($cname)) {
                throw new SkippedTestError("$cname is not defined.");
            }
            return constant($cname);
        };

        $prefix = strtoupper($dbms);
        $config = ['url' => $getconst("{$prefix}_URL")];

        if ($init) {
            $mparam = DriverManager::getConnection($config)->getParams();
            $dbname = isset($mparam['dbname']) ? $mparam['dbname'] : (isset($mparam['path']) ? $mparam['path'] : '');
            unset($mparam['url'], $mparam['dbname'], $mparam['path']);
            DriverManager::getConnection($mparam)->getSchemaManager()->dropAndCreateDatabase($dbname);
        }

        $connection = DriverManager::getConnection($config + [
                'wrapperClass'  => 'ryunosuke\\Test\\TestConnection',
                'driverOptions' => [
                    // for mysql
                    \PDO::MYSQL_ATTR_LOCAL_INFILE => true,
                ],
            ]
        );
        $connection->connect();

        $init_command = trim($getconst("{$prefix}_INITCOMMAND"));
        if ($init_command) {
            $connection->exec($init_command);
        }

        return $connection;
    }

    public static function provideConnection()
    {
        $getconst = function ($cname) {
            if (!defined($cname)) {
                throw new SkippedTestError("$cname is not defined.");
            }
            return constant($cname);
        };

        $rdbms = array_map('trim', explode(',', getenv('RDBMS') ?: $getconst('RDBMS')));
        foreach ($rdbms as $dbms) {
            if (isset(self::$connections[$dbms])) {
                continue;
            }

            try {
                $connection = self::createConnection($dbms, true);

                self::createTables(
                    $connection,
                    [
                        new Table('test',
                            [
                                new Column('id', Type::getType('integer'), ['autoincrement' => true]),
                                new Column('name', Type::getType('string'), ['length' => 32, 'default' => '']),
                                new Column('data', Type::getType('string'), ['length' => 32, 'default' => '']),
                            ],
                            [new Index('PRIMARY', ['id'], true, true)]
                        ),
                        new Table('test1',
                            [
                                new Column('id', Type::getType('integer'), ['autoincrement' => true]),
                                new Column('name1', Type::getType('string'), ['length' => 32]),
                            ],
                            [new Index('PRIMARY', ['id'], true, true), new Index('SECONDARY1', ['id'])]
                        ),
                        new Table('test2',
                            [
                                new Column('id', Type::getType('integer'), ['autoincrement' => true]),
                                new Column('name2', Type::getType('string'), ['length' => 32]),
                            ],
                            [new Index('PRIMARY', ['id'], true, true), new Index('SECONDARY2', ['id'])]
                        ),
                        new Table('auto',
                            [
                                new Column('id', Type::getType('integer'), ['autoincrement' => true]),
                                new Column('name', Type::getType('string'), ['length' => 32]),
                            ],
                            [new Index('PRIMARY', ['id'], true, true)],
                            [],
                            0,
                            // Doctrine で Sqlite の「本当の意味」での AUTOINCREMENT な列を作成することは出来ない(ので、置換する)
                            [
                                'create_sql' => [
                                    'sqlite' => 'CREATE TABLE auto (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(32) NOT NULL)',
                                ]
                            ]
                        ),
                        new Table('noauto',
                            [
                                new Column('id', Type::getType('string'), ['length' => 32]),
                                new Column('name', Type::getType('string'), ['length' => 32]),
                            ],
                            [new Index('PRIMARY', ['id'], true, true)]
                        ),
                        new Table('paging',
                            [
                                new Column('id', Type::getType('integer'), ['autoincrement' => true]),
                                new Column('name', Type::getType('string'), ['length' => 32]),
                            ],
                            [new Index('PRIMARY', ['id'], true, true)]
                        ),
                        new Table('aggregate',
                            [
                                new Column('id', Type::getType('integer'), ['autoincrement' => true]),
                                new Column('name', Type::getType('string'), ['length' => 32]),
                                new Column('group_id1', Type::getType('integer')),
                                new Column('group_id2', Type::getType('integer')),
                            ],
                            [new Index('PRIMARY', ['id'], true, true)]
                        ),
                        new Table('noprimary',
                            [
                                new Column('id', Type::getType('integer')),
                            ]
                        ),
                        new Table('nullable',
                            [
                                new Column('id', Type::getType('integer'), ['autoincrement' => true]),
                                new Column('name', Type::getType('string'), ['length' => 32, 'notnull' => false]),
                                new Column('cint', Type::getType('integer'), ['notnull' => false]),
                                new Column('cfloat', Type::getType('float'), ['notnull' => false]),
                                new Column('cdecimal', Type::getType('decimal'), ['notnull' => false, 'scale' => 2, 'precision' => 3]),
                            ],
                            [new Index('PRIMARY', ['id'], true, true)]
                        ),
                        new Table('multiprimary',
                            [
                                new Column('mainid', Type::getType('integer')),
                                new Column('subid', Type::getType('integer')),
                                new Column('name', Type::getType('string'), ['length' => 32, 'default' => '']),
                            ],
                            [new Index('PRIMARY', ['mainid', 'subid'], true, true)]
                        ),
                        new Table('multiunique',
                            [
                                new Column('id', Type::getType('integer')),
                                new Column('uc_s', Type::getType('string')),
                                new Column('uc_i', Type::getType('integer')),
                                new Column('uc1', Type::getType('string')),
                                new Column('uc2', Type::getType('integer')),
                            ],
                            [
                                new Index('PRIMARY', ['id'], true, true),
                                new Index('uk1', ['uc_s'], true),
                                new Index('uk2', ['uc_i'], true),
                                new Index('uk3', ['uc1', 'uc2'], true),
                            ]
                        ),
                        new Table('misctype',
                            [
                                new Column('id', Type::getType('integer'), ['autoincrement' => true]),
                                new Column('pid', Type::getType('integer'), ['notnull' => false]),
                                new Column('cint', Type::getType('integer')),
                                new Column('cfloat', Type::getType('float')),
                                new Column('cdecimal', Type::getType('decimal')),
                                new Column('cdate', Type::getType('date')),
                                new Column('cdatetime', Type::getType('datetime')),
                                new Column('cstring', Type::getType('string')),
                                new Column('ctext', Type::getType('text')),
                                new Column('cbinary', Type::getType('binary'), ['notnull' => false]),
                                new Column('cblob', Type::getType('blob'), ['notnull' => false]),
                                new Column('carray', Type::getType('simple_array'), ['notnull' => false]),
                            ],
                            [new Index('PRIMARY', ['id'], true, true), new Index('IDX_MISCTYPE1', ['pid'], true)]
                        ),
                        new Table('misctype_child',
                            [
                                new Column('id', Type::getType('integer'), ['autoincrement' => true]),
                                new Column('cid', Type::getType('integer')),
                                new Column('cint', Type::getType('integer')),
                                new Column('cfloat', Type::getType('float')),
                                new Column('cdecimal', Type::getType('decimal')),
                                new Column('cdate', Type::getType('date')),
                                new Column('cdatetime', Type::getType('datetime')),
                                new Column('cstring', Type::getType('string')),
                                new Column('ctext', Type::getType('text')),
                                new Column('cbinary', Type::getType('binary'), ['notnull' => false]),
                                new Column('cblob', Type::getType('blob'), ['notnull' => false]),
                            ],
                            [new Index('PRIMARY', ['id'], true, true)]
                        ),
                        new Table('foreign_p',
                            [
                                new Column('id', Type::getType('integer')),
                                new Column('name', Type::getType('string'), ['length' => 32, 'default' => '']),
                            ],
                            [new Index('PRIMARY', ['id'], true, true)]
                        ),
                        new Table('foreign_c1',
                            [
                                new Column('id', Type::getType('integer')),
                                new Column('seq', Type::getType('integer')),
                                new Column('name', Type::getType('string'), ['length' => 32, 'default' => '']),
                            ],
                            [new Index('PRIMARY', ['id', 'seq'], true, true)],
                            [new ForeignKeyConstraint(['id'], 'foreign_p', ['id'], 'fk_parentchild1')]
                        ),
                        new Table('foreign_c2',
                            [
                                new Column('cid', Type::getType('integer')),
                                new Column('seq', Type::getType('integer')),
                                new Column('name', Type::getType('string'), ['length' => 32, 'default' => '']),
                            ],
                            [new Index('PRIMARY', ['cid', 'seq'], true, true)],
                            [new ForeignKeyConstraint(['cid'], 'foreign_p', ['id'], 'fk_parentchild2')]
                        ),
                        new Table('foreign_d1',
                            [
                                new Column('id', Type::getType('integer')),
                                new Column('d2_id', Type::getType('integer')),
                                new Column('name', Type::getType('string'), ['length' => 32, 'default' => '']),
                            ],
                            [new Index('PRIMARY', ['id'], true, true)]
                        ),
                        new Table('foreign_d2',
                            [
                                new Column('id', Type::getType('integer')),
                                new Column('name', Type::getType('string'), ['length' => 32, 'default' => '']),
                            ],
                            [new Index('PRIMARY', ['id'], true, true)]
                        ),
                        function (Connection $connection) {
                            $fk1 = new ForeignKeyConstraint(['d2_id'], 'foreign_d2', ['id'], 'fk_dd12');
                            $fk2 = new ForeignKeyConstraint(['id'], 'foreign_d1', ['id'], 'fk_dd21');
                            $connection->getSchemaManager()->createForeignKey($fk1, 'foreign_d1');
                            $connection->getSchemaManager()->createForeignKey($fk2, 'foreign_d2');
                        },
                        new Table('foreign_s',
                            [
                                new Column('id', Type::getType('integer')),
                                new Column('name', Type::getType('string'), ['length' => 32, 'default' => '']),
                            ],
                            [new Index('PRIMARY', ['id'], true, true)]
                        ),
                        new Table('foreign_sc',
                            [
                                new Column('id', Type::getType('integer')),
                                new Column('name', Type::getType('string'), ['length' => 32, 'default' => '']),
                                new Column('s_id1', Type::getType('integer')),
                                new Column('s_id2', Type::getType('integer'), ['notnull' => false]),
                            ],
                            [new Index('PRIMARY', ['id'], true, true)],
                            [
                                new ForeignKeyConstraint(['s_id1'], 'foreign_s', ['id'], 'fk_sc1'),
                                new ForeignKeyConstraint(['s_id2'], 'foreign_s', ['id'], 'fk_sc2'),
                            ]
                        ),
                        new Table('g_ancestor',
                            [
                                new Column('ancestor_id', Type::getType('integer'), ['autoincrement' => true]),
                                new Column('ancestor_name', Type::getType('string'), ['length' => 32]),
                            ],
                            [new Index('PRIMARY', ['ancestor_id'], true, true)]
                        ),
                        new Table('g_parent',
                            [
                                new Column('parent_id', Type::getType('integer'), ['autoincrement' => true]),
                                new Column('parent_name', Type::getType('string'), ['length' => 32]),
                                new Column('ancestor_id', Type::getType('integer'), []),
                            ],
                            [new Index('PRIMARY', ['parent_id'], true, true)],
                            [new ForeignKeyConstraint(['ancestor_id'], 'g_ancestor', ['ancestor_id'], 'fkey_generation1')]
                        ),
                        new Table('g_child',
                            [
                                new Column('child_id', Type::getType('integer'), ['autoincrement' => true]),
                                new Column('child_name', Type::getType('string'), ['length' => 32]),
                                new Column('parent_id', Type::getType('integer'), []),
                            ],
                            [new Index('PRIMARY', ['child_id'], true, true)],
                            [new ForeignKeyConstraint(['parent_id'], 'g_parent', ['parent_id'], 'fkey_generation2')]
                        ),
                        new Table('horizontal1',
                            [
                                new Column('id', Type::getType('integer'), ['autoincrement' => true]),
                                new Column('name', Type::getType('string')),
                            ],
                            [new Index('PRIMARY', ['id'], true, true)]
                        ),
                        new Table('horizontal2',
                            [
                                new Column('id', Type::getType('integer')),
                                new Column('summary', Type::getType('string')),
                            ],
                            [new Index('PRIMARY', ['id'], true, true)],
                            [new ForeignKeyConstraint(['id'], 'horizontal1', ['id'], 'fkey_horizontal')]
                        ),
                        new Table('t_article',
                            [
                                new Column('article_id', Type::getType('integer')),
                                new Column('title', Type::getType('string')),
                                new Column('checks', Type::getType('string')),
                            ],
                            [
                                new Index('PRIMARY', ['article_id'], true, true),
                                new Index('secondary', ['title'])
                            ]
                        ),
                        new Table('t_comment',
                            [
                                new Column('comment_id', Type::getType('integer'), ['autoincrement' => true]),
                                new Column('article_id', Type::getType('integer')),
                                new Column('comment', Type::getType('text')),
                            ],
                            [new Index('PRIMARY', ['comment_id'], true, true)],
                            [
                                new ForeignKeyConstraint(['article_id'], 't_article', ['article_id'], 'fk_articlecomment', [
                                    'onUpdate' => 'CASCADE',
                                    'onDelete' => 'CASCADE'
                                ])
                            ]
                        ),
                        new View('v_blog', '
                            SELECT
                              A.article_id,
                              A.title,
                              C.comment_id,
                              C.comment
                            FROM t_article A
                            JOIN t_comment C ON A.article_id = C.article_id
                        '),
                    ]
                );
            }
            catch (\Exception $ex) {
                echo $ex->getMessage(), PHP_EOL;
                $connection = false;
            }

            self::$connections[$dbms] = $connection;
        }

        return array_map(function ($v) {
            return [$v];
        }, array_filter(self::$connections));
    }

    public static function provideDatabase()
    {
        static $dbs = [];
        return $dbs ?: $dbs = array_map(function ($v) {
            return [
                new Database($v[0], [
                    'entityMapper'  => function ($tablename) {
                        if ($tablename === 't_article') {
                            return \ryunosuke\Test\Entity\Article::class;
                        }
                        if ($tablename === 't_comment') {
                            return \ryunosuke\Test\Entity\Comment::class;
                        }
                    },
                    'gatewayMapper' => function ($tablename) {
                        if ($tablename === 't_article') {
                            return \ryunosuke\Test\Gateway\Article::class;
                        }
                        if ($tablename === 't_comment') {
                            return \ryunosuke\Test\Gateway\Comment::class;
                        }
                        return \ryunosuke\Test\Gateway\TableGateway::class;
                    },
                    'offsetGetFind' => false, // this is compatible setting
                ])
            ];
        }, self::provideConnection());
    }


    public static function getConnections()
    {
        return array_filter(self::$connections);
    }

    public static function getDummyDatabase()
    {
        if (self::$database === null) {
            self::$database = new Database(DriverManager::getConnection(['url' => 'sqlite:///:memory:']));
        }
        return self::$database;
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::readyRecord();
    }

    public static function readyRecord()
    {
        foreach (self::getConnections() as $connection) {
            /** @var TestConnection $connection */
            $connection->clean(function ($connection) {
                $db = new Database($connection);
                $db->truncate('test');
                $db->truncate('test1');
                $db->truncate('test2');
                $db->truncate('noauto');
                $db->truncate('paging');
                $db->truncate('aggregate');
                $db->truncate('noprimary');
                $db->truncate('multiprimary');
                $db->truncate('misctype');
                $db->truncate('misctype_child');
                $db->delete('foreign_c1');
                $db->delete('foreign_c2');
                $db->delete('foreign_p');
                $db->delete('g_child');
                $db->delete('g_parent');
                $db->delete('g_ancestor');
                $db->delete('t_comment');
                $db->delete('t_article');
                $db->truncate('t_comment'); // id のリセット

                $db->transact(function (Database $db) {
                    for ($i = 0, $char = 'a'; $i < 10; $i++) {
                        $db->insert('test', [
                            'name' => $char++,
                        ]);
                    }
                    for ($i = 0, $char = 'a'; $i < 10; $i++) {
                        $db->insert('test1', [
                            'name1' => $char++
                        ]);
                    }
                    for ($i = 0, $char = 'A'; $i < 20; $i++) {
                        $db->insert('test2', [
                            'name2' => $char++
                        ]);
                    }
                    for ($i = 0, $char = 'a'; $i < 100; $i++) {
                        $db->insert('paging', [
                            'name' => $char++
                        ]);
                    }
                    for ($i = 0, $char = 'a'; $i < 10; $i++) {
                        $db->insert('aggregate', [
                            'name'      => $char++,
                            'group_id1' => floor($i / 2) + 1,
                            'group_id2' => (floor($i / 5) + 1) * 10,
                        ]);
                    }
                    for ($i = 1, $char = 'a'; $i <= 10; $i++) {
                        $db->insert('multiprimary', [
                            'mainid' => ceil($i / 5),
                            'subid'  => $i,
                            'name'   => $char++,
                        ]);
                    }
                    $db->insert('t_article', [
                        'article_id' => 1,
                        'title'      => 'タイトルです',
                        'checks'     => '',
                    ]);
                    $db->insert('t_article', [
                        'article_id' => 2,
                        'title'      => 'コメントのない記事です',
                        'checks'     => '',
                    ]);
                    for ($i = 1; $i <= 3; $i++) {
                        $db->insert('t_comment', [
                            'article_id' => 1,
                            'comment'    => "コメント{$i}です",
                        ]);
                    }
                });
            });
        }
    }

    public function tearDown()
    {
        parent::tearDown();

        self::readyRecord();
    }

    public static function createTables(Connection $connection, $tableorviews)
    {
        /** @var AbstractAsset $tableorview */

        // 外部キーのつらみがあるので逆順で drop
        foreach (array_reverse($tableorviews) as $tableorview) {
            if ($tableorview instanceof \Closure) {
                continue;
            }
            $method = 'drop' . class_shorten($tableorview);
            $connection->getSchemaManager()->tryMethod($method, $tableorview->getQuotedName($connection->getDatabasePlatform()));
        }
        // そのあと create
        foreach ($tableorviews as $tableorview) {
            if ($tableorview instanceof Table) {
                /** @var Table $tableorview */
                $options = $tableorview->getOptions();
                $platname = $connection->getDatabasePlatform()->getName();
                if (isset($options['create_sql'][$platname])) {
                    $sql = $options['create_sql'][$platname];
                    $connection->executeUpdate($sql);
                    continue;
                }
            }
            if ($tableorview instanceof \Closure) {
                $tableorview($connection);
                continue;
            }
            $method = 'create' . class_shorten($tableorview);
            $connection->getSchemaManager()->$method($tableorview);
        }
    }

    public static function supportSyntax(Database $database, $syntax)
    {
        try {
            $database->executeQuery("$syntax");
            return true;
        }
        catch (\Exception $e) {
            return false;
        }
    }

    public static function assertException($e, $callback)
    {
        if (is_string($e)) {
            $e = new \Exception($e);
        }

        $callback = self::forcedCallize($callback);

        try {
            $callback(...array_slice(func_get_args(), 2));
        }
        catch (Error $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw $ex;
        }
        catch (\Exception $ex) {
            self::assertInstanceOf(get_class($e), $ex);
            self::assertEquals($e->getCode(), $ex->getCode());
            if (strlen($e->getMessage()) > 0) {
                self::assertContains($e->getMessage(), $ex->getMessage());
            }
            return;
        }
        self::fail(get_class($e) . ' is not thrown.');
    }

    public static function assertStringIgnoreBreak($expected, $actual, $message = '')
    {
        $expected = preg_replace('/[\r\n]/', ' ', trim($expected, "\r\n"));
        $actual = preg_replace('/[\r\n]/', ' ', trim($actual, "\r\n"));
        self::assertEquals($expected, $actual);
    }

    public static function removeDirectory($dirname)
    {
        if (!file_exists($dirname)) {
            return;
        }

        if (is_file($dirname)) {
            unlink($dirname);
        }
        else {
            $names = scandir($dirname);
            foreach ($names as $fname) {
                if ($fname !== '.' && $fname !== '..') {
                    self::removeDirectory($dirname . '/' . $fname);
                }
            }

            rmdir($dirname);
        }
    }

    public static function forcedCallize($callable, $method = null)
    {
        if (func_num_args() == 2) {
            $callable = func_get_args();
        }

        if (is_string($callable) && strpos($callable, '::') !== false) {
            $parts = explode('::', $callable);
            $method = new \ReflectionMethod($parts[0], $parts[1]);
            if (!$method->isPublic() && $method->isStatic()) {
                $method->setAccessible(true);
                return function () use ($method) {
                    return $method->invokeArgs(null, func_get_args());
                };
            }
        }

        if (is_array($callable) && count($callable) === 2) {
            try {
                $method = new \ReflectionMethod($callable[0], $callable[1]);
                if (!$method->isPublic()) {
                    $method->setAccessible(true);
                    return function () use ($callable, $method) {
                        return $method->invokeArgs($method->isStatic() ? null : $callable[0], func_get_args());
                    };
                }
            }
            catch (\ReflectionException $ex) {
                // __call を考慮するとどうしようもない
            }
        }

        return $callable;
    }

    public static function forcedRead($object, $property)
    {
        $refprop = null;
        $class = get_class($object);
        while (true) {
            try {
                $refprop = new \ReflectionProperty($class, $property);
                break;
            }
            catch (\ReflectionException $ex) {
                $class = get_parent_class($class);
                if ($class == false) {
                    throw $ex;
                }
            }
        }
        $refprop->setAccessible(true);
        return $refprop->getValue($object);
    }

    public static function forcedWrite($object, $property, $value)
    {
        $refprop = null;
        $class = get_class($object);
        while (true) {
            try {
                $refprop = new \ReflectionProperty($class, $property);
                break;
            }
            catch (\ReflectionException $ex) {
                $class = get_parent_class($class);
                if ($class == false) {
                    throw $ex;
                }
            }
        }
        $refprop->setAccessible(true);
        $current = $refprop->getValue($object);
        $refprop->setValue($object, $value);
        return $current;
    }
}
