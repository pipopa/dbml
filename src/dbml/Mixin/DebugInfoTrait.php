<?php

namespace ryunosuke\dbml\Mixin;

use ryunosuke\dbml\Database;

/**
 * var_dump で不必要なプロパティを伏せる trait
 *
 * これを use すると（現在の実装では） Database が var_dump に出現しなくなる。
 */
trait DebugInfoTrait
{
    /**
     * __debugInfo
     *
     * @ignoreinherit
     * @see http://php.net/manual/ja/language.oop5.magic.php#object.debuginfo
     *
     * @return array var_dump されるプロパティ
     */
    public function __debugInfo()
    {
        $properties = (array) $this;
        foreach ($properties as $name => $value) {
            if ($value instanceof Database) {
                unset($properties[$name]);
            }
        }
        return $properties;
    }
}
