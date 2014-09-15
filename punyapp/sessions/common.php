<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Session
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Session_Common
 */
class PunyApp_Session_Common {

  /**
   * @var object database
   */
  protected $_db = null;

  /**
   * @var string the session tablename
   */
  protected $_tableName = 'punyapp_sessions';

  /**
   * @var string database creation schema
   */
  protected $_schema = "CREATE TABLE IF NOT EXISTS %s (
    id       varchar(255) NOT NULL default '',
    data     text,
    expire   integer default NULL,
    updateAt integer default NULL,
    PRIMARY KEY (id)
  )";

  /**
   * @var bool maintains whether the session is started
   */
  protected $_started = false;

  /**
   * @var bool maintains whether the database was vacuumed
   */
  protected $_vacuumed = false;

  /**
   * @var bool maintains whether the database is able to vacuum
   */
  protected $_vacuumble = false;

  /**
   * @var bool maintains whether the instance is initialized
   */
  protected $_initialized = false;

  /**
   * @var bool maintains whether the session is closed
   */
  protected $_closed = false;

  /**
   * @var string session filename
   */
  public static $sessionFileName = null;

  /**
   * @var string session class name
   */
  public static $className = null;

  /**
   * Constructor
   */
  public function __construct() {
  }

  /**
   * Set session filename
   *
   * @param string session filename
   */
  public static function setClassName($classname) {
    self::$className = $classname;
  }

  /**
   * Set session filename
   *
   * @param string session filename
   */
  public static function setFileName($filename) {
    self::$sessionFileName = $filename;
  }

  /**
   * Create Database instance
   */
  public static function createDatabaseInstance() {
    throw new PunyApp_Error('abstract');
  }

  /**
   * Initialize the session with some settings
   *
   * @param  string   session name (i.e. 'PHPSESSID')
   * @param  string   session database path (i.e. database filename)
   * @param  string   session table name
   * @param  number   session lifetime (e.g. (60 * 60 * 24 * 365) = 1 year)
   * @param  string   session cookie path (e.g. '/path/to/myproject/')
   * @param  bool  whether connected with HTTPS
   */
  public static function init($sess_name,
                              $save_path,
                              $table_name = null,
                              $max_lifetime = 0,
                              $cookie_path = '/',
                              $is_https = false) {
    $self = PunyApp::getInstance(self::$className);

    if (!empty($self->_initialized)) {
      return;
    }

    $self->_initialized = true;
    call_user_func(array(self::$className, 'commit'));

    $self->_db = call_user_func(array(self::$className, 'createDatabaseInstance'));
    session_save_path(self::$sessionFileName);

    if ($table_name == null) {
      $table_name = $self->getTableName();
    }
    $self->setTableName((string)$table_name);

    $sql = sprintf($self->_schema, $self->getTableName());
    $self->_db->exec($sql);
    if ($self->isError()) {
      throw new PunyApp_Database_Error($self->getLastError());
    }
    $self->_schema = null;

    if ($is_https) {
      @ini_set('session.cookie_secure', 1);
    }

    @ini_set('session.use_trans_sid', 0);
    @ini_set('url_rewriter.tags', '');
    @ini_set('session.save_handler', 'user');
    @ini_set('session.serialize_handler', 'php');
    @ini_set('session.use_cookies', 1);
    @ini_set('session.name', $sess_name);

    // Settings for the Garbage Collector
    @ini_set('session.gc_probability', 1);

    // GC maybe works by 1/3 probability
    @ini_set('session.gc_divisor', 100);

    // Set max lifetime: => x days
    @ini_set('session.gc_maxlifetime', $max_lifetime);
    @ini_set('session.cookie_lifetime', $max_lifetime);
    @ini_set('session.cookie_path', $cookie_path);
    @ini_set('session.auto_start', 0);

    session_set_save_handler(
      array(__CLASS__, 'open'),
      array(__CLASS__, 'close'),
      array(__CLASS__, 'read'),
      array(__CLASS__, 'write'),
      array(__CLASS__, 'destroy'),
      array(__CLASS__, 'gc')
    );
    register_shutdown_function(array(__CLASS__, 'commit'));
  }

  /**
   * Commit and close session
   *
   * @param  void
   * @return void
   */
  public static function commit() {
    if (function_exists('session_write_close')) {
      session_write_close();
    }
  }

  /**
   * Starts the session with some settings
   *  and creates the instance of the class
   *
   * @return bool success or failure
   */
  public static function start() {
    $result = false;
    $self = PunyApp::getInstance(self::$className);

    if (empty($self->_initialized)) {
      $result = false;
    } else if (empty($self->_started)) {
      if (headers_sent()) {
        if (empty($_SESSION)) {
          $_SESSION = array();
        }
        $result = false;
      } else if (!isset($_SESSION)) {
        //FIXME: "must-revalidate" available?
        session_cache_limiter('must-revalidate');
        session_start();

        // UNI : uniqueid
        // ADM : admin
        // COM : computer
        // OUR : ours
        PunyApp::header('set', 'P3P', "CP='UNI ADM COM OUR'");

        if (!is_array($_SESSION)) {
          $_SESSION = array();
        }
        $result = true;
      } else {
        session_start();
        $result = true;
      }
      $self->_started = true;
    }

    return $result;
  }

  /**
   * Check whether the session was started
   *
   * @return bool  TRUE started, FALSE not started
   */
  public static function isStarted() {
    $result = false;
    $self = PunyApp::getInstance(self::$className);

    if (!empty($self->_started)) {
      $result = true;
    }

    return $result;
  }

  /**
   * Get the tablename as session storage
   *
   * @return string   the tablename as session storage
   */
  public static function getTableName() {
    $self = PunyApp::getInstance(self::$className);
    $result = $self->_tableName;
    return $result;
  }

  /**
   * Set the tablename as session storage
   *
   * @param  string   the table name
   * @return bool  success or failure
   */
  public static function setTableName($tablename) {
    $result = false;
    $self = PunyApp::getInstance(self::$className);

    if (is_string($tablename)) {
      $self->_tableName = $tablename;
      $result = true;
    }

    return $result;
  }

  /**
   * Check database error
   *
   * @return bool
   */
  public static function isError() {
    $result = false;
    $self = PunyApp::getInstance(self::$className);

    $error_code = $self->_db->errorCode();
    if ($error_code != null && $error_code !== '00000') {
      $result = true;
    }

    return $result;
  }

  /**
   * Get error message
   *
   * @return string
   */
  public static function getLastError() {
    $self = PunyApp::getInstance(self::$className);

    if (is_callable(array($self->_db, 'lastError'))) {
      return $self->_db->lastError();
    }

    $info = $self->_db->errorInfo();
    if (empty($info) || !array_key_exists(2, $info)) {
      return '';
    }

    return $info[2];
  }

  /**
   * Open the session
   *
   * @param  string   file path of the session as the database
   * @param  string   session name
   * @return bool  success or failure
   */
  public static function open($save_path, $name) {
    return true;
  }

  /**
   * Close the session
   *
   * @param  void
   * @return bool  success or failure
   * @access public
   */
  public static function close() {
    $result = true;
    $self = PunyApp::getInstance(self::$className);

    $max_lifetime = @ini_get('session.gc_maxlifetime');
    if (is_numeric($max_lifetime) && mt_rand(1, 150) <= 3) {
      $self->gc($max_lifetime);
    }

    return $result;
  }

  /**
   * Read the session
   *
   * @param  string  session id
   * @return string  sessoin data
   */
  public static function read($id) {
    $result = '';
    $self = PunyApp::getInstance(self::$className);

    $sql = sprintf(
      'SELECT data FROM %s WHERE id = ?',
      $self->getTableName()
    );
    $stmt = $self->_db->prepare($sql);
    $stmt->execute(array($id));
    $result = (string)$stmt->fetchColumn(0);

    if ($self->isError()) {
      $result = '';
    }

    return $result;
  }

  /**
   * Write the session
   *
   * @param  string  session id
   * @param  string  session data
   * @return bool success or failure
   */
  public static function write($id, $data) {
    $result = false;
    $self = PunyApp::getInstance(self::$className);

    $sql = sprintf(
      'SELECT COUNT(*) FROM %s WHERE id = ?',
      $self->getTableName()
    );
    $stmt = $self->_db->prepare($sql);
    $stmt->execute(array($id));
    $count = $stmt->fetchColumn(0);

    if ($count) {
      $sql = sprintf(
        'UPDATE %s SET data = ?, updateAt = ? WHERE id = ?',
        $self->getTableName()
      );
      $stmt = $self->_db->prepare($sql);
      $result = $stmt->execute(array($data, time(), $id));
    } else {
      $sql = sprintf(
        'INSERT INTO %s (id, data, updateAt) VALUES(?, ?, ?)',
        $self->getTableName()
      );
      $stmt = $self->_db->prepare($sql);
      $result = $stmt->execute(array($id, $data, time()));
    }

    if (!$self->isError()) {
      $result = true;
    }

    return $result;
  }

  /**
   * Destoroy the session
   *
   * @param  string  session id
   * @return bool success or failure
   */
  public static function destroy($id) {
    $result = false;
    $self = PunyApp::getInstance(self::$className);

    $sql = sprintf('DELETE FROM %s WHERE id = ?', $self->getTableName());
    $stmt = $self->_db->prepare($sql);
    $stmt->execute(array($id));

    if (!$self->isError()) {
      $result = true;
    }

    return $result;
  }

  /**
   * Garbage Collector
   *
   * @see    session.gc_probability    1
   * @see    session.gc_divisor      100
   * @see    session.gc_maxlifetime 1440
   *
   * @usage  execution rate        1/100
   *         (session.gc_probability/session.gc_divisor)
   *
   * @param  number  life time (Sec.)
   * @return bool success or failure
   */
  public static function gc($sec) {
    $result = false;
    $self = PunyApp::getInstance(self::$className);

    $sql = sprintf('DELETE FROM %s WHERE updateAt < ?', $self->getTableName());
    $stmt = $self->_db->prepare($sql);
    $stmt->execute(array(time() - $sec));

    if ($self->_vacuumble && empty($self->_vacuumed) && mt_rand(1, 100) === 1) {
      $self->_db->exec('VACUUM');
      $self->_vacuumed = true;
    }

    if (!$self->isError()) {
      $result = true;
    }

    return $result;
  }
}
