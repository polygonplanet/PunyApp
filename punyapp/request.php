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
 * @link       https://github.com/polygonplanet/PunyApp
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
   * @var array request methods
   */
  public $methods = array(
    'GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS',
    'TRACE','PATCH','LINK','UNLINK','CONNECT'
  );

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
   * @var string raw request data
   */
  public $raw = null;

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
   * Check whether argument is request method
   *
   * @param string $method
   * @return bool
   */
  public function isRequestMethod($method) {
    return in_array(strtoupper($method), $this->methods, true);
  }

  /**
   * Check whether is request method is GET
   *
   * @return bool
   */
  public function isGET() {
    return $this->method === 'GET';
  }

  /**
   * Check whether is request method is POST
   *
   * @return bool
   */
  public function isPOST() {
    return $this->method === 'POST';
  }

  /**
   * Check whether is request method is PUT
   *
   * @return bool
   */
  public function isPUT() {
    return $this->method === 'PUT';
  }

  /**
   * Check whether is request method is DELETE
   *
   * @return bool
   */
  public function isDELETE() {
    return $this->method === 'DELETE';
  }

  /**
   * Check whether is request method is HEAD
   *
   * @return bool
   */
  public function isHEAD() {
    return $this->method === 'HEAD';
  }

  /**
   * Check whether is request method is OPTIONS
   *
   * @return bool
   */
  public function isOPTIONS() {
    return $this->method === 'OPTIONS';
  }

  /**
   * Check whether is request method is TRACE
   *
   * @return bool
   */
  public function isTRACE() {
    return $this->method === 'TRACE';
  }

  /**
   * Check whether is request method is PATCH
   *
   * @return bool
   */
  public function isPATCH() {
    return $this->method === 'PATCH';
  }

  /**
   * Check whether is request method is LINK
   *
   * @return bool
   */
  public function isLINK() {
    return $this->method === 'LINK';
  }

  /**
   * Check whether is request method is UNLINK
   *
   * @return bool
   */
  public function isUNLINK() {
    return $this->method === 'UNLINK';
  }

  /**
   * Check whether is request method is CONNECT
   *
   * @return bool
   */
  public function isCONNECT() {
    return $this->method === 'CONNECT';
  }

  /**
   * Returns whether the server is secure by https
   *
   * @return bool
   */
  public function isSSL() {
    static $ssl = null;

    if ($ssl === null) {
      $ssl = false;
      $https = $this->app->env->HTTPS;
      if ($https !== null && 0 !== strcasecmp($https, 'off')) {
        $ssl = true;
      } else if (strpos($this->app->env->SCRIPT_URI, 'https://') === 0) {
        $ssl = true;
      }
    }
    return $ssl;
  }

  /**
   * Check whether is requested device is mobile
   *
   * pattern from CakePHP
   *
   * @return bool
   */
  public function isMobile() {
    $agents = array(
      'Android', 'AvantGo', 'BlackBerry', 'DoCoMo', 'Fennec', 'iPod', 'iPhone',
      'iPad', 'J2ME', 'MIDP', 'NetFront', 'Nokia', 'Opera Mini', 'Opera Mobi',
      'PalmOS', 'PalmSource', 'portalmmm', 'Plucker', 'ReqwirelessWeb',
      'SonyEricsson', 'Symbian', 'UP[.]Browser', 'webOS', 'Windows CE',
      'Windows Phone OS', 'Xiino'
    );
    $pattern = '/' . implode('|', $agents) . '/i';
    return (bool)preg_match($pattern, $this->app->env->HTTP_USER_AGENT);
  }

  /**
   * Check whether requested with XMLHttpRequest
   *
   * @return bool
   */
  public function isAjax() {
    return 0 === strcasecmp($this->headers->{'X-Requested-With'}, 'XMLHttpRequest');
  }

  /**
   * Parse request URI
   */
  private function _parseRequestURI() {
    $base = $this->app->getBaseURI();
    $uri = $this->app->env->REQUEST_URI;
    if ($base != null && strpos($uri, $base) === 0) {
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
    $uri = ltrim($uri, '/');
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

    $this->headers = new PunyApp_Request_Headers();

    $this->params = null;
    $this->raw = file_get_contents('php://input');
    if (preg_match('/^\s*\(?\s*\{[\s\S]*\}\s*\)?\s*$/', $this->raw)) {
      $data = json_decode($this->raw, true);
      if (is_array($data)) {
        $this->params = new PunyApp_Request_Params($data);
      }
      $data = null;
    }

    if ($this->params === null) {
      $this->params = new PunyApp_Request_Params(array_merge(
        isset($_GET) ? (array)$_GET : array(),
        isset($_POST) ? (array)$_POST : array()
      ));
    }

    $this->files = new PunyApp_Request_Files(
      isset($_FILES) ? (array)$_FILES : array()
    );
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
                       && get_magic_quotes_gpc();
      $magic_quotes_sybase = (bool)(int)ini_get('magic_quotes_sybase');
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

    if ((empty($this->_headers) || !is_array($this->_headers)) &&
        isset($_SERVER) && is_array($_SERVER)) {

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

  /**
   * Get header
   *
   * @param  string $name
   * @return mixed
   */
  private function _getHeader($name, $recursive = 0) {
    if (isset($this->_headers[$name])) {
      return $this->_headers[$name];
    }

    if (++$recursive > 2) {
      return null;
    }

    if (strpos($name, '-') !== false || strpos($name, '_') !== false) {
      return $this->_getHeader(PunyApp_Util::camelize($name), $recursive);
    }

    $name = implode('-', array_map('ucfirst',
      explode('_', PunyApp_Util::underscore($name))));

    return $this->_getHeader($name, $recursive);
  }


  /**
   * Magic methods
   */
  public function __get($name) {
    return $this->_getHeader($name);
  }


  public function __set($name, $value) {
    $this->_headers[$name] = $value;
  }


  public function __isset($name) {
    return $this->_getHeader($name) !== null;
  }


  public function __unset($name) {
    unset($this->_headers[$name]);
  }

  /**
   * Iteration methods
   */
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

