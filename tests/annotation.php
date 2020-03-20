<?php

namespace ryunosuke\Test\dbml\Annotation;

// this code auto generated.

// @formatter:off

/**
 * @property \ryunosuke\dbml\Gateway\TableGateway $aggregate
 * @method   \ryunosuke\dbml\Gateway\TableGateway aggregate($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $auto
 * @method   \ryunosuke\dbml\Gateway\TableGateway auto($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $foreign_c1
 * @method   \ryunosuke\dbml\Gateway\TableGateway foreign_c1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $foreign_c2
 * @method   \ryunosuke\dbml\Gateway\TableGateway foreign_c2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $foreign_d1
 * @method   \ryunosuke\dbml\Gateway\TableGateway foreign_d1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $foreign_d2
 * @method   \ryunosuke\dbml\Gateway\TableGateway foreign_d2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $foreign_p
 * @method   \ryunosuke\dbml\Gateway\TableGateway foreign_p($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $foreign_s
 * @method   \ryunosuke\dbml\Gateway\TableGateway foreign_s($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $foreign_sc
 * @method   \ryunosuke\dbml\Gateway\TableGateway foreign_sc($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $g_ancestor
 * @method   \ryunosuke\dbml\Gateway\TableGateway g_ancestor($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $g_child
 * @method   \ryunosuke\dbml\Gateway\TableGateway g_child($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $g_parent
 * @method   \ryunosuke\dbml\Gateway\TableGateway g_parent($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $horizontal1
 * @method   \ryunosuke\dbml\Gateway\TableGateway horizontal1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $horizontal2
 * @method   \ryunosuke\dbml\Gateway\TableGateway horizontal2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $misctype
 * @method   \ryunosuke\dbml\Gateway\TableGateway misctype($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $misctype_child
 * @method   \ryunosuke\dbml\Gateway\TableGateway misctype_child($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $multiprimary
 * @method   \ryunosuke\dbml\Gateway\TableGateway multiprimary($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $multiunique
 * @method   \ryunosuke\dbml\Gateway\TableGateway multiunique($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $noauto
 * @method   \ryunosuke\dbml\Gateway\TableGateway noauto($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $noprimary
 * @method   \ryunosuke\dbml\Gateway\TableGateway noprimary($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $nullable
 * @method   \ryunosuke\dbml\Gateway\TableGateway nullable($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $oprlog
 * @method   \ryunosuke\dbml\Gateway\TableGateway oprlog($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $paging
 * @method   \ryunosuke\dbml\Gateway\TableGateway paging($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\Test\Gateway\Article $t_article
 * @method   \ryunosuke\Test\Gateway\Article t_article($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\Test\Gateway\Article $Article
 * @method   \ryunosuke\Test\Gateway\Article Article($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\Test\Gateway\Comment $t_comment
 * @method   \ryunosuke\Test\Gateway\Comment t_comment($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\Test\Gateway\Comment $Comment
 * @method   \ryunosuke\Test\Gateway\Comment Comment($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $test
 * @method   \ryunosuke\dbml\Gateway\TableGateway test($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $test1
 * @method   \ryunosuke\dbml\Gateway\TableGateway test1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $test2
 * @method   \ryunosuke\dbml\Gateway\TableGateway test2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $v_blog
 * @method   \ryunosuke\dbml\Gateway\TableGateway v_blog($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 */
trait Database{}

/**
 * @property \ryunosuke\dbml\Gateway\TableGateway $aggregate
 * @method   $this aggregate($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $auto
 * @method   $this auto($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $foreign_c1
 * @method   $this foreign_c1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $foreign_c2
 * @method   $this foreign_c2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $foreign_d1
 * @method   $this foreign_d1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $foreign_d2
 * @method   $this foreign_d2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $foreign_p
 * @method   $this foreign_p($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $foreign_s
 * @method   $this foreign_s($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $foreign_sc
 * @method   $this foreign_sc($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $g_ancestor
 * @method   $this g_ancestor($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $g_child
 * @method   $this g_child($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $g_parent
 * @method   $this g_parent($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $horizontal1
 * @method   $this horizontal1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $horizontal2
 * @method   $this horizontal2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $misctype
 * @method   $this misctype($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $misctype_child
 * @method   $this misctype_child($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $multiprimary
 * @method   $this multiprimary($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $multiunique
 * @method   $this multiunique($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $noauto
 * @method   $this noauto($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $noprimary
 * @method   $this noprimary($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $nullable
 * @method   $this nullable($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $oprlog
 * @method   $this oprlog($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $paging
 * @method   $this paging($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\Test\Gateway\Article $t_article
 * @method   $this t_article($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\Test\Gateway\Article $Article
 * @method   $this Article($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\Test\Gateway\Comment $t_comment
 * @method   $this t_comment($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\Test\Gateway\Comment $Comment
 * @method   $this Comment($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $test
 * @method   $this test($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $test1
 * @method   $this test1($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $test2
 * @method   $this test2($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @property \ryunosuke\dbml\Gateway\TableGateway $v_blog
 * @method   $this v_blog($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 */
trait TableGateway{}

/**
 * @mixin TableGateway
 * @method array|\ryunosuke\Test\Entity\Article[]     array($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Article[]     assoc($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Article|false tuple($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Article|false find($variadic_primary, $tableDescriptor = [])
 * @method array|\ryunosuke\Test\Entity\Article[]     arrayInShare($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Article[]     assocInShare($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Article|false tupleInShare($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Article|false findInShare($variadic_primary, $tableDescriptor = [])
 * @method array|\ryunosuke\Test\Entity\Article[]     arrayForUpdate($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Article[]     assocForUpdate($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Article|false tupleForUpdate($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Article|false findForUpdate($variadic_primary, $tableDescriptor = [])
 * @method array|\ryunosuke\Test\Entity\Article[]     arrayOrThrow($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Article[]     assocOrThrow($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Article       tupleOrThrow($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Article       findOrThrow($variadic_primary, $tableDescriptor = [])
 */
trait ArticleTableGateway{}

/**
 * @mixin TableGateway
 * @method array|\ryunosuke\Test\Entity\Comment[]     array($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Comment[]     assoc($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Comment|false tuple($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Comment|false find($variadic_primary, $tableDescriptor = [])
 * @method array|\ryunosuke\Test\Entity\Comment[]     arrayInShare($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Comment[]     assocInShare($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Comment|false tupleInShare($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Comment|false findInShare($variadic_primary, $tableDescriptor = [])
 * @method array|\ryunosuke\Test\Entity\Comment[]     arrayForUpdate($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Comment[]     assocForUpdate($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Comment|false tupleForUpdate($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Comment|false findForUpdate($variadic_primary, $tableDescriptor = [])
 * @method array|\ryunosuke\Test\Entity\Comment[]     arrayOrThrow($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Comment[]     assocOrThrow($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Comment       tupleOrThrow($tableDescriptor = [], $where = [], $orderBy = [], $limit = [], $groupBy = [], $having = [])
 * @method array|\ryunosuke\Test\Entity\Comment       findOrThrow($variadic_primary, $tableDescriptor = [])
 */
trait CommentTableGateway{}

/**
 * @property int $article_id
 * @property string $title
 * @property array|string $checks
 * @property int $title2
 * @property int $title3
 * @property int $title4
 * @property int $title5
 * @property int $comment_count
 */
trait ArticleEntity{}

/**
 * @property int $comment_id
 * @property int $article_id
 * @property string $comment
 */
trait CommentEntity{}
