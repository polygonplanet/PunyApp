# [PunyApp](https://github.com/polygonplanet/PunyApp) リファレンス

## これはなに？

PunyApp は、MVC モデルの軽量 PHP フレームワーク (マイクロフレームワーク) です。

おおまかな設計は CakePHP を基にしてて、使い方も似ています。
規模のあるフレームワークを使うまでもないような小規模な開発に向いています。

他の PHP 拡張等は必要ありません。

ファイルサイズ: 約 62KB (v1.0.26)

## ダウンロード

* [ダウンロード リリース一覧](https://github.com/polygonplanet/PunyApp/releases)

## GitHub

* [GitHub](https://github.com/polygonplanet/PunyApp)

## 必要な環境

* PHP 7.0.0+
* mod_rewrite が有効 (Apache Server の場合)

## ライセンス

* MIT

## 機能/特徴

* MySQL, PostgreSQL, SQLite, [Posql](https://github.com/polygonplanet/Posql) に対応
* ビューのテンプレート変数はデフォルトでHTMLエスケープされる
* セッション (データベース使用可能)
* クッキー (暗号化対応)
* フォームのバリデーション
* フォームリクエストのトークン (CSRF対策)
* イベント (初期化時、データベースエラー時など)
* エラーログ、SQL クエリのログ

## ディレクトリ構成

```
/application             → アプリケーションディレクトリ
    /controllers         → コントローラ
    /models              → モデル
    /views               → ビュー
    /libraries           → 共通ライブラリやヘルパー等
    /storage             → データを保管するディレクトリ
    /settings            → 設定
        app-settings.php → アプリケーション設定ファイル
    /public              → 公開ディレクトリ
        /css
        /js
        index.php
/punyapp                 → PunyApp 内部ライブラリ
/vendors                 → 外部ライブラリ等を入れるディレクトリ
index.php
```

application ディレクトリ配下にソースコードを置きます。

----

## アプリケーション設定

`/application/settings/app-settings.php` を編集してアプリケーションの設定をします。

```php
$settings = array(
  /**
   * System settings
   */
  'system' => array(
    /**
     * Debug mode
     *
     * true:
     *   - エラーが起きた時、表示します
     *   - 'database.debug' が適応されます
     *
     * false:
     *   - エラーが起きても何も表示しません
     *   - 'database.default' が適応されます
     */
    'debug' => true,
    /**
     * Internal language
     * 日本語の場合 'ja' を設定します
     */
    'lang' => 'ja',
    /**
     * Timezone
     * 日本時刻の場合は 'Asia/Tokyo' を入力します
     */
    'timezone' => 'Asia/Tokyo',
    /**
     * アプリケーションのセキュリティキーとなるもの
     * 暗号化時に使用されます
     * 長さ不問、記号等も使用できます
     * 適当な文字列に変更してください
     */
    'salt' => 'ZQJaiPPYn6Tldb2gottKwIDmGiatuSnV',
    /**
     * エラーが起きた時ログを保存するかどうか
     * 'application/storage/logs' に保存されます
     */
    'logError' => true,
    /**
     * logError の最大行数
     */
    'logErrorMax' => 200
  ),
  /**
   * Database settings
   */
  'database' => array(
    /**
     * default設定
     *
     * 'system.debug' が false の場合に適応されます
     */
    'default' => array(
      /**
       * Database engine
       * "mysql", "pgsql", "sqlite" または "posql" が利用可能です
       */
      'engine' => '',
      /**
       * データベースの内部文字コード (default = 'utf8')
       */
      'encoding' => 'utf8',
      /**
       * Database ユーザ名
       */
      'user' => '',
      /**
       * Database パスワード
       */
      'pass' => '',
      /**
       * Database name
       */
      'dbname' => 'database_name',
      /**
       * Database host
       */
      'host' => 'localhost',
      /**
       * Database port
       */
      'port'=> ''
    ),
    /**
     * debug設定
     *
     * 'system.debug' が true の場合に適応されます
     */
    'debug' => array(
      'engine' => '',
      'encoding' => 'utf8',
      'user' => '',
      'pass' => '',
      'dbname' => 'database_name',
      'host' => 'localhost',
      'port'=> ''
    ),
    /**
     * SQL クエリをログ保存するかどうか
     * ログは 'application/storage/logs' に保存されます
     */
    'logQuery' => true,
    /**
     * logQuery の最大クエリ数
     */
    'logQueryMax' => 200
  ),
  /**
   * Session settings
   */
  'session' => array(
    /**
     * セッションエンジン
     * "php", "file", "database" が利用可能です
     *
     * php: PHP デフォルトのエンジンを使用します
     * file: ファイルエンジンを使用します
     *  セッションは 'application/storage/sessions' に置かれます
     * database: database 項目で設定したデータベースを使用します
     *  テーブルスキーマの定義が必要です
     */
    'engine' => 'php',
    /**
     * セッションクッキー名
     * (PHPのデフォルトは 'PHPSESSID' となるもの)
     */
    'name' => 'sid',
    /**
     * セッションの有効期限 (秒数)
     *
     * default = 60*60*1 = 1時間
     */
    'timeout' => 60 * 60 * 1
  )
);
```

----

## コントローラ

ここではサンプルとして ToDo アプリケーションの例で解説します。

### コントローラの作成

`/application/controllers` に todo.php というファイル名で  
PunyApp_Controller を継承して TodoController を作成します。

コントローラのアクションは、GET や POST などのリクエストメソッドで切り分けができます。

```php
class TodoController extends PunyApp_Controller {

  /**
   * GET /item
   */
  public function getItem($params) {
    // GET リクエスト時の処理
  }

  /**
   * POST /item
   */
  public function postItem($params) {
    // POST リクエスト時の処理
  }

  /**
   * DELETE /item
   */
  public function deleteItem($params) {
    // DELETE リクエスト時の処理
  }
}
```

### メソッド名の付け方

コントローラのメソッド名は、`リクエストメソッド名` + `アクション名` になります。  
GET リクエストに対応する hoge アクションの場合、`getHoge` と定義します。  
アクション名は、リクエストメソッド名を外した名前です。

URL は `http://www.example.com/コントローラ名/アクション名` となり、 TodoController で getItem の場合は、`http://www.example.com/todo/item` になります。

大文字小文字は区別されません。

### before(前処理), after(後処理)

リクエストメソッド名の部分を `before` にすると前処理、`after` で後処理が設定できます。

```php
  /**
   * before /item
   */
  public function beforeItem($params) {
    // 前処理
  }

  /**
   * after /item
   */
  public function afterItem($params) {
    // 後処理
  }
```

### any (すべてのリクエストメソッド)

`any` は、すべてのリクエストメソッドに対応します。

```php
class TodoController extends PunyApp_Controller {

  /**
   * any /index
   */
  public function anyIndex($params) {
    // すべてのリクエストメソッドに対応する処理
  }
}
```

この例は `http://www.example.com/todo/index` にアクセスされたとき、  
GET や POST、DELETE などすべてのリクエストメソッドに対して実行されます。

メソッド名を any だけ `function any(){}` にすると、どんなアクションでも 404 にならずに any が実行されます。

コントローラ名を `AnyController` (any.php) という名前にすると、  
すべてのリクエストに対応するコントローラになります。

AnyController は `http://www.example.com/アクション名` となり、コントローラ名の部分が省かれます。

```php
class AnyController extends PunyApp_Controller {

  public function any($params) {
    // ...
  }
}
```

AnyController の中に any メソッドを定義すると、あらゆるリクエストに対して実行することになります。

### パラメータ

引数の $params は、リクエストされたパラメータが配列で渡されます。  
パラメータがない場合は空の配列が渡されます。  
このパラメータは、`$this->request->params->xxx` で取得できるものと同じです。

GET リクエストからのパラメータは、  
`http://www.example.com/コントローラ名/アクション名/1/2/3` のように渡すことができます。  
この場合、`$params = array(1, 2, 3)` となります。  
名前付きのパラメータは `/foo:1/bar:2` のように `:` で区切って渡します。  
これは、 `$params = array('foo' => 1, 'bar' => 2)` となります。

### beforeFilter, afterFilter, beforeRender

メソッド名を `beforeFilter` にすると、すべての処理の前に行う処理、  
`afterFilter` ですべての後に実行される処理が設定できます。  
`beforeRender` は `$this->view->render()` の前に実行されます。

```php
public function beforeFilter($params) {
  // 前処理
}

public function afterFilter($params) {
  // 後処理
}

public function beforeRender($params) {
  // renderの前の処理
}
```

## スキーマ

### テーブル定義

データベースのテーブル定義を作成します。  
事前にデータベースに定義しない場合、フレームワーク内で定義することができます。

`/application/settings/app-schema.php` に記述しておくと、一度だけ実行されます。  
再度実行したい場合は、`/application/storage/cache/app-cache.json` に保存されているキャッシュを削除します。

```php
$schema = array(
  // セッション
  "CREATE TABLE IF NOT EXISTS punyapp_sessions (
    id      varchar(128) NOT NULL default '',
    data    text,
    expires integer default NULL,
    PRIMARY KEY (id)
  )",

  // Todo テーブル
  "CREATE TABLE IF NOT EXISTS todo (
    id       integer,
    content  text,
    created  integer,
    modified integer,
    PRIMARY KEY (id)
  )"
);
```

### created, modified

テーブル定義にあらかじめ `created` もしくは `modified` を定義しておくと、 insert (save) や update のタイミングでそれぞれ現在時刻が自動で設定されます。

integer や int(11) で定義すると 現在の time() が秒数で設定されます。  
datetime は Y-m-d H:i:s 形式になります。  
varchar(255) で定義すると現在時刻をミリ秒で設定されます。

* **created** : 作成された日時
* **modified** : 更新された日時

これらはアプリケーション側で created, modified をパラメータに扱う場合は無視されます。

## モデル

### モデルの作成

モデルは `/application/models` の中に作成します。  
TodoModel の場合、todo.php というファイル名になります。  
PunyApp_Model を継承します。

モデルのクエリは基本的にプレースホルダ、プリペアドステートメントを使用します。  
add (insert) は、`$this->newInstance()` で新しいインスタンスを作ってデータを追加します。

```php
class TodoModel extends PunyApp_Model {

  /**
   * アイテムを追加
   * @param string $content
   * @return bool
   */
  public function addItem($content) {
    $todo = $this->newInstance();
    $todo->content = $content;
    return $todo->save();
  }

  /**
   * アイテムを削除
   * @param int $id
   * @return int affected rows
   */
  public function deleteItem($id) {
    return $this->delete(
      array('id' => '?'),
      array($id)
    );
  }

  /**
   * アイテムを取得
   * @param int $id
   * @return array
   */
  public function getItem($id) {
    return $this->findOne(
      array(
        'fields' => array('id', 'content'),
        'where' => array('id' => '?')
      ),
      array($id)
    );
  }

  /**
   * アイテムを全部取得
   * @return array
   */
  public function getItems() {
    return $this->find();
  }
}
```

### find メソッド

* _array_ **find** ( _array_ <span>$</span>query = array(), _array_ <span>$</span>params = array())

find() メソッドの引数 $query は、 ‘distinct’, ‘fields’, ‘from’, ‘as’, ‘joins’, ‘where’, ‘group’, ‘having’, ‘order’, ‘limit’, ‘offset’ が使えます。

```php
public function getUserByName($name) {
  return $this->find(
    array(
      'distinct' => false,
      'fields' => array(
        'U.id AS id', 'U.name AS name',
        'U.category AS cat', 'P.url AS url'
      ),
      'as' => 'U',
      'joins' => array(
        'type' => 'LEFT',
        'table' => 'profile',
        'as' => 'P',
        'on' => array('U.id' => 'P.id')
      ),
      'where' => array(
        'name' => ':name'
      ),
      'group' => 'cat',
      'order' => 'id DESC',
      'limit' => 10,
      'offset' => 5
    ),
    array(
      ':name' => $name
    )
  );
}
```

ある程度のクエリは扱えます。

### PDO として扱う

モデル内では `$this->getDatabase()` が PDO として使えるので、

```php
public function getName($id) {
  $statement = 'SELECT name FROM foo WHERE id = ?';
  $sth = $this->getDatabase()->prepare($statement);
  $sth->execute(array($id));
  return $sth->fetchAll(PDO::FETCH_ASSOC);
}
```

上のように直接クエリを書くことができますが、  
直接クエリを書いた場合、**created** と **modified** フィールドが自動セットされないので注意してください。

### コントローラでのモデル指定

コントローラでモデルを指定するには以下のように `$models` を配列で指定します。  
配列内のモデルがフィールドとしてインスタンス作成されます。

```php
class TodoController extends PunyApp_Controller {

  /**
   * @var array
   */
  public $models = array('todo'); // モデル名を入れる

  /**
   * @var TodoModel
   */
  public $todo;

  /**
   * POST /item
   */
  public function postItem($params) {
    if ($this->todo->addItem($params['content'])) {
      // ビューへのレンダリングなど
    }
  }
}
```

## ビュー

### ビューの作成

ビューは `/application/views/contents` 内に作成します。  
PHP そのままのテンプレートを使用します。

テンプレート変数はデフォルトで HTML エスケープされます。

```php
class TodoController extends PunyApp_Controller {  

  /**
   * @var array
   */
  public $models = array('todo');

  /**
   * @var TodoModel
   */
  public $todo;

  /**
   * any /index
   */
  public function anyIndex($params) {
    $this->view->title = 'ToDo';
    $this->view->items = $this->todo->getItems();
    $this->view->render('todo/index');
  }
}
```

views/todo/index.php

```html
<html>
  <body>
    <h1><?php echo $title ?></h1>
    <ul>
      <?php foreach ($items as $item): ?>
      <li><?php echo $item['content'] ?></li>
      <?php endforeach ?>
    </ul>
  </body>
</html>
```

## バリデーション

リクエストされたフォームのバリデーションを行います。  
バリデーションはコントローラで行います。

### 定義の作成

バリデーションの定義は、コントローラ内に `$validationRules` を配列で指定します。

```php
public $validationRules = array(
  'id' => array(
    'required' => true,
    'rule' => array('numeric'),
    'message' => 'idが不正です'
  ),
  'content' => array(
    'required' => true,
    'rule' => array(
      array('minLength', 1),
      array('maxLength', 50)
    ),
    'message' => '内容は1文字以上50文字以内で入力してください'
  )
);
```

バリデーションのルールは  
email, url, ip, numeric, between, minLength, maxLength, regex  
等が定義されています。

### 独自ルールの作成

自分でバリデーションルールを作成する場合は、コントローラ内に `validate` という接頭辞をつけてメソッドを定義します。

```php
class TodoController extends PunyApp_Controller {  

  public $validationRules = array(
    'content' => array(
      'required' => true,
      'rule' => array('MyContent'),
      'message' => '内容は1文字以上50文字以内で入力してください'
    )
  );

  /**
   * Custom validator
   *
   * @param mixed $value
   * @return bool
   */
  public function validateMyContent($value) {
    return (bool)preg_match('/^.{1,50}$/u', $value);
  }
}
```

### バリデーションを実行

バリデーションは、`$this->validate()` で実行します。

引数を省略すると $validationRules に対して実行します。  
任意のルールを配列で引数に渡すこともできます。

```php
class TodoController extends PunyApp_Controller {

  public $validationRules = array( ... )

  public function postItem($params) {
    if (!$this->validate()) {
      // invalid
    }
    $this->view->render('todo/index');
  }
}
```

### メッセージを取得

バリデーションエラーメッセージは以下のビューメソッドから取得できます。

* 名前を指定して取得:  
  _string_ **getValidationError** ( _string_ $name )

* 最後のメッセージを取得:  
  _string_ **getLastValidationError** ( )

* 全部取得:  
  _array_ **getValidationErrors** ( )

* HTML リスト要素で全部取得:  
  _string_ **getValidationErrorMessages** ( )

コントローラ内では `$this->view->getValidationError('hoge')` のように取得できます。

ビュー内では以下のように表示できます。

```html
<html>
  <body>
    <h1>Add ToDo</h1>
    <div class="error">
      <?php echo $this->getValidationError('content') ?>
    </div>
    <form action="item" method="post">
      <input type="text" name="content">
      <input type="submit">
    </form>
  </body>
</html>
```

## フォームトークン

CSRF 対策として、フォームリクエストのトークンが生成できます。

ビュー内の form の中に以下のように記述します。

```html
<html>
  <body>
    <h1>Add ToDo</h1>
    <form action="item" method="post">
      <input type="hidden" name="token" value="<?php
        echo $this->generateToken() ?>">
      <input type="text" name="content">
      <input type="submit">
    </form>
  </body>
</html>
```

コントローラ内で、トークンのバリデーションを行います。

```php
public function postItem($params) {
  if (!$this->token->validate($this->request->params->token)) {
    // 不正なリクエスト
    $this->view->error = 'Bad request';
  }
  // ...
}
```

## イベント

任意にアプリケーションイベントを設定します。  
イベントはどこからでも定義できますが、一番最初に実行される  
`/application/settings/app-initialize.php` に記述すると確実です。

```php
// データベースエラーを表示する
$this->event->on('app-database-error', function ($app, $error) {
  if ($app->isDebug()) {
    // デバッグモードのみ表示
    echo $app->escapeHTML($error);
  }
});
```

## インストールと実行

* 展開したファイルをサーバの任意のディレクトリに置きます
* `application/settings/app-settings.php` を編集してアプリケーションの設定をします
* データベーススキーマを作成するか、`application/settings/app-schema.php` に記述します
* `application/storage` 配下のファイルとディレクトリを「書き込み可」にします
* 最初に展開したディレクトリにブラウザでアクセスします

## サンプルコード

サンプルのログインフォームが `/sample/` に入っています。  
不要な場合は、

* /application/controllers
* /application/models
* /application/views/contents
* /application/public/css

配下のファイルまたはディレクトリをそれぞれ削除してください。
