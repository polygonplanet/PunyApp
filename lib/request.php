<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Request
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       http://polygonpla.net/
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Request
 */
class PunyApp_Request {

  /**
   * @var PunyApp_Request_Params request parameters
   */
  public $params = null;

  /**
   * @var string request method
   */
  public $method = null;

  /**
   * @var PunyApp_Request_Files request files
   */
  public $files = null;

  /**
   * @var PunyApp_Request_Headers request headers
   */
  public $headers = null;

  /**
   * @var string queryString
   */
  public $queryString = null;

  /**
   * @var PunyApp application instance
   */
  public $app = null;

  /**
   * @var string action name
   */
  public $actionName = null;

  /**
   * @var string controller name
   */
  public $controllerName = null;

  /**
   * @param PunyApp $app
   */
  public function __construct(PunyApp $app) {
    $this->app = $app;
    $this->_setRequestVars();
  }

  /**
   * Parse request URI
   */
  private function _parseRequestURI() {
    $base = $this->app->getBaseURI();
    $uri = $this->app->env->REQUEST_URI;
    if ($base != null && $base !== '/' && strpos($uri, $base) === 0) {
      $uri = substr($uri, strlen($base));
    }
    $this->_parseURI($uri);
  }

  /**
   * Parse URI
   *
   * @param string $uri
   * @return array
   */
  private function _parseURI($uri) {
    $parts = explode('?', $uri);
    $path = array_shift($parts);
    $paths = explode('/', $path);

    $this->controllerName = array_shift($paths);

    if (!empty($paths)) {
      $this->actionName = array_shift($paths);
    }

    if ($this->controllerName == null) {
      $this->controllerName = 'index';
    }
    if ($this->actionName == null) {
      $this->actionName = 'index';
    }

    if (empty($paths)) {
      return;
    }

    foreach ($paths as $key => $val) {
      $this->params->{$key} = $val;

      if (strpos($val, ':') !== false) {
        $items = explode(':', $val, 2);
        $this->params->{$items[0]} = $items[1];
      }
    }
  }

  /**
   * Refer the request variable
   *
   * @return array the reference of request variable
   */
  private function _setRequestVars() {
    $this->_normalizeVariables();

    $this->method = strtoupper(trim($this->app->env->REQUEST_METHOD));

    $this->queryString = '';
    if (is_string($this->app->env->QUERY_STRING)) {
      $this->queryString = rawurldecode(
        str_replace('+', '%20', $this->app->env->QUERY_STRING)
      );
    }

    $this->get = new PunyApp_Request_Params(
      isset($_GET) ? (array)$_GET : array()
    );

    $this->post = new PunyApp_Request_Params(
      isset($_POST) ? (array)$_POST : array()
    );

    $this->params = new PunyApp_Request_Params(array_merge(
      isset($_GET) ? (array)$_GET : array(),
      isset($_POST) ? (array)$_POST : array()
    ));

    $this->files = new PunyApp_Request_Files(
      isset($_FILES) ? (array)$_FILES : array()
    );

    $this->headers = new PunyApp_Request_Headers();

    $this->_parseRequestURI();
  }


  /**
   * Removes null(\0) bytes and extra backslash from variables
   *
   * @param  mixed $var target variable to filter
   * @return mixed  filtered variable
   *
   * @since PHP 4, PHP 5 < PHP 5.4.0
   */
  private function _normalizeMagicQuotes($var) {
    static $magic_quotes_gpc = null, $magic_quotes_sybase = null;

    if ($magic_quotes_gpc === null) {
      $magic_quotes_gpc = function_exists('get_magic_quotes_gpc')
                       && @get_magic_quotes_gpc();
      $magic_quotes_sybase = (bool)(int)@ini_get('magic_quotes_sybase');
    }

    if (is_array($var)) {
      $var = array_map(array($this, '_normalizeMagicQuotes'), $var);
    } else if (is_string($var)) {
      $var = str_replace("\0", '', $var);

      if ($magic_quotes_gpc) {
        if ($magic_quotes_sybase) {
          $var = str_replace("''", "'", $var);
        } else {
          $var = stripslashes($var);
        }
      }
    }

    return $var;
  }

  /**
   * Normalize super global variables (e.g. $_POST or $_GET)
   *
   * @param  void
   * @return void
   */
  private function _normalizeVariables() {
    static $initialized = false;

    if ($initialized) {
      return;
    }
    $initialized = true;

    if (isset($_GET)) {
      $_GET = $this->_normalizeMagicQuotes($_GET);
    }
    if (isset($_POST)) {
      $_POST = $this->_normalizeMagicQuotes($_POST);
    }
    if (isset($_COOKIE)) {
      $_COOKIE = $this->_normalizeMagicQuotes($_COOKIE);
    }
    if (isset($_REQUEST)) {
      $_REQUEST = $this->_normalizeMagicQuotes($_REQUEST);
    }
  }
}

/**
 * @name PunyApp_Request_Params
 */
class PunyApp_Request_Params implements Iterator {

  /**
   * @var array request vars
   */
  private $_params = null;

  /**
   * @var int
   */
  private $_pos = 0;

  /**
   * Constructor
   *
   * @param array request vars
   */
  public function __construct($params = array()) {
    $this->_params = array();

    foreach ((array)$params as $key => $value) {
      $this->_params[$key] = $value;
    }
  }


  public function __get($name) {
    return isset($this->_params[$name]) ? $this->_params[$name] : null;
  }


  public function __set($name, $value) {
    $this->_params[$name] = $value;
  }


  public function __isset($name) {
    return isset($this->_params[$name]);
  }


  public function __unset($name) {
    unset($this->_params[$name]);
  }


  public function rewind() {
    return reset($this->_params);
  }


  public function current() {
    return current($this->_params);
  }


  public function key() {
    return key($this->_params);
  }


  public function next() {
    return next($this->_params);
  }


  public function valid() {
    return key($this->_params) !== null;
  }
}

/**
 * @name PunyApp_Request_Headers
 */
class PunyApp_Request_Headers implements Iterator {

  /**
   * @var array request headers
   */
  private $_headers = null;

  /**
   * Constructor
   */
  public function __construct() {
    $this->_parseRequestHeaders();
  }

  /**
   * Parse headers
   */
  private function _parseRequestHeaders() {
    $this->_headers = array();

    if (isset($_SERVER) && is_array($_SERVER)) {
      foreach ($_SERVER as $key => $val) {
        $key = strtolower($key);

        if (strpos($key, 'http_') !== 0) {
          continue;
        }

        $key = substr($key, 5);
        $parts = explode('_', $key);

        if (count($parts) > 0 && strlen($key) > 2) {
          $key = implode('-', array_map('ucfirst', $parts));
        }
        $this->_headers[$key] = $val;
      }
    }
  }


  public function __get($name) {
    return isset($this->_headers[$name]) ? $this->_headers[$name] : null;
  }


  public function __set($name, $value) {
    $this->_headers[$name] = $value;
  }


  public function __isset($name) {
    return isset($this->_headers[$name]);
  }


  public function __unset($name) {
    unset($this->_headers[$name]);
  }

  public function rewind() {
    return reset($this->_headers);
  }


  public function current() {
    return current($this->_headers);
  }


  public function key() {
    return key($this->_headers);
  }


  public function next() {
    return next($this->_headers);
  }


  public function valid() {
    return key($this->_headers) !== null;
  }
}

/**
 * @name PunyApp_Request_Files
 */
class PunyApp_Request_Files extends PunyApp_Request_Params {}

