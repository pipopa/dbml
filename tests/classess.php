<?php

namespace {

    function L($object)
    {
        $dummy = new class($object) {
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
        };
        /** @var object $dummy */
        return $dummy ?: $object;
    }
}

namespace ryunosuke\Test {

    use Doctrine\DBAL\Platforms\SQLServerPlatform;

    /**
     * テスト用 Database
     *
     * 行を変更したら戻したり SQLServer 用の小細工オーバーライド。
     *
     * @mixin \ryunosuke\Test\dbml\Annotation\Database
     */
    class Database extends \ryunosuke\dbml\Database
    {
        private $is_dirty = true;

        public function clean($callback)
        {
            if ($this->is_dirty) {
                $callback($this);
                $this->is_dirty = false;
            }
        }

        /**
         * カバレッジ確保のためにテスト中は常にエミュレーションOFFとする
         *
         * @inheritdoc
         */
        public function isEmulationMode($delegate_original = false)
        {
            if ($delegate_original) {
                return parent::isEmulationMode();
            }
            return false;
        }

        public function executeAffect($query, iterable $params = [])
        {
            $this->is_dirty = true;
            return parent::executeAffect($query, $params);
        }

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
                $specified_id = count($pcols) === 1 && reset($pcols)->getAutoincrement() && isset($data[key($pcols)]);

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

    use ryunosuke\dbml\Database;

    /**
     * @mixin \ryunosuke\Test\dbml\Annotation\TableGateway
     */
    class TableGateway extends \ryunosuke\dbml\Gateway\TableGateway
    {
    }

    class Article extends TableGateway
    {
        use \ryunosuke\Test\dbml\Annotation\ArticleTableGateway;

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
        }
    }

    class Comment extends TableGateway
    {
        use \ryunosuke\Test\dbml\Annotation\CommentTableGateway;

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
     * @mixin \ryunosuke\Test\dbml\Annotation\ArticleEntity
     * @property Comment $comment
     * @property Comment $Comment
     * @property Comment[] $comments
     */
    class Article extends \ryunosuke\dbml\Entity\Entity
    {
    }

    /**
     * @mixin \ryunosuke\Test\dbml\Annotation\CommentEntity
     * @property Article $Article
     */
    class Comment extends \ryunosuke\dbml\Entity\Entity
    {
    }

    /**
     * @mixin \ryunosuke\Test\dbml\Annotation\CommentEntity
     * @property Article $Article
     */
    class ManagedComment extends \ryunosuke\dbml\Entity\Entity
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
