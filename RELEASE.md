# RELEASE

バージョニングはセマンティックバージョニングでは**ありません**。

| バージョン   | 説明
|:--           |:--
| メジャー     | 大規模な仕様変更の際にアップします（クラス構造・メソッド体系などの根本的な変更）。<br>メジャーバージョンアップ対応は多大なコストを伴います。
| マイナー     | 小規模な仕様変更の際にアップします（中機能追加・メソッドの追加など）。<br>マイナーバージョンアップ対応は1日程度の修正で終わるようにします。
| パッチ       | バグフィックス・小機能追加の際にアップします（基本的には互換性を維持するバグフィックス）。<br>パッチバージョンアップは特殊なことをしてない限り何も行う必要はありません。

なお、下記の一覧のプレフィックスは下記のような意味合いです。

- change: 仕様変更
- feature: 新機能
- fixbug: バグ修正
- refactor: 内部動作の変更
- `*` 付きは互換性破壊

## x.y.z

- Entity 消したい。使わない…
- phpstorm と相性が悪いのでマジックメソッドを撲滅したい
- TableGateway の column/where などを個別メソッド化する（可変引数などに対応できていないので）

## 2.0.9

- [feature][Database] IGNORE シンタックスが使えるメソッドに ～Ignore を用意
- [feature][Database] save の実装
- [feature][Database] changeArray のリファクタ
- [feature][Database] sqlite と postgresql の merge を実装
- [feature][Database] isEmulationMode を追加
- [feature][Database] 仮想カラムの更新機能
- [change][Database] modify でエラーにならないように insert する機能の廃止
- [change][TableGateway] bindScope は引数が累積するのではなくオリジナルの上書きとする

## 2.0.8

- [feature] bump version

## 2.0.7

- [fixbug][TableGateway] 継承クラスで scopes にアクセスできない不具合を修正
- [feature][TableGateway] paginate/sequence の委譲メソッドを追加
- [feature][Database] 暖気運転を行う warmup メソッドを追加
- [fixbug][QueryBuilder] トップレベル以外で仮想カラムが使われてしまう不具合を修正
- [feature][QueryBuilder] before/after コールバックを実装
- [feature][QueryBuilder] 配列時の取得方法を指定する arrayFetch オプションを追加
- [feature][Logger] メタデータの出力機能を追加

## 2.0.6

- [feature][QueryBuilder] with 句の対応
- [feature][QueryBuilder] 常に末尾に並び順を追加できる defaultOrder オプションを追加
- [feature][QueryBuilder] nullsOrder を個別に当てられるように修正
- [fixbug][QueryBuilder] where を呼ぶだけで遅延仮想カラムの取得が走っていた不具合を修正

## 2.0.5

- [fixbug][Database] overrideColumns で循環参照になることがある不具合を修正
- [fixbug][QueryBuilder] 仮想カラムが暴走する不具合を修正
- [feature][QueryBuilder] NULL の並び順を制御できる nullsOrder オプションを追加
- [feature][Paginator] 前後ページがあるかを返す hasPrev/hasNext を実装

## 2.0.4

- [feature][Database] 仮想カラムの型はある程度推測できるのでそのようにした
- [fixbug][Database] reduce で想定より多くの行が消えてしまう不具合を修正
- [fixbug][Database] range の [空, 空] の自動フィルタの不具合を修正
- [feature][QueryBuilder] 設定されている select 句を伏せることができる unselect メソッドを実装
- [feature][QueryBuilder] テーブル指定でスコープを当たられる scope メソッドを実装
- [fixbug][QueryBuilder] 仮想カラムを使っていないのに値が変わってしまう不具合を修正
- [fixbug][QueryBuilder] Queryable に ASC/DESC 込みで orderBy すると不正な sql になる不具合を修正
- [fixbug][QueryBuilder] 親・子行がない時に notice エラーが出る不具合を修正
- [feature][TableGateway] スコープが定義されているか調べられる definedScope を実装
- [feature][TableGateway] スコープのデフォルト引数を設定できる bindScope を実装
- [fixbug][TableGateway] mixScope で可変引数があると値が渡らない不具合を修正

## 2.0.3

- [change][TableDescriptor] テーブル記法の埋め込みを json ではなく paml に変更
- [fixbug][TableDescriptor] 特定条件で仮想カラム・スコープが見つからない不具合を修正
- [fixbug][QueryBuilder] スコープが JOIN 記述を含んでいても無視される不具合を修正
- [fixbug][TableGateway] 継承したクラスで private エラーになる不具合を修正
- [feature][TableGateway] mixScope で追加スコープを与えられるように修正
- [feature][Database] 曖昧な外部キーや存在しない外部キー指定時にしっかりと例外が飛ぶように改善

## 2.0.2

- [feature][QueryBuilder] 暗黙スコープを指定可能にした
- [feature][TableGateway] affect 系にスコープを当てないオプションを用意
- [fixbug][Database] echoAnnotation の結果が FQSEN じゃない不具合を修正

## 2.0.1

- [feature][QueryBuilder] on メソッドを追加してサブクエリとの JOIN 機能を強化
- [feature][QueryBuilder] operatize を実装
- [feature][Operator] default の行値式対応
- [feature][Database] echoPhpStormMeta を実装
- [feature][Database] getAffectedRows メソッドを追加
- [feature][TableDescriptor] group 記述を追加
- [feature][Logger] array モードを新設
- [feature][Generator] ArrayGenerator の assoc 対応
- [feature][Operator] マジックメソッドで左辺未設定インスタンスを返せるように変更
- [feature][TableGateway] sub系のエイリアスメソッドを用意
- [feature][TableGateway] export系メソッドを用意
- [feature][TableGateway] スコープ定義のインスタンス返しの対応
- [refactor][all] doctrine 由来のメソッド名を独自体系に変更
  - deprecated Database::executeQuery, use Database::executeSelect
  - deprecated Database::executeUpdate, use Database::executeAffect
- [fixbug][Logger] バイナリ時の表示を修正
- [fixbug][Database] modify でエラーになることがある不具合を修正
- [change][Schema] テーブル・カラムの ini 仕様を廃止

## 2.0.0

- [feature][all] 各所で iterable を受けられるように修正
- [feature][Database] binder メソッドの追加
- [feature][Database] ゲッター/セッターの強化
  - getConnections を追加してコネクション単位の取得を可能にした
  - setLogger を追加してコネクション毎にロガーの設定を可能にした
  - compatiblePlatform をインジェクション可能にした
- [feature][CompatiblePlatform] getPrimaryCondition の行値式対応
- [feature][TableDescriptor] `{id}` が `{id: id}` になるような短縮構文を導入
- [feature][SubqueryBuilder] lazy mode に batch/yield を新設
- [feature][Entity] エンティティのコンストラクタを自由化
- [fixbug][CompatiblePlatform] postgresql で php と db のキーが異なる不具合を修正
- [fixbug][QueryBuilder] 同名テーブルを自動 JOIN したときに統合されない不具合を修正
- [fixbug][Operator] オペレータが大文字化されてしまう不具合を修正
- [fixbug][Operator] LIKEIN で エスケープが行われていない不具合を修正
- [*change][Database] テーブル名 <=> エンティティ名の1対1制限を撤廃
- [*change][Database] autoCastSuffix を削除
- [*change][Transaction] Logger のシンプル化
  - 文字列化系オプションをすべて callback にまとめた
  - バッファリングを有効にしてログが直列化されるようにした
- [*change][QueryBuilder] PhpExpression の廃止
- [*change][QueryBuilder] groupBy 可変引数に変更
- [*change][QueryBuilder] autoSelectClosure を削除
- [*change][QueryBuilder] 直接 param を持つのではなく、 param を持った Queryable を持つようにした
- [*change][QueryBuilder] SubqueryBuilder を削除して機能マージした
- [*change][all] 仮想カラムは Gateway が握るのではなく Schema が握るように変更
  - Gateway 周りから仮想カラム関係を全て削除
  - 代わりに Database, Schema で仮想カラムを追加・変更できるようになった
- [*change][all] 互換用に残っていたコード・設定を削除
  - Database::addRelation の lazy フラグを削除
  - Database::notableAsColumn を削除
  - Gateway::offsetGetFind を削除
  - Gateway::addVirtualColumn() を削除

## 1.0.7

- [change] 内部処理をいくつか変更
- [change][QueryBuilder] 存在するカラムのクロージャを自動で SELECT 句に加えるオプションを用意

## 1.0.6

- [refactor] 依存ライブラリの整理
- [feature][Database] subquery メソッドを追加
- [change][Database] upsert のデフォルト引数を統一
- [feature][TableGateway] affect 系メソッドのオーバーライドに対応
- [feature][Schema] 外部キーの遅延追加を実装

## 1.0.5

- [feature][Database] anywhere のクオート対応
- [feature][Database] addRelation メソッドを追加
- [feature][TableGateway] 仮想カラムの機能を強化
  - implicit 属性を持たせて !取得や where,having で仮想カラムを使用できるようになった
  - 仮想カラムの削除に対応（メソッド名が変わったが旧名も使える）
- [feature][TableGateway] スコープ周りの改善
  - スコープの同時指定
  - 合成スコープ
- [feature][TableGateway] autoincrement 系メソッドの移譲
  - getLastInsertId
  - resetAutoIncrement
- [feature][TableDescriptor] テーブルに紐付かないカラムのフラット指定を可能にした
- [fixbug][QueryBuilder] スコープ付きテーブル記法の場合は ON やサブクエリに飲み込まれるのが正しい仕様
- [fixbug][QueryBuilder] スコープ付きテーブル記法の場合に修飾子がつかない不具合を修正
- [fixbug][QueryBuilder] neighbor を呼ぶと limit が設定される副作用を修正
- [fixbug][QueryBuilder] orderBy で Queryable を使えるように修正
- [change][Database] var_dump の出力を変更

## 1.0.4

- [feature][Operator] range で行値式が使えるように拡張
- [feature][Transaction] catch イベントを追加
- [feature][TableGateway] invoke で find が呼び出せるように拡張
- [feature][Database] aggregate でクロス集計がしやすいように拡張
- [feature][Database] [insert|upsert|modify]Conditionally を実装
- [fixbug][Database] loadCsv が dryrun に対応していなかった不具合を修正

## 1.0.3

- [feature][TableGateway] offsetGet の * 対応
- [feature][Database] INSERT SET 構文対応
- [feature][QueryBuilder] 前後のレコードを返す neighbor を実装
- [fixbug][QueryBuilder] ORDER, LIMIT 付きの UNION でエラーになっていた不具合を修正
- [fixbug][All] 一部マルチバイトで動かないメソッドがあったので正規表現に/uオプションを追加
- [chage][Database] :placeholder の仕様を変更
  - column は ":name" と指定するのを正式仕様に変更
  - where も ":name" で placeholder が使えるように変更

## 1.0.2

- [feature][Database] クエリビルダを返す aggregate を実装
- [feature][Database] bool は int で bind されるように修正
- [feature][Database] レコードをかき集める gather を実装
- [feature][Database] update/delete のテーブル記法対応
- [feature][Database] レコードを削減する reduce を実装
- [feature][QueryBuilder] 集約系を where でも使えるように拡張
- [feature][QueryBuilder] クロージャを返すクロージャに対応
- [feature][QueryBuilder] where で主キーを表す空文字キーを実装
- [feature][QueryBuilder] レコードを少しづつ取得する chunk を実装
- [feature][CompatiblePlatform] Postgres の upsert に対応
- [feature][Logger] クロージャログの対応
- [refactor][QueryBuilder] ORDER BY の内部の保持形式を変更
- [fixbug][Database] mysql 以外で自動採番列に null を与えるとエラーになる不具合を修正
- [fixbug][Database] テーブル記法と引数渡しの複合で記法が効かない不具合を修正
- [fixbug][Database] 集約結果が数値前提だった不具合を修正
- [fixbug][Database] スキーマ取得前に context 相当のことをすると2重に取ってしまう不具合を修正
- [fixbug][TableGateway] subselect 系が一切動いていない不具合を修正
- [fixbug][TableGateway] スコープを当てても結果が残る不具合を修正
- [fixbug][TableGateway] 特定条件で WHERE が2倍になる不具合を修正
- [fixbug][QueryBuilder] detectAutoOrder を修正
- [fixbug][QueryBuilder] join の時の駆動表判定が誤る可能性がある不具合を修正
- [fixbug][QueryBuilder] join 条件で sub 系や!が使えない不具合を修正
- [change][TableGateway] offsetGet の挙動を find から pk に変更（互換性のため要オプション）

## 1.0.1

- [fixbug][Database] エンティティ名でアクセスしてもエンティティゲートウェイが返らない不具合を修正
- [fixbug][Database] エンティティを作用系に投げるとエラーが出る不具合を修正
- [fixbug][Database] sqlite の truncate で自動採番列がリセットされない不具合を修正
- [feature][Database] NOT ブロックの実装
- [feature][Database] echoAnnotation を実装
- [feature][TableGateway] 主キー指定の配列アクセスを実装

## 1.0.0

- 公開
