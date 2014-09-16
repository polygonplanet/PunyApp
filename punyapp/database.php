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
        case 'logQuery':
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
    }

    return (string)$engine;
  }

  /**
   * Get logQuery
   *
   * @return bool
   */
  public function getLogQuery() {
    $log_query = null;

    if (isset($this->logQuery)) {
      $log_query = $this->logQuery;
    }

    return (bool)$log_query;
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
   * @const string queries log name
   */
  const QUERIES_LOG_NAME = 'app-queries';

  /**
   * @var PunyApp
   */
  public $app = null;

  /**
   * @var PDO
   */
  private $_db = null;

  /**
   * @var PunyApp_Database_Common
   */
  public $driver = null;

  /**
   * @var string
   */
  public $driverName = null;

  /**
   * @var PunyApp_Log
   */
  private $_log = null;

  /**
   * @var string
   */
  private $_dbfile = null;

  /**
   * @var bool whether to log SQL query
   */
  private $_storeQueriesLog = array();

  /**
   * @var array queries log
   */
  private $_queriesLog = array();

  /**
   * @var int queries log max
   */
  private $_queriesLogMax = 200;

  /**
   * Connect database with DSN
   *
   * @param PunyApp $app
   */
  public function __construct(PunyApp $app) {
    $this->app = $app;
    $this->_log = new PunyApp_Log(self::QUERIES_LOG_NAME, $this->_queriesLogMax, true);
    $this->_storeQueriesLog = $this->app->databaseSettings->getLogQuery();
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

    $database_dir = PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'database';
    $common = $database_dir . DIRECTORY_SEPARATOR . 'common.php';
    require_once $common;

    $this->driverName = strtolower($m[1]);
    $driver = $database_dir . DIRECTORY_SEPARATOR . $this->driverName . '.php';
    if (!file_exists($driver)) {
      throw new PunyApp_Database_Error("Driver '{$driver}' is not defined");
    }
    require_once $driver;

    $this->_dbfile = PUNYAPP_DATABASES_DIR . DIRECTORY_SEPARATOR . self::DATABASE_FILENAME;

    switch ($this->driverName) {
      case 'posql':
        $this->_dbfile .= sprintf('.%s', $this->driverName);
        $this->_db = new Posql($this->_dbfile);
        break;
      case 'sqlite':
        $this->_dbfile .= sprintf('.%s', $this->driverName);
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

    $driver_class = sprintf('PunyApp_Database_%s', ucfirst($this->driverName));
    $this->driver = new $driver_class($this);

    if ($this->isError()) {
      throw new PunyApp_Database_Error($this->getLastError());
    }
  }

  /**
   * Destructor
   */
  public function __destruct() {
    $this->_saveLogs();
  }

  /**
   * Prepare statement
   *
   * @param string $statement
   * @param array $driver_options = array()
   * @return PunyApp_Database_Statement
   */
  public function prepare($statement) {
    $args = func_get_args();
    $stmt = call_user_func_array(array($this->_db, 'prepare'), $args);
    return new PunyApp_Database_Statement($this, $stmt, $statement);
  }

  /**
   * Execute query
   *
   * @param string $statement
   * @return PunyApp_Database_Statement
   */
  public function query($statement) {
    $args = func_get_args();
    $stmt = call_user_func_array(array($this->_db, 'query'), $args);
    $this->logQuery($statement);
    $this->_assignError();
    return new PunyApp_Database_Statement($this, $stmt, $statement);
  }

  /**
   * Execute statement
   *
   * @param string $statement
   * @return PunyApp_Database_Statement
   */
  public function exec($statement) {
    $args = func_get_args();
    $result = call_user_func_array(array($this->_db, 'exec'), $args);
    $this->logQuery($statement, array(), $result);
    $this->_assignError();
    return $result;
  }

  /**
   * Return last inserted id
   *
   * @param string $name
   * @return string
   */
  public function lastInsertId($name = null) {
    $args = func_get_args();
    return call_user_func_array(array($this->_db, 'lastInsertId'), $args);
  }

  /**
   * Check error
   *
   * @return boolean
   */
  public function isError() {
    if ($this->driverName === 'posql') {
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
    if ($this->driverName === 'posql') {
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
   * Get queries log
   *
   * @return array
   */
  public function getLogs() {
    return $this->_queriesLog;
  }

  /**
   * Log query
   *
   * @param string $statement
   * @param array $params
   * @param int $affected
   */
  public function logQuery($statement, $params = array(), $affected = null) {
    $this->_queriesLog[] = array(
      'query' => $statement,
      'params' => $params,
      'affected' => $affected,
      'time' => PunyApp::now()
    );

    if (count($this->_queriesLog) > $this->_queriesLogMax) {
      array_shift($this->_queriesLog);
    }
  }

  /**
   * Save SQL query logs
   */
  private function _saveLogs() {
    if ($this->_storeQueriesLog && !empty($this->_queriesLog)) {
      $messages = array();
      foreach ($this->_queriesLog as $log) {
        $messages[] = array(
          'message' => $log,
          'time' => $log['time']
        );
      }
      $this->_log->write($messages, true);
    }
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
  private $_db = null;

  /**
   * @var PDO_Statement
   */
  private $_stmt = null;

  /**
   * @var string statement
   */
  private $_query = null;

  /**
   * Constructor
   *
   * @param PunyApp_Database $db
   * @param PDO_Statement $stmt
   * @param string $query
   */
  public function __construct($db, $stmt, $query) {
    $this->_db = $db;
    $this->_stmt = $stmt;
    $this->_query = $query;
    if (!$this->_stmt) {
      throw new PunyApp_Database_Error($this->_db->getLastError());
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
  public function execute($input_parameters = array()) {
    $args = func_get_args();
    $result = call_user_func_array(array($this->_stmt, 'execute'), $args);
    $this->_db->logQuery($this->_query, $input_parameters, $this->rowCount());
    $this->_assignError();
    return $result;
  }

  /**
   * Returns the number of rows affected by the last SQL statement
   *
   * @return int
   */
  public function rowCount() {
    return $this->_stmt->rowCount();
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
