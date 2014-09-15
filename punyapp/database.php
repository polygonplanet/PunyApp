<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Database
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Database_Error
 */
class PunyApp_Database_Error extends Exception {}

/**
 * @name PunyApp_Database_Settings
 */
class PunyApp_Database_Settings {

  /**
   * @return string dsn
   */
  public function getDSN() {
    $dsn = null;
    $props = array();

    foreach ($this as $key => $val) {
      switch ($key) {
        case 'user':
        case 'pass':
        case 'engine':
        case 'encoding':
          break;
        case 'dsn':
          $dsn = $val;
          break;
        default:
          if ($val != null) {
            $props[] = $key . '=' . $val;
          }
          break;
      }
    }

    if ($dsn != null) {
      return $dsn;
    }

    if ($this->engine == null) {
      return '';
    }

    return sprintf('%s:%s', $this->engine, implode(';', $props));
  }

  /**
   * Get engine
   *
   * @return string
   */
  public function getEngine() {
    $engine = null;

    if (isset($this->engine) && $this->engine != null) {
      $engine = $this->engine;
      $this->engine = null;
    }

    return (string)$engine;
  }

  /**
   * Get encoding
   *
   * @return string
   */
  public function getEncoding() {
    $encoding = null;

    if (isset($this->encoding) && $this->encoding != null) {
      $encoding = $this->encoding;
      $this->encoding = null;
    }

    return (string)$encoding;
  }

  /**
   * Get user
   *
   * @return string
   */
  public function getUser() {
    $user = null;

    if (isset($this->user) && $this->user != null) {
      $user = $this->user;
      $this->user = null;
    }

    return (string)$user;
  }

  /**
   * Get pass
   *
   * @return string
   */
  public function getPass() {
    $pass = null;

    if (isset($this->pass) && $this->pass != null) {
      $pass = $this->pass;
      $this->pass = null;
    }

    return (string)$pass;
  }
}

/**
 * @name PunyApp_Database
 */
class PunyApp_Database {

  /**
   * @const string database filename
   */
  const DATABASE_FILENAME = 'app-database';

  /**
   * @var PunyApp
   */
  public $app = null;

  /**
   * @var string
   */
  public $driver = null;

  /**
   * @var PDO
   */
  private $_db = null;

  /**
   * @var string
   */
  private $_dbfile = null;

  /**
   * Connect database with DSN
   *
   * @param PunyApp $app
   */
  public function __construct(PunyApp $app) {
    $this->app = $app;

    $dsn = $this->app->databaseSettings->getDSN();
    $engine = $this->app->databaseSettings->getEngine();
    $user = $this->app->databaseSettings->getUser();
    $pass = $this->app->databaseSettings->getPass();
    $options = array();

    if ($dsn == null) {
      return;
    }

    if (!preg_match('/^([^:]+):(.*)$/', $dsn, $m)) {
      throw new PunyApp_Database_Error('Invalid DSN');
    }

    $this->driver = strtolower($m[1]);
    $this->_dbfile = PUNYAPP_DATABASES_DIR . DIRECTORY_SEPARATOR . self::DATABASE_FILENAME;

    switch ($this->driver) {
      case 'posql':
        $this->_dbfile .= sprintf('.%s', $this->driver);
        $this->_db = new Posql($this->_dbfile);
        break;
      case 'sqlite':
        $this->_dbfile .= sprintf('.%s', $this->driver);
        $dsn = 'sqlite:' . $this->_dbfile;
        $this->_db = new PDO($dsn, null, null, $options);
        break;
      case 'mysql':
        $this->_dbfile = null;
        $encoding = $this->app->databaseSettings->getEncoding();
        if ($encoding != null) {
          $encoding = preg_replace('/\W/', '', $encoding);
          $options[PDO::MYSQL_ATTR_INIT_COMMAND] = sprintf('SET NAMES %s', $encoding);
        }
        $this->_db = PunyApp::getInstance('PDO', $dsn, $user, $pass, $options);
        break;
      default:
        $this->_dbfile = null;
        $this->_db = PunyApp::getInstance('PDO', $dsn, $user, $pass, $options);
        break;
    }

    if ($this->isError()) {
      throw new PunyApp_Database_Error($this->getLastError());
    }
  }


  /**
   * Prepare
   *
   * @param string $statement
   * @param array $driver_options = array()
   * @return PunyApp_Database_Statement
   */
  public function prepare() {
    $args = func_get_args();
    $stmt = call_user_func_array(array($this->_db, 'prepare'), $args);
    return new PunyApp_Database_Statement($this, $stmt);
  }

  /**
   * Query
   *
   * @param string $statement
   * @return PunyApp_Database_Statement
   */
  public function query() {
    $args = func_get_args();
    $stmt = call_user_func_array(array($this->_db, 'query'), $args);
    $this->_assignError();
    return new PunyApp_Database_Statement($this, $stmt);
  }

  /**
   * Exec
   *
   * @param string $statement
   * @return PunyApp_Database_Statement
   */
  public function exec() {
    $args = func_get_args();
    $result = call_user_func_array(array($this->_db, 'exec'), $args);
    $this->_assignError();
    return $result;
  }

  /**
   * Check error
   *
   * @return boolean
   */
  public function isError() {
    if ($this->driver === 'posql') {
      return $this->_db->isError();
    }

    $code = $this->_db->errorCode();
    if ($code == null || $code === '00000') {
      return false;
    }

    return true;
  }

  /**
   * Get error message
   *
   * @return string
   */
  public function getLastError() {
    if ($this->driver === 'posql') {
      $error = $this->_db->lastError();
      if ($error != null) {
        $this->_db->getErrors();
        $this->_db->pushError($error);
      }
      return $error;
    }

    $info = $this->_db->errorInfo();
    if (empty($info) || !array_key_exists(2, $info)) {
      return '';
    }

    return $info[2];
  }


  /**
   * Assign error
   */
  private function _assignError() {
    if ($this->isError()) {
      $this->app->event->trigger('app-database-error', array($this->getLastError()));
    }
  }


  public function __call($func, $args) {
    return call_user_func_array(array($this->_db, $func), $args);
  }
}

/**
 * @name PunyApp_Database_Statement
 */
class PunyApp_Database_Statement {

  /**
   * @var PunyApp_Database
   */
  private $_db;

  /**
   * @var PDO_Statement
   */
  private $_stmt;

  /**
   * Constructor
   *
   * @param PunyApp_Database $db
   * @param PDO_Statement $stmt
   */
  public function __construct($db, $stmt) {
    $this->_db = $db;
    $this->_stmt = $stmt;
    if (!$this->_stmt) {
      throw new PunyApp_Database_Error('Cannot execute statement');
    }
  }

  /**
   * Bind column
   *
   * @param mixed $column
   * @param mixed $param
   * @param int $type
   * @return boolean
   */
  public function bindColumn($column, &$param, $type = null) {
    if ($type === null) {
      return $this->_stmt->bindColumn($column, $param);
    }
    return $this->_stmt->bindColumn($column, $param, $type);
  }

  /**
   * Bind param
   *
   * @param mixed $param
   * @param mixed &$var
   * @param int $type
   * @return bool
   */
  public function bindParam($param, &$var, $type = null) {
    if ($type === null) {
      return $this->_stmt->bindParam($param, $var);
    }
    return $this->_stmt->bindParam($param, $var, $type);
  }

  /**
   * Bind value
   *
   * @param mixed $parameter
   * @param mixed $value
   * @param int $data_type
   */
  public function bindValue($parameter, $value, $data_type = null) {
    if ($data_type === null) {
      return $this->_stmt->bindValue($parameter, $value);
    }
    return $this->_stmt->bindValue($parameter, $value, $data_type);
  }

  /**
   * Execute
   *
   * @param array $input_parameters = array()
   * @return boolean
   */
  public function execute() {
    $args = func_get_args();
    $result = call_user_func_array(array($this->_stmt, 'execute'), $args);
    $this->_assignError();
    return $result;
  }


  /**
   * Assign error
   */
  private function _assignError() {
    if ($this->_db->isError()) {
      $this->_db->app->event->trigger('app-database-error', array($this->_db->getLastError()));
    }
  }


  public function __call($func, $args) {
    return call_user_func_array(array($this->_stmt, $func), $args);
  }


  public function __get($property) {
    return $this->_stmt->{$property};
  }
}
