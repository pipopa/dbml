<?php

namespace ryunosuke\dbml\Query;

/**
 * クエリ文字列（完全性は問わない。部分クエリでも良い）とパラメータを持つインターフェース
 */
interface Queryable
{
    /**
     * クエリ文字列を返す
     *
     * @return string クエリ文字列
     */
    public function getQuery();

    /**
     * パラメータを返す
     *
     * @return array パラメータ配列
     */
    public function getParams();

    /**
     * パラメータをマージして文字列表現を返す
     *
     * クエリ文字列を返し、引数配列にパラメータが追加される
     *
     * @param ?array $params この引数にパラメータが追加される
     * @return string 文字列表現を返す
     */
    public function merge(?array &$params);
}
