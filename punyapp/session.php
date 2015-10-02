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
 * @copyright  Copyright (c) 2014-2015 polygon planet
 */

/**
 * @name PunyApp_Session_Settings
 */
class PunyApp_Session_Settings {}

/**
 * @name PunyApp_Session
 */
class PunyApp_Session implements Iterator {

  /**
   * @const string session filename
   */
  const SESSION_FILENAME = 'app-session';

  /**
   * @const string session tablename
   */
  const SESSION_TABLENAME = 'punyapp_sessions';

  /**
   * @var PunyApp
   */
  private $_app = null;

  /**
   * @var string session cookie name
   */
  private $_name = null;

  /**
   * @var int session timeout
   */
  private $_timeout = null;

  /**
   * @var string session cookie path
   */
  private $_cookiePath = null;

  /**
   * @var string session save path
   */
  private $_savePath = null;

  /**
   * @var PunyApp_Session_Database
   */
  public static $handler = null;

  /**
   * @param PunyApp $app
   */
  public function __construct(PunyApp $app) {
    $this->_app = $app;
    $this->_initialize();
  }

  /**
   * Initialize
   */
  private function _initialize() {
    static $initialized = false;

    if ($initialized) {
      return;
    }
    $initialized = true;

    if (empty($this->_app->sessionSettings->engine)) {
      return;
    }

    $this->_name = $this->_app->sessionSettings->name;
    $this->_timeout = $this->_app->sessionSettings->timeout;
    $this->_savePath = PUNYAPP_SESSIONS_DIR;
    $this->_cookiePath = $this->_app->getBaseURI();

    $engine = strtolower($this->_app->sessionSettings->engine);
    $config = $this->_getDefaultConfig($engine);

    if (isset($config['ini'])) {
      PunyApp::setConfig($config['ini']);
    }

    if (isset($config['handler'])) {
      $class = $config['handler']['class'];
      $dir = PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'sessions';
      $filename = $dir . DIRECTORY_SEPARATOR . 'database.php';
      require_once $filename;

      $handler = new $class(
        $this->_app->database,
        self::SESSION_TABLENAME,
        $this->_timeout
      );

      session_set_save_handler(
        array($handler, 'open'),
        array($handler, 'close'),
        array($handler, 'read'),
        array($handler, 'write'),
        array($handler, 'destroy'),
        array($handler, 'gc')
      );
    }
    register_shutdown_function('session_write_close');
  }

  /**
   * Starts the session
   *
   * @return bool
   */
  public static function start() {
    if (self::isStarted()) {
      return false;
    }

    session_write_close();
    if (headers_sent()) {
      if (empty($_SESSION)) {
        $_SESSION = array();
      }
    } else {
      session_cache_limiter('must-revalidate');
      session_start();
    }

    return self::isStarted();
  }

  /**
   * Determine if Session has been started
   *
   * @return bool
   */
  public static function isStarted() {
    return isset($_SESSION) && session_id() != null;
  }

  /**
   * Commit and close session
   */
  public static function commit() {
    session_write_close();
  }


  /**
   * Get default session configurations
   *
   * @param string $name
   * @return array
   */
  private function _getDefaultConfig($name) {
    $defaults = array(
      'php' => array(
        'ini' => array()
      ),
      'file' => array(
        'ini' => array(
          'url_rewriter.tags' => '',
          'session.serialize_handler' => 'php',
          'session.use_cookies' => 1,
          'session.save_path' => $this->_savePath,
          'session.save_handler' => 'files'
        )
      ),
      'database' => array(
        'ini' => array(
          'url_rewriter.tags' => '',
          'session.use_cookies' => 1,
          'session.save_handler' => 'user',
          'session.serialize_handler' => 'php'
        ),
        'handler' => array(
          'class' => 'PunyApp_Session_Database'
        )
      )
    );

    if (!isset($defaults[$name])) {
      throw new PunyApp_Error("Session '{$name}' is not defined");
    }

    $commons = array(
      'session.name' => $this->_name,
      'session.cookie_path' => $this->_cookiePath,
      'session.use_trans_sid' => 0,
      'session.gc_probability' => 1,
      'session.gc_divisor' => 1,
      'session.gc_maxlifetime' => $this->_timeout,
      'session.auto_start' => 0
    );
    $result = $defaults[$name];
    $result['ini'] = $commons + $result['ini'];

    return $result;
  }


  /**
   * Magic methods
   */
  public function __get($name) {
    return isset($_SESSION, $_SESSION[$name]) ? $_SESSION[$name] : null;
  }


  public function __set($name, $value) {
    if (isset($_SESSION) && is_array($_SESSION)) {
      $_SESSION[$name] = $value;
    }
  }


  public function __isset($name) {
    return isset($_SESSION, $_SESSION[$name]);
  }


  public function __unset($name) {
    if (isset($_SESSION) && is_array($_SESSION)) {
      $_SESSION[$name] = null;
      unset($_SESSION[$name]);
    }
  }


  /**
   * Iteration methods
   */
  public function rewind() {
    return reset($_SESSION);
  }


  public function current() {
    return current($_SESSION);
  }


  public function key() {
    return key($_SESSION);
  }


  public function next() {
    return next($_SESSION);
  }


  public function valid() {
    return key($_SESSION) !== null;
  }
}
