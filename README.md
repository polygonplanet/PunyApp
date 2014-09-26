PunyApp
=======

[![Build Status](https://travis-ci.org/polygonplanet/PunyApp.svg?branch=master)](https://travis-ci.org/polygonplanet/PunyApp)

PunyApp is a lightweight MVC PHP framework that does not require the external PHP extensions, it's based the CakePHP framework.  
  
Requires PHP 5.2.0 or newer.  


### Supported Databases

* MySQL
* PostgreSQL
* SQLite
* [Posql](https://github.com/polygonplanet/Posql)


### Tutorial

#### layout

Application directory layout:  

    /application
      /controllers        -> App controllers
      /models             -> App models
      /views              -> App views
      /libraries          -> App libraries
      /storage            -> App storage
      /settings           -> Application settings
        app-settings.php
        app-scheme.php
      /public             -> public web
        /css
        /js
        index.php
    /punyapp              -> PunyApp libraries
    /vendors              -> Vendors
    index.php
 

### Controllers

```php
class SampleController extends PunyApp_Controller {

  public $models = array('sample');

  /**
   * GET /login
   */
  public function getLogin($params) {
    $this->view->render('sample/login');
  }

  /**
   * POST /login
   */
  public function postLogin($params) {
    $has = $this->sample->hasUser($params['id'], $params['pass']);
    if ($has) {
      $this->session->userid = $params['id'];
      $this->redirect('home');
    }

    // ...
  }

  /**
   * Any /login
   */
  public function anyLogin($params) {
    // ...
  }

  /**
   * Before /login
   */
  public function beforeLogin($params) {
    if (!empty($this->session->userid)) {
      $this->redirect('home');
    }
  }

  /**
   * After /login
   */
  public function afterLogin($params) {
    // ...
  }

  /**
   * GET /home
   */
  public function getHome($params) {
    if (empty($this->session->userid)) {
      $this->redirect('login');
    }

    $this->view->user = $this->sample->getUser($this->session->userid);
    $this->view->render('sample/home');
  }

  /**
   * GET /register
   */
  public function getRegister($params) {
    $this->view->render('sample/register');
  }

  /**
   * POST /register
   */
  public function postRegister($params) {
    if ($this->validate()) {
      $this->sample->addUser($params['id'], $params['email'], $params['pass']);
      $this->session->userid = $params['id'];
      $this->redirect('home');
    }
    $this->view->render('sample/register');
  }
}
```

### Models

Using Prepared Statements.  


```php
class SampleModel extends PunyApp_Model {

  public function addUser($userid, $email, $pass) {
    $sample = $this->newInstance();
    $sample->userid = $userid;
    $sample->email = $email;
    $sample->pass = sha1($pass);
    return $sample->save();
  }


  public function deleteUser($userid) {
    return $this->delete(
      array('userid' => '?'),
      array($userid)
    );
  }


  public function getUser($userid) {
    return $this->findOne(
      array(
        'fields' => array('id', 'userid', 'email'),
        'where' => array('userid' => '?')
      ),
      array($userid)
    );
  }


  public function hasUser($userid, $pass) {
    return $this->has(
      array(
        'where' => array(
          'userid' => ':userid',
          'pass' => ':pass'
        )
      ),
      array(
        ':userid' => $userid,
        ':pass' => sha1($pass)
      )
    );
  }
}


```

### Views

Using pure PHP template.  

The template variables is escaped for HTML entities by default.  


```php
$this->view->text = 'Hello!';
$this->view->render('index');
```

views/index.php  

```php
<html>
  <body>
    <h1>Sample</h1>
    <?php echo $text; ?>
  </body>
</html>
```

### Events

Handle application events, or define yourself.  

```php
// Handle the database error
$this->event->on('app-database-error', function ($app, $error) {
  if ($app->isDebug()) {
    // Show error message only in debug mode
    echo $error;
  }
});
```


### Validation

Request Form Validation.  

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


### Install and Run

* Extract files to the any directory on the server.

* Settings `application/settings/app-settings.php`.

```php
$settings = array(
  /**
   * System settings
   */
  'system' => array(
    /**
     * Timezone
     *
     * e.g., 'America/Chicago', 'Asia/Tokyo' etc.
     */
    'timezone' => '',
  ),

  /**
   * Database settings
   */
  'database' => array(

    /**
     * Database engine
     *
     * Available engines: "mysql", "pgsql", "sqlite" and "posql".
     */
    'engine' => '',
  ),

  /**
   * Session settings
   */
  'session' => array(

    /**
     * Session engine
     *
     * Available engines: "php", "file" and "database".
     */
    'engine' => '',
  )
);
```

*  Create database schema or write schema in  `application/settings/app-schema.php`.

* Set to writable in the directories and files under `application/storage`.

* Browse the first you files extracted directory.  

### Sample

* There is a sample login form in `/sample/`.

----

### License

The PunyApp is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)



