<?php

namespace ryunosuke\dbml\Query;

use Doctrine\DBAL\Connection;
use ryunosuke\dbml\Database;
use ryunosuke\dbml\Mixin\DebugInfoTrait;

/**
 * Statement をラップして扱いやすくしたクラス
 *
 * 主にプリペアドステートメントのために存在する。よってエミュレーションモードがオンだとほとんど意味を為さない。
 *
 * クエリビルダは疑問符プレースホルダが大量に埋め込まれる可能性があるので、全部パラメータにするのが大変。
 * ので、「prepare した時点で固定し、残り（名前付き）のみ後から指定する」という仕様になっている。
 *
 * ```php
 * $qb = $db->select('t_table.*', ['id = :id', 'opt1' => 1, 'opt2' => 2])->prepare();
 * // :id は解決していないため、パラメータで渡すことができる（下記はエミュレーションモードがオフなら『本当の』プリペアドステートメントで実行される）
 * $qb->fetchArray([':id' => 100]); // SELECT t_table.* FROM t_table WHERE id = 100 AND opt1 = 1 AND opt2 = 2
 * $qb->fetchArray([':id' => 101]); // SELECT t_table.* FROM t_table WHERE id = 101 AND opt1 = 1 AND opt2 = 2
 * $qb->fetchArray([':id' => 102]); // SELECT t_table.* FROM t_table WHERE id = 102 AND opt1 = 1 AND opt2 = 2
 * ```
 *
 * `['id' => ':id']` ではなく `['id = :id']` であることに注意。
 * 「通常の値バインド」と区別する必要がある（`['id' => ':id']` だと `WHERE id = ":id"` というただの文字列の WHERE になってしまう）。
 */
class Statement implements Queryable
{
    use DebugInfoTrait;

    private const AUTO_BIND_KEY = '__dbml_auto_bind';

    /** @var string */
    private $query;

    /** @var array */
    private $params = [];

    /** @var Database */
    private $database;

    /** @var \Doctrine\DBAL\Statement[] */
    private $statements = [];

    public function __construct($query, array $params, Database $database)
    {
        // コンストラクタ時点で疑問符プレースホルダーをすべて名前付きプレースホルダーに変換しておく
        $n = 0;
        $this->query = preg_replace_callback('#\?#', function () use (&$n) {
            return ':' . self::AUTO_BIND_KEY . ($n++);
        }, $query);

        // 初期パラメータはこの時点で確定
        $m = 0;
        foreach ($params as $k => $param) {
            if (is_int($k)) {
                $m++;
                $k = self::AUTO_BIND_KEY . $k;
            }
            $this->params[$k] = $param;
        }

        // 疑問符プレースホルダーの数に不整合があるととても厄介なことになるのでこの段階で弾く
        if ($n !== $m) {
            throw new \InvalidArgumentException("'?' placeholder length is mismatch ($n !== $m).");
        }

        // コネクションを保持
        $this->database = $database;
    }

    private function _execute(array $params = [], Connection $connection)
    {
        // 同じコネクションの stmt はキャッシュする（$this->query は不変なので問題ない）
        $key = spl_object_hash($connection);
        if (!isset($this->statements[$key])) {
            $this->statements[$key] = $connection->prepare($this->query);
        }
        $stmt = $this->statements[$key];

        // 引数パラメータを基本として初期パラメータで上書く
        foreach ($params + $this->params as $k => $param) {
            $stmt->bindValue($k, $param);
        }

        // 実行して返す
        $stmt->execute();
        return $stmt;
    }

    /**
     * 取得系クエリとして実行する
     *
     * @param array $params 追加パラメータ
     * @param Connection $connection コネクション
     * @return \Doctrine\DBAL\Statement stmt オブジェクト
     */
    public function executeQuery(array $params = [], Connection $connection = null)
    {
        return $this->_execute($params, $connection ?: $this->database->getSlaveConnection());
    }

    /**
     * 更新系クエリとして実行する
     *
     * @param array $params 追加パラメータ
     * @param Connection $connection コネクション
     * @return \Doctrine\DBAL\Statement stmt オブジェクト
     */
    public function executeUpdate(array $params = [], Connection $connection = null)
    {
        return $this->_execute($params, $connection ?: $this->database->getMasterConnection());
    }

    /**
     * @inheritdoc
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @inheritdoc
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @inheritdoc
     */
    public function merge(&$params)
    {
        foreach ($this->getParams() as $k => $param) {
            $params[$k] = $param;
        }
        return $this->getQuery();
    }
}
