<?php

namespace ryunosuke\dbml\Entity;

/**
 * エンティティであることを示すインターフェース
 *
 * エンティティとして利用するには必ずこのインターフェースを実装しなければならない。
 */
interface Entityable extends \ArrayAccess
{
    /**
     * 配列からプロパティをセットする
     *
     * @param array $fields 元配列
     * @return $this 自分自身
     */
    public function assign($fields);

    /**
     * 子要素も含めて再帰的に配列化する
     *
     * @return array プロパティを配列化したもの
     */
    public function arrayize();
}
