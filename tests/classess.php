<?php

namespace {

    use ryunosuke\dbml\Database;
    use ryunosuke\dbml\Entity\Entity;
    use ryunosuke\dbml\Gateway\TableGateway;
    use ryunosuke\dbml\Generator\AbstractGenerator;
    use ryunosuke\dbml\Generator\Yielder;
    use ryunosuke\dbml\Metadata\CompatiblePlatform;
    use ryunosuke\dbml\Metadata\Schema;
    use ryunosuke\dbml\Query\Expression\Expression;
    use ryunosuke\dbml\Query\Expression\Operator;
    use ryunosuke\dbml\Query\Pagination\Paginator;
    use ryunosuke\dbml\Query\Pagination\Sequencer;
    use ryunosuke\dbml\Query\QueryBuilder;
    use ryunosuke\dbml\Query\SubqueryBuilder;
    use ryunosuke\dbml\Transaction\Transaction;

    /**
     * @param $object
     * @return Database|Schema|QueryBuilder|SubqueryBuilder|TableGateway|Paginator|Sequencer|CompatiblePlatform|Operator|Expression|Entity|Transaction|Yielder|AbstractGenerator
     */
    function L($object)
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return new \LazyCaller($object);
    }

    /**
     * assertException のために遅延実行するラッパークラス
     */
    class LazyCaller
    {
        private $object;

        public function __construct($object)
        {
            $this->object = $object;
        }

        public function __get($name)
        {
            return function () use ($name) {
                return $this->object->$name;
            };
        }

        public function __call($name, $args)
        {
            return function () use ($name, $args) {
                $params = [];
                for ($i = 0, $l = count($args); $i < $l; $i++) {
                    $params[$i] = &$args[$i];
                }
                return call_user_func_array([$this->object, $name], $params);
            };
        }
    }
}

namespace ryunosuke\Test {

    use Doctrine\DBAL\Connection;
    use Doctrine\DBAL\Platforms\SQLServerPlatform;

    /**
     * テスト用 Connection
     *
     * 行を変更したら戻してくれる。
     */
    class TestConnection extends Connection
    {
        private $is_dirty = true;

        public function clean($callback)
        {
            if ($this->is_dirty) {
                $callback($this);
                $this->is_dirty = false;
            }
        }

        public function executeUpdate($query, array $params = [], array $types = [])
        {
            $this->is_dirty = true;
            return parent::executeUpdate($query, $params, $types);
        }
    }

    /**
     * テスト用 Database
     *
     * 諸アノテーションと SQLServer 用の小細工オーバーライド
     *
     * @property \ryunosuke\Test\Gateway\TableGateway $aggregate
     * @method   \ryunosuke\Test\Gateway\TableGateway aggregate($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $auto
     * @method   \ryunosuke\Test\Gateway\TableGateway auto($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $foreign_c1
     * @method   \ryunosuke\Test\Gateway\TableGateway foreign_c1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $foreign_c2
     * @method   \ryunosuke\Test\Gateway\TableGateway foreign_c2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $foreign_d1
     * @method   \ryunosuke\Test\Gateway\TableGateway foreign_d1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $foreign_d2
     * @method   \ryunosuke\Test\Gateway\TableGateway foreign_d2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $foreign_p
     * @method   \ryunosuke\Test\Gateway\TableGateway foreign_p($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $foreign_s
     * @method   \ryunosuke\Test\Gateway\TableGateway foreign_s($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $foreign_sc
     * @method   \ryunosuke\Test\Gateway\TableGateway foreign_sc($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $g_ancestor
     * @method   \ryunosuke\Test\Gateway\TableGateway g_ancestor($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $g_child
     * @method   \ryunosuke\Test\Gateway\TableGateway g_child($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $g_parent
     * @method   \ryunosuke\Test\Gateway\TableGateway g_parent($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $horizontal1
     * @method   \ryunosuke\Test\Gateway\TableGateway horizontal1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $horizontal2
     * @method   \ryunosuke\Test\Gateway\TableGateway horizontal2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $misctype
     * @method   \ryunosuke\Test\Gateway\TableGateway misctype($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $misctype_child
     * @method   \ryunosuke\Test\Gateway\TableGateway misctype_child($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $multiprimary
     * @method   \ryunosuke\Test\Gateway\TableGateway multiprimary($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $multiunique
     * @method   \ryunosuke\Test\Gateway\TableGateway multiunique($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $noauto
     * @method   \ryunosuke\Test\Gateway\TableGateway noauto($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $noprimary
     * @method   \ryunosuke\Test\Gateway\TableGateway noprimary($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $nullable
     * @method   \ryunosuke\Test\Gateway\TableGateway nullable($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $paging
     * @method   \ryunosuke\Test\Gateway\TableGateway paging($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\Article      $t_article
     * @method   \ryunosuke\Test\Gateway\Article      t_article($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\Article      $Article
     * @method   \ryunosuke\Test\Gateway\Article      Article($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\Comment      $t_comment
     * @method   \ryunosuke\Test\Gateway\Comment      t_comment($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\Comment      $Comment
     * @method   \ryunosuke\Test\Gateway\Comment      Comment($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $test
     * @method   \ryunosuke\Test\Gateway\TableGateway test($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $test1
     * @method   \ryunosuke\Test\Gateway\TableGateway test1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $test2
     * @method   \ryunosuke\Test\Gateway\TableGateway test2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $v_blog
     * @method   \ryunosuke\Test\Gateway\TableGateway v_blog($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     */
    class Database extends \ryunosuke\dbml\Database
    {
        /**
         * SQLServer は AUTO_INCREMENT なカラムを明示指定できないので小細工する
         *
         * @inheritdoc
         */
        public function insert($tableName, $data)
        {
            if ($this->getPlatform() instanceof SQLServerPlatform && is_string($tableName) && strpos($tableName, ' ') === false && strpos($tableName, '.') === false && strpos($tableName, ',') === false) {
                $tableName2 = $this->convertTableName($tableName);
                $pcols = $this->getSchema()->getTablePrimaryColumns($tableName2);
                $specified_id = count($pcols) === 1 && reset($pcols)->getAutoincrement() && array_key_exists(key($pcols), $data);

                if ($specified_id) {
                    $this->getConnection()->exec($this->getCompatiblePlatform()->getIdentityInsertSQL($tableName2, true));
                }

                $result = parent::insert(...func_get_args());

                if ($specified_id) {
                    $this->getConnection()->exec($this->getCompatiblePlatform()->getIdentityInsertSQL($tableName2, false));
                }

                return $result;
            }

            return parent::insert(...func_get_args());
        }

        /**
         * SQLServer は AUTO_INCREMENT なカラムを明示指定できないので小細工する
         *
         * @inheritdoc
         */
        public function duplicate($targetTable, array $overrideData = [], $where = [], $sourceTable = null)
        {
            if ($this->getPlatform() instanceof SQLServerPlatform) {
                $targetTable2 = $this->convertTableName($targetTable);
                $sourceTable2 = $this->convertTableName($sourceTable);
                $pcols1 = $this->getSchema()->getTablePrimaryColumns($targetTable2);
                $pcols2 = $this->getSchema()->getTablePrimaryColumns($sourceTable2 ?: $targetTable2);
                $specified_id = (count($pcols1) === 1 && reset($pcols1)->getAutoincrement() && array_key_exists(key($pcols1), $overrideData));
                if ($sourceTable2) {
                    $specified_id = $specified_id || isset($pcols2[key($pcols1)]);
                }

                if ($specified_id) {
                    $this->getConnection()->exec($this->getCompatiblePlatform()->getIdentityInsertSQL($targetTable2, true));
                }

                $result = parent::duplicate(...func_get_args());

                if ($specified_id) {
                    $this->getConnection()->exec($this->getCompatiblePlatform()->getIdentityInsertSQL($targetTable2, false));
                }

                return $result;
            }

            return parent::duplicate(...func_get_args());
        }
    }
}

namespace ryunosuke\Test\Gateway {

    use Doctrine\DBAL\Types\Type;
    use ryunosuke\dbml\Database;

    /**
     * @property \ryunosuke\Test\Gateway\TableGateway $aggregate
     * @method   \ryunosuke\Test\Gateway\TableGateway aggregate($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $auto
     * @method   \ryunosuke\Test\Gateway\TableGateway auto($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $foreign_c1
     * @method   \ryunosuke\Test\Gateway\TableGateway foreign_c1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $foreign_c2
     * @method   \ryunosuke\Test\Gateway\TableGateway foreign_c2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $foreign_d1
     * @method   \ryunosuke\Test\Gateway\TableGateway foreign_d1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $foreign_d2
     * @method   \ryunosuke\Test\Gateway\TableGateway foreign_d2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $foreign_p
     * @method   \ryunosuke\Test\Gateway\TableGateway foreign_p($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $foreign_s
     * @method   \ryunosuke\Test\Gateway\TableGateway foreign_s($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $foreign_sc
     * @method   \ryunosuke\Test\Gateway\TableGateway foreign_sc($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $g_ancestor
     * @method   \ryunosuke\Test\Gateway\TableGateway g_ancestor($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $g_child
     * @method   \ryunosuke\Test\Gateway\TableGateway g_child($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $g_parent
     * @method   \ryunosuke\Test\Gateway\TableGateway g_parent($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $horizontal1
     * @method   \ryunosuke\Test\Gateway\TableGateway horizontal1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $horizontal2
     * @method   \ryunosuke\Test\Gateway\TableGateway horizontal2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $misctype
     * @method   \ryunosuke\Test\Gateway\TableGateway misctype($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $misctype_child
     * @method   \ryunosuke\Test\Gateway\TableGateway misctype_child($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $multiprimary
     * @method   \ryunosuke\Test\Gateway\TableGateway multiprimary($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $multiunique
     * @method   \ryunosuke\Test\Gateway\TableGateway multiunique($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $noauto
     * @method   \ryunosuke\Test\Gateway\TableGateway noauto($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $noprimary
     * @method   \ryunosuke\Test\Gateway\TableGateway noprimary($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $nullable
     * @method   \ryunosuke\Test\Gateway\TableGateway nullable($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $paging
     * @method   \ryunosuke\Test\Gateway\TableGateway paging($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\Article      $t_article
     * @method   \ryunosuke\Test\Gateway\Article      t_article($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\Article      $Article
     * @method   \ryunosuke\Test\Gateway\Article      Article($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\Comment      $t_comment
     * @method   \ryunosuke\Test\Gateway\Comment      t_comment($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\Comment      $Comment
     * @method   \ryunosuke\Test\Gateway\Comment      Comment($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $test
     * @method   \ryunosuke\Test\Gateway\TableGateway test($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $test1
     * @method   \ryunosuke\Test\Gateway\TableGateway test1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $test2
     * @method   \ryunosuke\Test\Gateway\TableGateway test2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @property \ryunosuke\Test\Gateway\TableGateway $v_blog
     * @method   \ryunosuke\Test\Gateway\TableGateway v_blog($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     */
    class TableGateway extends \ryunosuke\dbml\Gateway\TableGateway
    {
    }

    class Article extends TableGateway
    {
        protected $defaultIteration  = 'assoc';
        protected $defaultJoinMethod = 'left';

        public function __construct(Database $database, $table_name, $entity_name)
        {
            parent::__construct($database, $table_name, $entity_name);

            $this->addScope('scope1', 'NOW()');
            $this->addScope('scope2', function ($id) {
                return [
                    'where' => [
                        'article_id' => $id,
                    ]
                ];
            });

            $this->addVirtualColumn([
                'title'         => [
                    'anywhere' => [
                        'enable'  => true,
                        'collate' => 'utf8_bin',
                    ],
                ],
                'title2'        => 'UPPER(%s.title)',
                'title3'        => [
                    'expression' => function ($v = 'title') {
                        return "a $v z";
                    },
                ],
                'checks'        => [
                    'type' => Type::getType('simple_array'),
                ],
                'comment_count' => [
                    'expression' => $database->subcount('t_comment')
                ],
            ]);
        }
    }

    /**
     * @method Article t_article($column = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     * @method Article Article($column = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
     */
    class Comment extends \ryunosuke\dbml\Gateway\TableGateway
    {
        public function __construct(Database $database, $table_name)
        {
            parent::__construct($database, $table_name);

            $this->addScope('scope1', 'NOW()');
            $this->addScope('scope2', function ($id) {
                return [
                    'where' => [
                        'comment_id' => $id,
                    ]
                ];
            });
        }
    }
}

namespace ryunosuke\Test\Entity {

    /**
     * @property string $title
     * @property Comment $comment
     * @property Comment[] $comments
     * @property Comment[] $Comment
     */
    class Article extends \ryunosuke\dbml\Entity\Entity
    {
    }

    /**
     * @property Article $article
     */
    class Comment extends \ryunosuke\dbml\Entity\Entity
    {
    }
}

namespace ryunosuke\Test\Platforms {

    /**
     * 特定条件下において sqlite は外部キー制約を持てるのでオーバーライドする Platform
     */
    class SqlitePlatform extends \Doctrine\DBAL\Platforms\SqlitePlatform
    {
        public function supportsForeignKeyConstraints()
        {
            return true;
        }

        public function getReadLockSQL()
        {
            return '/* lock for read */';
        }

        public function getWriteLockSQL()
        {
            return '/* lock for write */';
        }
    }
}

namespace ryunosuke\Test\Driver\PDOSqlite {

    /**
     * 上記の Platform を使用させるための Driver
     */
    class Driver extends \Doctrine\DBAL\Driver\PDOSqlite\Driver
    {
        public function getDatabasePlatform()
        {
            return new \ryunosuke\Test\Platforms\SqlitePlatform();
        }
    }
}
