PunyApp
=======

[![Build Status](https://travis-ci.org/polygonplanet/PunyApp.svg?branch=master)](https://travis-ci.org/polygonplanet/PunyApp)

PunyApp is a lightweight MVC PHP framework.  
PunyApp requires PHP 5.2.0 or newer.  
It does not require the external PHP extensions.  


### Supported Databases

* MySQL
* SQLite
* Posql *[GitHub](https://github.com/polygonplanet/Posql) [(English)](http://feel.happy.nu/doc/posql/en/)


### Tutorial

#### layout

Application directory layout:  

    /app                  -> Application
      /controllers        -> App controllers
      /models             -> App models
      /views              -> App views
      /storage            -> A storage of the filebase database
      /settings           -> Application settings
        app-settings.json
        app-scheme.php
      /public             -> public web
        /css
        /js
        index.php
    /lib                  -> PunyApp library
    /vendors              -> Venders
    .htaccess
    index.php
 

### Controllers

```php
class SampleController extends PunyApp_Controller {

  /**
   * Before /login
   */
  public function beforeLogin() {
    if (!empty($this->session->userId)) {
      $this->redirect('home');
    }
  }

  /**
   * GET /login
   */
  public function getLogin() {
    $this->view->render('sample/login');
  }

  /**
   * POST /login
   */
  public function postLogin() {
    if (!empty($this->session->userId)) {
      $this->redirect('home');
    }
    $is_user = $this->models->sample->isUser(
      $this->request->params->id,
      $this->request->params->pass
    );
    if ($is_user) {
      $this->session->userId = $this->request->params->id;
      $this->redirect('home');
    }
  }
}
```

#### Validation
```php
  public $validationRules = array(
    'id' => array(
      'required' => true,
      'rule' => array('regex', '/^[a-z0-9]{1,10}$/i'),
      'message' => 'Only letters and integers, max 10 characters'
    ),
    'email' => array(
      'required' => true,
      'rule' => array('email'),
      'message' => 'Invalid email address'
    ),
    'pass' => array(
      'required' => true,
      'rule' => array(
        array('minLength', 4),
        array('maxLength', 20)
      ),
      'message' => 'Min 4 characters, max 20 characters'
    )
  );

```

### Models

```php
class SampleModel extends PunyApp_Model {

  public function addUser($user_id, $email, $pass) {
    return $this->insert(array(
      'userId' => ':userId',
      'email' => ':email',
      'pass' => ':pass'
    ), array(
      ':userId' => $user_id,
      ':email' => $email,
      ':pass' => sha1($pass)
    ));
  }


  public function deleteUser($user_id) {
    return $this->delete(array('userId' => '?'),
                         array($user_id));
  }


  public function getUser($user_id) {
    return $this->findOne(
      array('id', 'userId', 'email'),
      array('userId' => '?'),
      array($user_id)
    );
  }


  public function isUserId($user_id) {
    return $this->count(array('userId' => '?'),
                        array($user_id)) > 0;
  }
}

```

### Views

```php
$this->view->set('title', 'PunyApp');
$this->view->set('description', 'The puny developer framework for rapid compiling.');
$this->view->render();
```

Use pure PHP template.

```php
<h1><?php echo $title ?></h1>
<p><?php echo $description ?></p>
```

### Settings

* app/settings/app-settings.json

```javascript
{
  "system": {
    "debug": true, // debug mode
    "lang": "ja", // or "en" etc.
    "charset": "utf-8",
    "timezone": "Asia/Tokyo", // or 'America/Chicago' etc.
    // Application security salt
    // Enter something characters. symbols is possible.
    "salt": "ZQJaiPPYn6Tldb2gottKwIDmGiatuSnV"
  },
  "database": {
    "engine": "sqlite", // or "mysql", "posql"
    "encoding": "utf8",
    "user": "",
    "pass": "",
    "dbname": "database_name",
    "host": "localhost",
    "port": ""
  },
  "session": {
    "engine": "sqlite", // or "mysql", "posql"
    "name": "sessid", // PHPSESSID
    "expirationDate": 365
  }
}
```

For SQLite, set to writable following files.  

 * app/storage/databases/app-database.sqlite
 * app/storage/sessions/app-session.sqlite

### Sample

* There is a sample login form in `/sample/`.

----

#### Use Posql

Posql documents [(Japanese)](http://feel.happy.nu/doc/posql/) [(English)](http://feel.happy.nu/doc/posql/en/)  

1. [Download](https://github.com/polygonplanet/Posql/tree/master/posql-2.18a) latest `posql.php`

2. Put `posql.php` in `/vendors` directory.
3. Requires posql in `app/settings/app-initialize.php`

```php
PunyApp::uses('posql', 'vendors');
```

4. Settings `app/settings/app-settings.json`  

```javascript
{
  ...
  "database": {
    "engine": "posql",
    ...
  },
  "session": {
    "engine": "posql",
    ...
  }
}
```

After accessed to the application `/sample/`, set to writable following files.  

 * app/storage/databases/app-database.posql.php
 * app/storage/sessions/app-session.posql.php

#### posqladmin

1. Download latest posqladmin [(Japanese)](https://github.com/polygonplanet/Posql/tree/master/posql-2.18a) [(English)](http://feel.happy.nu/doc/posql/en/)
2. Put `posqladmin.php` in public directory.
3. Set to writable `posqladmin.php` (e.g., `0666` permissions).
4. Access `posqladmin.php`.
5. Set id and pass in settings.

### License

The PunyApp is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)


