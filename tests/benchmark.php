<?php

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use ryunosuke\dbml\Database;
use ryunosuke\dbml\Query\Expression\PhpExpression;

require_once __DIR__ . '/bootstrap.php';

$args = \ryunosuke\dbml\arguments([
    'initdb' => null,
    'sqlite',
]);
$dbms = $args[0];
$initdb = $args['initdb'];

// phpunit.xml から接続情報を拝借
$dbms = strtoupper($dbms);
$phpunit = simplexml_load_file(__DIR__ . '/phpunit.xml');
$url = $phpunit->xpath('/phpunit/php/const[contains(@name, "' . $dbms . '_URL")][1]');
$cmd = $phpunit->xpath('/phpunit/php/const[contains(@name, "' . $dbms . '_INITCOMMAND")][1]');
($url && $cmd) or die;

// テスト用コネクションクラス
class Connection extends \Doctrine\DBAL\Connection
{
    public function fetchUnique($statement, array $params = [], array $types = [])
    {
        return $this->executeQuery($statement, $params, $types)->fetchAll(\PDO::FETCH_ASSOC | \PDO::FETCH_UNIQUE);
    }

    public function fetchGroup($statement, array $params = [], array $types = [])
    {
        return $this->executeQuery($statement, $params, $types)->fetchAll(\PDO::FETCH_ASSOC | \PDO::FETCH_GROUP);
    }
}

// 接続とアダプタを用意
/** @var Connection $connection */
$connection = \Doctrine\DBAL\DriverManager::getConnection([
    'wrapperClass' => 'Connection',
    'url'          => $url[0]['value'],
]);
if (strlen($cmd[0]['value'])) {
    $connection->exec($cmd[0]['value']);
}
$database = new Database($connection);

// initdb フラグがあるなら初期化
if ($initdb) {
    // 適当なテーブルを用意
    call_user_func(function (AbstractSchemaManager $schemar) {
        $schemar->dropAndCreateTable(new Table(
            'article',
            [
                new Column('article_id', Type::getType('integer')),
                new Column('title', Type::getType('string')),
                new Column('content', Type::getType('string')),
            ],
            [
                new Index('PRIMARY', ['article_id'], true, true),
            ]
        ));
        $schemar->dropAndCreateTable(new Table(
            'comment',
            [
                new Column('article_id', Type::getType('integer')),
                new Column('seq', Type::getType('integer')),
                new Column('comment', Type::getType('string')),
            ],
            [
                new Index('PRIMARY', ['article_id', 'seq'], true, true),
            ]
        ));
        $schemar->dropAndCreateTable(new Table(
            'heavy',
            [
                new Column('heavy_id', Type::getType('integer')),
                new Column('data', Type::getType('string')),
            ],
            [
                new Index('PRIMARY', ['heavy_id'], true, true),
            ]
        ));
    }, $connection->getSchemaManager());

    // 適当なレコードを用意
    $database->transact(function (Database $database) {
        $ARTICLE_COUNT = 300;
        $COMMENT_COUNT = 20;
        foreach (range(1, $ARTICLE_COUNT) as $article_id) {
            $database->insert('article', [
                'article_id' => $article_id,
                'title'      => "title-$article_id",
                'content'    => str_repeat("content-$article_id", 10),
            ]);
            foreach (range(1, $COMMENT_COUNT) as $seq) {
                $database->insert('comment', [
                    'article_id' => $article_id,
                    'seq'        => $seq,
                    'comment'    => str_repeat("comment-$article_id-$seq", 10),
                ]);
            }
        }
    });
}

// 削除順のツラミがあるのでアプリ的に外部キーを定義
$database->addForeignKey('comment', 'article', 'article_id');

// 計測処理
$targets = [
    'select' => [
        'raw' => function () use ($connection) {
            $articles = $connection->fetchUnique('SELECT article_id, article.* FROM article');
            $article_ids = implode(',', array_map([$connection, 'quote'], array_keys($articles)));
            $comments = $connection->fetchGroup("SELECT article_id, seq, comment.* FROM comment WHERE article_id IN ($article_ids)");
            foreach ($articles as $n => $article) {
                $articles[$n]['title'] = strtoupper($article['title']);
                foreach ($comments[$article['article_id']] as $comment) {
                    $comment['comment'] = strtoupper($comment['comment']);
                    $articles[$n]['comments'][$comment['seq']] = $comment;
                }
            }
            return $articles;
        },
        'dbm' => function () use ($database) {
            $articles = $database->selectAssoc([
                'article.*' => [
                    'title'              => new PhpExpression('strtoupper', 'title'),
                    'comment comments.*' => [
                        'comment' => new PhpExpression('strtoupper', 'comment'),
                    ],
                ],
            ]);
            return $articles;
        },
    ],
    'insert' => [
        'raw' => function () use ($connection) {
            $connection->executeUpdate($connection->getDatabasePlatform()->getTruncateTableSQL('heavy'));
            $connection->beginTransaction();
            foreach (range(1, 1000) as $i) {
                $connection->insert('heavy', [
                    'heavy_id' => $i,
                    'data'     => "heavy-data-$i",
                ]);
            }
            $connection->commit();
            return $connection->fetchAll('SELECT * FROM heavy');
        },
        'dbm' => function () use ($database) {
            $database->getConnection()->executeUpdate($database->getPlatform()->getTruncateTableSQL('heavy'));
            $database->begin();
            foreach (range(1, 1000) as $i) {
                $database->insert('heavy', [
                    'heavy_id' => $i,
                    'data'     => "heavy-data-$i",
                ]);
            }
            $database->commit();
            return $database->getConnection()->fetchAll('SELECT * FROM heavy');
        },
    ],
    'update' => [
        'raw' => function () use ($connection) {
            $connection->beginTransaction();
            foreach (range(1, 1000) as $i) {
                $connection->update('heavy', [
                    'data' => "heavy-data-$i-x",
                ], [
                    'heavy_id' => $i,
                ]);
            }
            $connection->commit();
            return $connection->fetchAll('SELECT * FROM heavy');
        },
        'dbm' => function () use ($database) {
            $database->begin();
            foreach (range(1, 1000) as $i) {
                $database->update('heavy', [
                    'data' => "heavy-data-$i-x",
                ], [
                    'heavy_id' => $i,
                ]);
            }
            $database->commit();
            return $database->getConnection()->fetchAll('SELECT * FROM heavy');
        },
    ],
];

// 暖機運転兼同じ結果の担保
foreach ($targets as $cate => $target) {
    $raw = $target['raw']();
    $dbm = $target['dbm']();
    if (serialize($raw) !== serialize($dbm)) {
        $diff = print_r([
            'raw' => reset($raw),
            'dbm' => reset($dbm),
        ], true);
        printf("%-8s diff %s.\n", $cate, $diff);
    }
}
echo "\n";

// 計測
function benchmark($title, $proc)
{
    $t = microtime(true);
    $proc();
    $t = microtime(true) - $t;
    printf("%-16s %8f seconds.\n", $title, $t);
    return $t;
}

foreach ($targets as $cate => $target) {
    $t1 = benchmark("$cate-raw", $target['raw']);
    $t2 = benchmark("$cate-dbm", $target['dbm']);

    printf("%-16s %4f.\n", "$cate-raw:dbm", $t1 / $t2);
    printf("%-16s %4f.\n", "$cate-dbm:raw", $t2 / $t1);
    echo "\n";
}
