<?php

namespace ryunosuke\dbml\Entity;

use ryunosuke\dbml\Database;
use ryunosuke\dbml\Mixin\DebugInfoTrait;

/**
 * 組み込みのデフォルトエンティティクラス
 */
class Entity implements Entityable
{
    use DebugInfoTrait;

    /** @var Database これをどう使うかは自由。うまく使えば ActiveRecord のような実装もできるはず */
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database->getOriginal();
    }

    public function __call($name, $arguments)
    {
        return ($this->$name)(...$arguments);
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    public function assign($fields)
    {
        foreach ($fields as $k => $v) {
            $this->$k = $v;
        }
        return $this;
    }

    public function arrayize()
    {
        $result = get_object_vars($this);
        unset($result['database']);
        foreach ($result as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $ck => $cv) {
                    if ($cv instanceof Entityable) {
                        $result[$k][$ck] = $cv->arrayize();
                    }
                }
            }
        }
        return $result;
    }
}
