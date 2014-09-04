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
 * @link       http://polygonpla.net/
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
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
   * @var PunyApp
   */
  private $_app = null;

  /**
   * @var string session class name
   */
  private $_sessionClassName = null;

  /**
   * @var string session filename
   */
  private $_sessionFileName = null;

  /**
   * @var int
   */
  private $_sessionMaxLifeTime = 0;

  /**
   * @param PunyApp $app
   */
  public function __construct(PunyApp $app) {
    $this->_app = $app;
    $this->_init();
  }


  private function _init() {
    static $initialized = false;

    if ($initialized) {
      return;
    }

    $initialized = true;

    $this->_sessionFileName = PUNYAPP_SESSIONS_DIR . DIRECTORY_SEPARATOR . self::SESSION_FILENAME;

    if (isset($this->_app->sessionSettings->engine) &&
        $this->_app->sessionSettings->engine != null) {
      $common = PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'session' . DIRECTORY_SEPARATOR . 'common.php';
      require_once $common;

      $engine = strtolower($this->_app->sessionSettings->engine);
      $driver = PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'session' . DIRECTORY_SEPARATOR . $engine . '.php';

      require_once $driver;
      $this->_sessionClassName = 'PunyApp_Session_' . ucfirst($engine);
      $this->_sessionFileName .= '.' . preg_replace('/\W+/', '', $engine);

      call_user_func(array($this->_sessionClassName, 'setClassName'), $this->_sessionClassName);
      call_user_func(array($this->_sessionClassName, 'setFileName'), $this->_sessionFileName);
    }

    if (isset($this->_app->sessionSettings->expirationDate) &&
        (int)$this->_app->sessionSettings->expirationDate > 0) {
      $this->_sessionMaxLifeTime = 60 * 60 * 24 * (int)$this->_app->sessionSettings->expirationDate;
    }
  }


  /**
   * Starts session
   */
  public function start() {
    call_user_func(array($this->_sessionClassName, 'init'),
      $this->_app->sessionSettings->name,
      PunyApp_Util::fullPath($this->_sessionFileName),
      null,
      $this->_sessionMaxLifeTime,
      $this->_app->getBaseURI(),
      $this->_app->isHTTPS()
    );

    call_user_func(array($this->_sessionClassName, 'start'));
  }


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
