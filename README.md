PunyApp
=======

[![Build Status](https://travis-ci.org/polygonplanet/PunyApp.svg?branch=master)](https://travis-ci.org/polygonplanet/PunyApp)

PunyApp is a lightweight MVC PHP framework.  
PunyApp requires PHP 5.2.0 or newer.  
It does not require the external PHP extensions.  


### Supported Databases

* MySQL
* SQLite
* [Posql](https://github.com/polygonplanet/Posql) [(documents)](http://feel.happy.nu/doc/posql/en/)


### Tutorial

#### layout

Application directory layout:  

    /app                  -> Application
      /controllers        -> App controllers
      /models             -> App models
      /views              -> App views
      /storage            -> A storage of the filebase database
      /settings           -> Application settings
        app-settings.php
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
   * GET /login
   */
  public function getLogin() {
    $this->view->render('sample/login');
  }

  /**
   * POST /login
   */
  public function postLogin() {
    $is_user = $this->models->sample->isUser(
      $this->request->params->id,
      $this->request->params->pass
    );
    if ($is_user) {
      $this->session->userId = $this->request->params->id;
      $this->redirect('home');
    }

    // ...

    $this->view->render('sample/login');
  }

  /**
   * Any /login
   */
  public function anyLogin() {
    // ...
  }

  /**
   * Before /login
   */
  public function beforeLogin() {
    if (!empty($this->session->userId)) {
      $this->redirect('home');
    }
  }

  /**
   * After /login
   */
  public function afterLogin() {
    // ...
  }

  /**
   * GET /home
   */
  public function getHome() {
    // ...
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

The template variables already escaped for HTML entities.  

```php
<h1><?php echo $title ?></h1>
<p><?php echo $description ?></p>
```

### Settings

* app/settings/app-settings.php

```php
$settings = array(
  /**
   * System settings
   */
  'system' => array(

    /**
     * Debug mode
     *
     * true = show errors
     * false = hide errors
     */
    'debug' => true,

    /**
     * internal character-code
     *
     * default = utf-8
     */
    'charset' => 'utf-8',

    ...
  ),

  /**
   * Database settings
   */
  'database' => array(

    /**
     * Database engine
     *
     * Available engines: "mysql", "sqlite" and "posql".
     */
    'engine' => 'sqlite',

    ...
  ),

  /**
   * Session settings
   */
  'session' => array(

    /**
     * Session engine
     *
     * Available engines: "mysql", "sqlite" and "posql".
     */
    'engine' => 'sqlite',

    ...
  )
);


```

For SQLite, set to writable following files.  

 * app/storage/databases/app-database.sqlite
 * app/storage/sessions/app-session.sqlite

### Events

```php
// Example events
$this->event->on('app-initialize', function ($app) {});
$this->event->on('app-database-error', function ($app, $error) {});
$this->event->on('app-before-validate', function ($app, $rules = array()) {});
$this->event->on('app-before-redirect', function ($app, $url) {});
$this->event->on('app-before-render', function ($app, $template) {});
$this->event->on('app-after-render', function ($app, $template) {});
$this->event->on('app-before-render-error', function ($app, $code) {});
$this->event->on('app-after-render-error', function ($app, $code) {});
$this->event->on('app-before-filter', function ($app, $params = array()) {});
$this->event->on('app-after-filter', function ($app, $params = array()) {});
```

#### Handle Error

```php
// Handle the database error
$this->event->on('app-database-error', function ($app, $error) {
  if ($app->getDebug()) {
    // Show error message only in debug mode
    echo '<div style="color:red">', $app->escapeHTML($error), '</div>';
  }
});
```


### Sample

* There is a sample login form in `/sample/`.

----

### License

The PunyApp is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)


