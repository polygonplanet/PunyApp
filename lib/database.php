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
 * @link       http://polygonpla.net/
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
   * @param PunyApp_Database_Settings $settings
   */
  public function __construct(PunyApp_Database_Settings $settings) {
    $dsn = $settings->getDSN();
    $engine = $settings->getEngine();
    $user = $settings->getUser();
    $pass = $settings->getPass();
    $options = array();

    if ($dsn != null) {
      if (!preg_match('/^([^:]+):(.*)$/', $dsn, $m)) {
        throw new PunyApp_Database_Error('Invalid DSN');
      }

      $driver = strtolower($m[1]);
      $this->_dbfile = PUNYAPP_DATABASES_DIR . DIRECTORY_SEPARATOR . self::DATABASE_FILENAME;

      switch ($driver) {
        case 'posql':
          $this->_dbfile .= '.' . $driver;
          $this->_db = new Posql($this->_dbfile);
          $this->_dbfile = $this->_db->getPath();
          break;
        case 'sqlite':
          $this->_dbfile .= '.' . $driver;
          $dsn = 'sqlite:' . $this->_dbfile;
          $this->_db = new PDO($dsn, null, null, $options);
          break;
        default:
          $this->_dbfile = null;
          $this->_db = new PDO($dsn, $user, $pass, $options);
          break;
      }
    }
  }


  public function __call($func, $args) {
    return call_user_func_array(array($this->_db, $func), $args);
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
    return call_user_func_array(array($this->_db, 'exec'), $args);
  }

  /**
   * Check error
   *
   * @return boolean
   */
  public function isError() {
    if ($this->_db instanceof Posql) {
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
    if ($this->_db instanceof Posql) {
      return $this->_db->lastError();
    }

    $info = $this->_db->errorInfo();
    if (empty($info) || !array_key_exists(2, $info)) {
      return '';
    }

    return $info[2];
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
  }


  public function __call($func, $args) {
    return call_user_func_array(array($this->_stmt, $func), $args);
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
    return call_user_func_array(array($this->_stmt, 'execute'), $args);
  }


  public function __get($property) {
    return $this->_stmt->{$property};
  }
}
