<?php
/**
 * PunyApp: PHP Micro Framework based CakePHP
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Application
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014-2018 polygon planet
 * @version    1.0.25
 */

/**
 * @name PunyApp_Error
 */
class PunyApp_Error extends Exception {}

/**
 * @name PunyApp
 */
class PunyApp extends PunyApp_Settings {

  /**
   * @const string initialize filename
   */
  const INITIALIZE_FILENAME = 'app-initialize.php';

  /**
   * @const string settings filename
   */
  const SETTINGS_FILENAME = 'app-settings.php';

  /**
   * @const string schema filename
   */
  const SCHEMA_FILENAME = 'app-schema.php';

  /**
   * @const string cache name
   */
  const CACHE_NAME = 'app-cache';

  /**
   * @const string error log name
   */
  const ERROR_LOG_NAME = 'app-error';

  /**
   * @var PunyApp_Request
   */
  public $request = null;

  /**
   * @var PunyApp_Env
   */
  public $env = null;

  /**
   * @var PunyApp_Database
   */
  public $database = null;

  /**
   * @var PunyApp_Database_Settings
   */
  public $databaseSettings = null;

  /**
   * @var PunyApp_Session
   */
  public $session = null;

  /**
   * @var PunyApp_Cookie
   */
  public $cookie = null;

  /**
   * @var PunyApp_Cache
   */
  public $cache = null;

  /**
   * @var PunyApp_Log
   */
  public $errorlog = null;

  /**
   * @var PunyApp_Security_Cipher
   */
  public $cipher = null;

  /**
   * @var PunyApp_Security_Arcfour
   */
  public $arcfour = null;

  /**
   * @var PunyApp_Security_Token
   */
  public $token = null;

  /**
   * @var PunyApp_Session_Settings
   */
  public $sessionSettings = null;

  /**
   * @var PunyApp_Event
   */
  public $event = null;

  /**
   * @var PunyApp_View
   */
  public $view = null;

  /**
   * @var PunyApp_Validator
   */
  public $validator = null;

  /**
   * @var PunyApp_Model
   */
  public $model = null;

  /**
   * @var PunyApp_Controller
   */
  public $controller = null;

  /**
   * Initialization
   */
  public function initialize() {
    static $initialized = false;

    if ($initialized) {
      return;
    }
    $initialized = true;

    if ($this->_debug) {
      error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
    } else {
      error_reporting(0);
    }

    $this->event = new PunyApp_Event($this);
    $this->env = new PunyApp_Env($this);
    $this->request = new PunyApp_Request($this);

    $this->databaseSettings = new PunyApp_Database_Settings();
    $this->sessionSettings = new PunyApp_Session_Settings();

    $this->_parseSettings();
    $this->_updateTimezone();
    $this->_updateSettings();
    $this->_executeAppInitialization();

    $this->cipher = new PunyApp_Security_Cipher($this->_generateKey());
    $this->arcfour = new PunyApp_Security_Arcfour($this->_generateKey());
    $this->token = new PunyApp_Security_Token($this);
    $this->cache = new PunyApp_Cache(self::CACHE_NAME);
    $this->errorlog = new PunyApp_Log(self::ERROR_LOG_NAME, $this->_logErrorMax);
    $this->view = new PunyApp_View($this);
    $this->database = new PunyApp_Database($this);
    $this->_executeAppSchema();

    $this->validator = new PunyApp_Validator($this);
    $this->session = new PunyApp_Session($this);
    if ($this->sessionSettings->engine != null) {
      $this->session->start();
    }
    $this->cookie = new PunyApp_Cookie($this);

    register_shutdown_function(array($this, 'handleLastError'));
    set_error_handler(array($this, 'handleError'));
    spl_autoload_register(array(__CLASS__, 'load'));
    $this->removePoweredByHeader();

    $this->event->trigger('app-initialize', array());
  }


  /**
   * Parse settings file
   */
  private function _parseSettings() {
    require_once PunyApp_Util::fullPath(
      PUNYAPP_SETTINGS_DIR . DIRECTORY_SEPARATOR . self::SETTINGS_FILENAME
    );

    foreach ($settings as $cat => $values) {
      switch ($cat) {
        case 'system':
          foreach ($values as $key => $val) {
            $method = 'set' . ucfirst($key);
            $func = array($this, $method);
            if (method_exists('PunyApp_Settings', $method) && is_callable($func)) {
              call_user_func($func, $val);
            }
          }
          break;
        case 'database':
          foreach ($values as $key => $val) {
            switch ($key) {
              case 'default':
                if (!$this->isDebug()) {
                  foreach ($val as $k => $v) {
                    $this->databaseSettings->{$k} = $v;
                  }
                }
                break;
              case 'debug':
                if ($this->isDebug()) {
                  foreach ($val as $k => $v) {
                    $this->databaseSettings->{$k} = $v;
                  }
                }
                break;
              default:
                $this->databaseSettings->{$key} = $val;
                break;
            }
          }
          break;
        case 'session':
          foreach ($values as $key => $val) {
            $this->sessionSettings->{$key} = $val;
          }
          break;
      }
    }
  }

  /**
   * Execute user initialize file
   */
  private function _executeAppInitialization() {
    require_once PunyApp_Util::fullPath(
      PUNYAPP_SETTINGS_DIR . DIRECTORY_SEPARATOR . self::INITIALIZE_FILENAME
    );
  }

  /**
   * Execute user schema
   */
  private function _executeAppSchema() {
    if ($this->cache->_appSchemaDone) {
      return;
    }

    require_once PunyApp_Util::fullPath(
      PUNYAPP_SETTINGS_DIR . DIRECTORY_SEPARATOR . self::SCHEMA_FILENAME
    );

    if (isset($schema)) {
      foreach ((array)$schema as $def) {
        $this->database->exec($def);
        if ($this->database->isError()) {
          throw new PunyApp_Database_Error($this->database->getLastError());
        }
      }
    }

    $this->cache->_appSchemaDone = true;
  }

  /**
   * Set config
   *
   * @param mixed $name
   * @param mixed $value
   */
  public static function setConfig($name, $value = null) {
    $values = $name;
    if (!is_array($name)) {
      $values = array($name => $value);
    }

    foreach ($values as $key => $val) {
      ini_set($key, $val);
    }
  }

  /**
   * Get current time in milliseconds
   *
   * @return float
   */
  public static function now() {
    return PunyApp_Util::now();
  }

  /**
   * Imports library
   *
   * @param string $name
   * @param string $path
   * @return bool
   */
  public static function uses($name, $path) {
    static $paths = array();

    $key = $name . ':' . $path;
    if (isset($paths[$key])) {
      return false;
    }

    $filename = self::getLibPath($name, $path);
    if (!$filename) {
      return false;
    }

    $paths[$key] = true;
    require_once $filename;
    return true;
  }


  /**
   * Load class
   *
   * @param string $name class name
   * @return mixed
   */
  public static function load($name) {
    static $paths = array(), $files = null, $ext = '.php';

    if ($files === null) {
      $files = PunyApp_Util::getFiles(PUNYAPP_LIBRARIES_DIR, $ext);
    }

    if (isset($paths[$name])) {
      return false;
    }

    $found = false;
    $dirname = null;
    $filename = $name . $ext;
    foreach ($files as $file) {
      $dirname = dirname($file);
      $basename = basename($file);
      if ($filename === $basename) {
        $found = true;
        break;
      }

      if (strpos($name, '_') === false) {
        $underscore = PunyApp_Util::underscore($name) . $ext;
        if ($underscore === $basename) {
          $filename = $underscore;
          $found = true;
          break;
        }
      } else {
        $camelcase = PunyApp_Util::camelize($name) . $ext;
        if ($camelcase === $basename) {
          $filename = $camelcase;
          $found = true;
          break;
        }
      }
    }

    if (!$found) {
      return false;
    }

    $filename = $dirname . DIRECTORY_SEPARATOR . $filename;
    $paths[$name] = true;
    return include $filename;
  }

  /**
   * Get library path
   *
   * @param string $name
   * @param string $path
   * @return string or false
   */
  public static function getLibPath($name, $path) {
    $sep = '/';
    $parts = null;
    $name = PunyApp_Util::normalizeFilePath($name);
    $path = PunyApp_Util::normalizeFilePath($path);

    if (strpos($name, $sep) !== false) {
      $parts = explode($sep, $name);
      $name = array_pop($parts);
      return self::getLibPath(
        $name,
        empty($parts) ? $path : $path . $sep . implode($sep, $parts)
      );
    }

    $parts = explode($sep, $path);
    $const = sprintf('PUNYAPP_%s_DIR', strtoupper(array_shift($parts)));
    if (!defined($const)) {
      return false;
    }

    $dir = constant($const);
    if (!empty($parts)) {
      $dir .= $sep . implode($sep, $parts);
    }

    $ext = '.php';
    $basename = basename($name);
    $filenames = array(
      $basename . $ext,
      strtolower($basename) . $ext,
      PunyApp_Util::underscore($basename) . $ext,
      PunyApp_Util::camelize($basename) . $ext
    );

    foreach ($filenames as $filename) {
      $pathname = PunyApp_Util::fullPath($dir . $sep . $filename);
      if (file_exists($pathname)) {
        return $pathname;
      }
    }

    return false;
  }

  /**
   * Custom error handler
   *
   * @param int $code
   * @param string $message
   * @param string $file
   * @param int $line
   * @param array $context
   */
  public function handleError($code,
                              $message,
                              $file = null,
                              $line = null,
                              $context = null) {

    $e = new ErrorException(strip_tags($message), 0, $code, $file, $line);

    if ($this->_logError) {
      $this->errorlog->write($e);
    }

    $this->event->trigger('app-error', array($e));
    throw $e;
  }

  /**
   * Custom error handler for fatal error
   */
  public function handleLastError() {
    $last_error = error_get_last();
    if (!empty($last_error) &&
        isset($last_error['type'], $last_error['message'])) {

      $type = $last_error['type'];
      if (!(error_reporting() & $type)) {
        return;
      }

      $message = $last_error['message'];
      $file = null;
      if (isset($last_error['file'])) {
        $file = $last_error['file'];
      }

      $line = null;
      if (isset($last_error['line'])) {
        $line = $last_error['line'];
      }
      $this->handleError($type, $message, $file, $line);
    }
  }

  /**
   * Get class name without namespace
   *
   * @param object $class
   * @return string
   */
  public static function getClassName($class) {
    $name = null;
    if (is_object($class)) {
      $name = get_class($class);
    }

    if ($name != null && is_string($name)) {
      $pos = strrpos($name, '\\');
      if ($pos !== false) {
        $name = substr($name, $pos + 1);
      }
    }

    return $name;
  }

  /**
   * Get/Create class instance
   *
   * @param string $classname
   * @param array $params
   * @return object
   */
  public static function getInstance($classname, $params = array()) {
    static $instances = array();

    if (!isset($instances[$classname])) {
      $reflection = new ReflectionClass($classname);
      $instance = $reflection->newInstanceArgs($params);
      $instances[$classname] = $instance;
    }
    return $instances[$classname];
  }

  /**
   * Store
   *
   * @param string $action
   * @param string $name
   * @param mixed $value
   * @param string $secret_key
   * @return mixed
   */
  public static function store($action, $name, $value = null, $secret_key = null) {
    static $store = array(), $secrets = array();

    $num_args = func_num_args();
    switch (strtolower($action)) {
      case 'get':
        if ($num_args === 3) {
          $secret_key = $value;
        }

        if ($name == null || !array_key_exists($name, $store)) {
          return null;
        }

        if ($secret_key == null) {
          if (array_key_exists($name, $secrets)) {
            return null;
          }
          return $store[$name];
        }

        if (!array_key_exists($name, $secrets) || $secrets[$name] !== $secret_key) {
          return null;
        }
        return $store[$name];

      case 'set':
        $store[$name] = $value;
        if ($secret_key != null) {
          $secrets[$name] = $secret_key;
        }
        return true;

      case 'delete':
        if ($num_args === 3) {
          $secret_key = $value;
        }

        if ($secret_key == null) {
          if (array_key_exists($name, $secrets)) {
            return false;
          }
          unset($store[$name]);
          return true;
        }

        if (!array_key_exists($name, $secrets)) {
          return false;
        }
        if ($secrets[$name] === $secret_key) {
          unset($store[$name], $secrets[$name]);
          return true;
        }
        return false;
    }

    return null;
  }


  /**
   * Get the base URI that is able to use in cookie path etc.
   *
   * @param  void
   * @return string   the base URI
   */
  public function getBaseURI() {
    static $uri = null;

    if ($uri === null) {
      $uri = '';
      $sep = '/';
      $docroot = PunyApp_Util::normalizeFilePath($this->env->DOCUMENT_ROOT);
      $filename = PunyApp_Util::normalizeFilePath($this->env->SCRIPT_FILENAME);
      $uri = str_replace($docroot, '', $filename);

      $removals = array('<', '>', '*', '\'', '"');
      $uri = PunyApp_Util::normalizeFilePath(str_replace($removals, '', dirname($uri)));
      if ($uri === '\\' || $uri === '.' || $uri == null) {
        $uri = $sep;
      }

      $uri = PunyApp_Util::normalizeFilePath($uri);
      if (substr($uri, 0, 1) !== $sep) {
        $uri = $sep . $uri;
      }
      if (substr($uri, -1) !== $sep) {
        $uri .= $sep;
      }

      if ($uri === ($sep . $sep)) {
        $uri = $sep;
      } else {
        $dir = '/application/public/';
        if (substr($uri, -strlen($dir)) === $dir) {
          $uri = substr($uri, 0, strrpos($uri, $dir) + 1);
        }
      }
    }
    return $uri;
  }

  /**
   * Check the debug mode
   *
   * @return bool return TRUE if running in debug mode
   */
  public function isDebug() {
    return $this->_debug;
  }

  /**
   * PHPがCGIモードで動いてるか調べる
   *
   * @return bool CGIモードで動いてるならtrue、そうでないならfalseが返る
   */
  public function isCGI() {
    static $is_cgi = null;
    if ($is_cgi === null) {
      $is_cgi = strtolower(substr(php_sapi_name(), 0, 3)) === 'cgi';
    }
    return $is_cgi;
  }

  /**
   * Return string length
   *
   * @param string $string
   * @return int
   */
  public function length($string) {
    return PunyApp_Util::length($string, $this->_charset);
  }

  /**
   * Escape the context string for HTML entities
   *
   * @param  mixed $string subject string or array or any value
   * @return mixed escaped value
   */
  public function escapeHTML($string) {
    return PunyApp_Util::escapeHTML($string, $this->_charset);
  }

  /**
   * Unescape the content string for HTML entities
   *
   * @param  mixed $string
   * @return mixed unescaped value
   */
  public static function unescapeHTML($string) {
    return PunyApp_Util::unescapeHTML($string);
  }

  /**
   * headerを設定・取得・送信・削除する
   *
   * @param string $action
   * @param string $name
   * @param string $value
   * @param bool $replace
   * @param int $code
   * @param bool $is_response_code
   * @return bool
   */
  public static function header($action, $name = null, $value = null,
                                $replace = true, $code = null,
                                $is_response_code = false) {
    static $headers = array();

    switch (strtolower($action)) {
      case 'get':
        return $headers;

      case 'set':
        if (!$replace && array_key_exists($name, $headers)) {
          return false;
        }

        if ($is_response_code) {
          $header = $name;
        } else {
          $header = $name . ': ' . $value;
        }
        $headers[$name] = array($header, $replace, $code);
        if ($code === null) {
          array_pop($headers[$name]);
        }
        return true;

      case 'send':
        if (empty($headers) || headers_sent()) {
          return false;
        }

        foreach ($headers as $key => $args) {
          call_user_func_array('header', $args);
          unset($headers[$key]);
        }
        $headers = array();
        return true;

      case 'delete':
        if (function_exists('header_remove')) {
          header_remove($name);
        } else {
          PunyApp::header('set', $name, null);
        }
        return true;
    }
  }


  /**
   * HTTPレスポンスコードを送信する
   * FastCGIやPHP-FPMでPHPを実行してる場合、ステータスコードが正しく送信できないバグがある
   *
   * PHP: How to send HTTP response code? - Stack Overflow
   * https://stackoverflow.com/questions/3258634/php-how-to-send-http-response-code
   *
   * @param int $code
   * @return void
   */
  public function sendResponseCode($code) {
    if (headers_sent()) {
      return false;
    }

    $code = (int)$code;
    $message = null;

    switch ($code) {
      case 200:
        $message = 'OK';
        break;
      case 403:
        $message = 'Forbidden';
        break;
      case 404:
        $message = 'Not Found';
        break;
      case 500:
        $message = 'Internal Server Error';
        break;
      default:
        throw new PunyApp_Error("ResponseCode '{$code}' is not defined");
    }

    if (self::isCGI()) {
      $response = sprintf('%d %s', $code, $message);
      self::header('set', 'Status', $response, true, $code);
    } else {
      $protocol = 'HTTP/1.0';
      if (isset($this->env->SERVER_PROTOCOL)) {
        $protocol = $this->env->SERVER_PROTOCOL;
      }
      $response = sprintf('%s %d %s', $protocol, $code, $message);
      self::header('set', $response, null, true, $code, true);
    }
  }

  /**
   * Send Content-type states header
   *
   * @param  string $type content-type
   * @return void
   */
  public function sendContentType($type = null) {
    if ($type === null) {
      $type = $this->_mimeType;
    }

    if ($type != null) {
      $header = $type;
      if ($this->_charset != null) {
        $header .= sprintf('; charset=%s', $this->_charset);
      }
      self::header('set', 'Content-Type', $header);
    }
  }

  /**
   * Remove X-Powered-By: PHP x.x.x header
   */
  public static function removePoweredByHeader() {
    self::header('delete', 'X-Powered-By');
  }

  /**
   * Send response
   *
   * @param string $message message
   * @return bool
   */
  public static function send($message) {
    $sent = false;
    $args = func_get_args();

    if (!empty($args)) {
      self::header('send');
      foreach ($args as $arg) {
        echo $arg;
      }
      $sent = true;
    }

    return $sent;
  }

  /**
   * Send response as JSON
   *
   * @param array $data json data
   * @return bool
   */
  public static function sendJSON($data = array()) {
    if (!is_array($data) || empty($data)) {
      return self::send('{}');
    }
    return self::send(json_encode($data));
  }

  /**
   * Redirect location
   *
   * @param string $url
   * @return void
   */
  public function redirect($url) {
    $url = trim($url);

    if (!headers_sent()) {
      self::header('set', 'Location', $url);
      self::header('send');
      exit;
    }

    echo '</script></textarea></pre><html><head>';
    printf('<meta http-equiv="refresh" content="0;url=%s">',
      $this->escapeHTML($url)
    );
    printf('<script>location.href=%s</script>',
      json_encode($url)
    );
    echo '</head><body></body></html>';
    exit;
  }

  /**
   * Encrypt data
   *
   * @param  string $data
   * @return string encrypted data
   */
  public function encrypt($data) {
    return PunyApp_Util::base64URLEncode($this->arcfour->encrypt($data));
  }

  /**
   * Decrypt data
   *
   * @param string $data
   * @return string decrypted data
   */
  public function decrypt($data) {
    return $this->arcfour->decrypt(PunyApp_Util::base64URLDecode($data));
  }

  /**
   * Generate hash.
   * The returned value is a 40-character hexadecimal number.
   *
   * @param string $value
   * @return string
   */
  public static function hash($value) {
    return sha1($value);
  }

  /**
   * Generate key
   *
   * @return string
   */
  private function _generateKey() {
    return self::hash(sprintf('{"db0163bc":"%s"}', $this->_salt));
  }
}
