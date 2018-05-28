<?php

namespace ryunosuke\dbml\Transaction;

use Doctrine\DBAL\Logging\SQLLogger;
use ryunosuke\dbml\Mixin\OptionTrait;

/**
 * スタンダードな SQL ロガー
 *
 * Database の logger オプションにこのインスタンスを渡すとクエリがログられるようになる。
 *
 * ```php
 * # 標準出力にログる
 * $db = new Database($connection, [
 *     'logger' => new Logger([
 *         'destination' => STDOUT
 *     ]),
 * ]);
 * # /var/log/query.log にログる
 * $db = new Database($connection, [
 *     'logger' => new Logger([
 *         'destination' => '/var/log/query.log'
 *     ]),
 * ]);
 * ```
 *
 * Transaction 名前空間に居るのは少し小細工をしているから（癒着している）＋「クエリログは膨大なのでログらない（RDBMS のログに任せる）がトランザクションログはアプリで取っておきたい」という要件が多いため。
 * 別にグローバルなロガーとして設定しても問題はない。
 *
 * @package ryunosuke\dbml\Transaction
 */
class Logger implements SQLLogger
{
    use OptionTrait;

    /** @var array アンクオート対象 DCL */
    private static $DCLMAP = [
        '"START TRANSACTION"'     => 'START TRANSACTION',
        '"SAVEPOINT"'             => 'SAVEPOINT',
        '"COMMIT"'                => 'COMMIT',
        '"RELEASE SAVEPOINT"'     => 'RELEASE SAVEPOINT',
        '"ROLLBACK"'              => 'ROLLBACK',
        '"ROLLBACK TO SAVEPOINT"' => 'ROLLBACK TO SAVEPOINT',
    ];

    public static function getDefaultOptions()
    {
        return [
            // 出力場所（string/resource/ArrayObject/null）。ArrayObject を与えるとログを蓄えられるがテストや preview 用
            'destination' => null,
            // ロックモード（true にすると flock で書き込む。 NFS などではないローカルファイルなどなら不要）
            'lockmode'    => false,
            // プレフィックス（各ログに付与されるプレフィックス。クロージャを渡すと書き込み時点で都度呼び出される）
            'prefix'      => null,
            // フォーマッタ（出力前にクエリ文字列が通される。フォーマットしたり改行を削除して1行化したり）
            'format'      => null,
            // 出力モード（文字列ならメソッドが、クロージャなら任意の処理が呼び出される）
            'method'      => 'escape',
        ];
    }

    /**
     * コンストラクタ
     *
     * @param mixed $destination 出力場所だけはほぼ必須かつ単一で与えることも多いため別引数で与え**られる**
     * @param array $options オプション
     */
    public function __construct($destination = null, $options = [])
    {
        if (is_array($destination)) {
            $options = $destination;
        }
        else {
            $options['destination'] = $destination;
        }
        $this->setDefault($options);
    }

    private function _write($destination, $log)
    {
        // ArrayObjet は特別扱いでリソースに書き込むのではなく配列に格納する
        if ($destination instanceof \ArrayObject) {
            $destination[] = $log;
            return;
        }

        // ファイル名の場合は開くが閉じない
        static $persistences = [];
        if (is_string($destination)) {
            if (!isset($persistences[$destination])) {
                $persistences[$destination] = fopen($destination, 'a');
            }
            $destination = $persistences[$destination];
        }

        // 配列とかオブジェクトなどが与えられた時。所詮ログなので例外を投げたりせずスルー
        if (!is_resource($destination)) {
            return;
        }

        $locking = $this->getUnsafeOption('lockmode');

        if ($locking) {
            flock($destination, LOCK_EX);
        }
        fwrite($destination, $log . "\n");
        if ($locking) {
            flock($destination, LOCK_UN);
        }
    }

    protected function _escape($sql, $params)
    {
        // 所詮ログなのでざっくりとエスケープ
        $n = 0;
        return preg_replace_callback('/(\?)|(:([a-z_][a-z_0-9]*))/ui', function ($m) use ($params, &$n) {
            $name = $m[1] === '?' ? $n++ : $m[3];
            if (!array_key_exists($name, $params)) {
                return $m[0];
            }
            if ($params[$name] === null) {
                return 'NULL';
            }
            if (is_numeric($params[$name])) {
                return $params[$name];
            }
            return '"' . addslashes((string) $params[$name]) . '"';
        }, $sql);
    }

    protected function _keyvalue($sql, $params)
    {
        return $sql . ':' . json_encode($params, JSON_UNESCAPED_UNICODE);
    }

    public function log($sql, array $params = [])
    {
        // Connection がクォートするので外す
        if (isset(self::$DCLMAP[$sql])) {
            $sql = self::$DCLMAP[$sql];
        }

        // フォーマット
        $format = $this->getUnsafeOption('format');
        if ($format instanceof \Closure) {
            $sql = $format($sql);
        }

        // コンバート
        $method = $this->getUnsafeOption('method');
        if (is_string($method)) {
            $method = function ($sql, $params) use ($method) { return $this->{"_$method"}($sql, $params); };
        }
        $sql = $method($sql, $params);

        // プレフィックス
        $prefix = $this->getUnsafeOption('prefix');
        if ($prefix instanceof \Closure) {
            $prefix = $prefix();
        }
        $sql = $prefix . $sql;

        $this->_write($this->getUnsafeOption('destination'), $sql);
    }

    public function getLog()
    {
        $holder = $this->getUnsafeOption('destination');
        if ($holder instanceof \ArrayObject) {
            return (array) $holder;
        }
        return [];
    }

    public function startQuery($sql, array $params = null, array $types = null) { $this->log($sql, $params ?: []); }

    public function stopQuery() { }
}
