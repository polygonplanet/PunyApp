<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Application
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       http://polygonpla.net/
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 * @version    1.0.11
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
  const SETTINGS_FILENAME = 'app-settings.json';

  /**
   * @const string schema filename
   */
  const SCHEMA_FILENAME = 'app-schema.php';

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
      error_reporting(E_ALL & ~E_STRICT);
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
    $this->_executeUserInitialization();

    $this->cipher = new PunyApp_Security_Cipher($this->_generateKey());
    $this->arcfour = new PunyApp_Security_Arcfour($this->_generateKey());
    $this->token = new PunyApp_Security_Token($this);
    $this->view = new PunyApp_View($this);
    $this->database = new PunyApp_Database($this);
    $this->model = new PunyApp_Model($this);
    $this->validator = new PunyApp_Validator($this);
    $this->session = new PunyApp_Session($this);
    $this->session->start();
    $this->cookie = new PunyApp_Cookie($this);

    $this->_executeUserScheme();
    $this->removePoweredByHeader();
    $this->event->trigger('app-initialize', array());
  }


  /**
   * Parse settings file
   */
  private function _parseSettings() {
    $filename = PunyApp_Util::fullPath(
      PUNYAPP_SETTINGS_DIR . DIRECTORY_SEPARATOR . self::SETTINGS_FILENAME
    );
    $settings = file_get_contents($filename);
    $data = json_decode($settings);
    if (!$data) {
      throw new PunyApp_Error('Invalid JSON format');
    }

    foreach ($data as $cat => $values) {
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
            $this->databaseSettings->{$key} = $val;
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
  private function _executeUserInitialization() {
    require_once PunyApp_Util::fullPath(
      PUNYAPP_SETTINGS_DIR . DIRECTORY_SEPARATOR . self::INITIALIZE_FILENAME
    );
  }

  /**
   * Execute user scheme
   */
  private function _executeUserScheme() {
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
  }


  /**
   * Get current time in milliseconds
   *
   * @return int
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
    $filename = self::getLibPath($name, $path);
    if ($filename != null) {
      require_once $filename;
      return true;
    }
    return false;
  }

  /**
   * Imports library
   *
   * @param string $name
   * @param string $path
   * @return string or false
   */
  public static function getLibPath($name, $path) {
    $filename = null;
    $sep = DIRECTORY_SEPARATOR;
    $sep_hex = '\\x' . dechex(ord($sep));
    $split_re = sprintf('{[%s/]+}', $sep_hex);

    if (strpos($name, '/') !== false ||
        strpos($name, $sep) !== false) {
      $parts = preg_split($split_re, $name, -1, PREG_SPLIT_NO_EMPTY);
      $name = array_pop($parts);
      return self::getLibPath(
        $name,
        empty($parts) ? $path : $path . $sep . implode($sep, $parts)
      );
    }

    $name = PunyApp_Util::underscore(basename($name)) . '.php';
    $parts = preg_split($split_re, $path, -1, PREG_SPLIT_NO_EMPTY);
    $const = sprintf('PUNYAPP_%s_DIR', strtoupper(array_shift($parts)));

    if (!defined($const)) {
      $filename = PunyApp_Util::fullPath($path . $sep . $name);
    } else {
      $dir = constant($const);
      if (!empty($parts)) {
        $dir .= $sep . implode($sep, $parts);
      }

      $filename = PunyApp_Util::fullPath($dir . $sep . $name);
      if (!@file_exists($filename)) {
        $dir .= $sep . implode($sep, $parts);
        $filename = PunyApp_Util::fullPath($dir . $sep . $name);
      }

      if ($filename != null && @file_exists($filename)) {
        return $filename;
      }
    }

    return false;
  }

  /**
   * Get class name without namespace
   *
   * @param object $class
   * @return string
   */
  public static function getClass($class) {
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
   * @return object
   */
  public static function getInstance($classname) {
    static $instances = array();

    if (!isset($instances[$classname])) {
      $args = array_slice(func_get_args(), 1);
      $reflection = new ReflectionClass($classname);
      $instance = $reflection->newInstanceArgs($args);
      $instances[$classname] = $instance;
    }
    return $instances[$classname];
  }

  /**
   * Cache
   *
   * @param string $action
   * @param string $name
   * @param mixed $value
   * @param string $secret_key
   * @return mixed
   */
  public static function cache($action, $name, $value = null, $secret_key = null) {
    static $cache = array(), $secrets = array();

    $num_args = func_num_args();
    switch (strtolower($action)) {
      case 'get':
        if ($num_args === 3) {
          $secret_key = $value;
        }

        if ($name == null || !array_key_exists($name, $cache)) {
          return null;
        }

        if ($secret_key == null) {
          if (array_key_exists($name, $secrets)) {
            return null;
          }
          return $cache[$name];
        }

        if (!array_key_exists($name, $secrets) || $secrets[$name] !== $secret_key) {
          return null;
        }

        return $cache[$name];
      case 'set':
        $cache[$name] = $value;
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
          unset($cache[$name]);
          return true;
        }

        if (!array_key_exists($name, $secrets)) {
          return false;
        }
        if ($secrets[$name] === $secret_key) {
          unset($cache[$name], $secrets[$name]);
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
      } else if (strpos($uri, '/app/public/') !== false) {
        $uri = preg_replace('{^(.+/)app/public/$}', '$1', $uri);
      }
    }
    return $uri;
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
   * header
   *
   * @param string $action
   * @param string $name
   * @param string $value
   * @param bool $replace
   * @param int $code
   * @return bool
   */
  public static function header($action, $name = null, $value = null,
                                $replace = true, $code = null) {
    static $headers = array();

    switch (strtolower($action)) {
      case 'set':
        if (!$replace && array_key_exists($name, $headers)) {
          return false;
        }

        $header = null;
        if ($value === null) {
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
        if (headers_sent()) {
          return false;
        }

        foreach ($headers as $key => $args) {
          call_user_func_array('header', $args);
          unset($headers[$key]);
        }
        $headers = array();
        return true;
      case 'delete':
        unset($headers[$name]);
        return true;
    }
  }


  /**
   * Send HTTP response code
   *
   * @param int $code
   * @return bool
   */
  public function sendResponseCode($code) {
    if (headers_sent()) {
      return false;
    }

    $code = (int)$code;
    $response = null;
    switch ($code) {
      case 403:
        $response = 'Forbidden';
        break;
      case 404:
        $response = 'Not Found';
        break;
      case 500:
        $response = 'Internal Server Error';
        break;
    }

    if ($response != null) {
      $protocol = 'HTTP/1.0';
      if (isset($this->env->SERVER_PROTOCOL)) {
        $protocol = $this->env->SERVER_PROTOCOL;
      }
      self::header('set', sprintf('%s %d %s', $protocol, $code, $response), null, true, $code);
      return true;
    }
    return false;
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
    self::header('set', 'X-Powered-By', '');
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

    echo '</script></textarea><html><head>';
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
   * Generate key
   *
   * @return string
   */
  private function _generateKey() {
    return sprintf('{"db0163bc":"%s"}', $this->_salt);
  }
}
