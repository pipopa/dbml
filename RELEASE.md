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
