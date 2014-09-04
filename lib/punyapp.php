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
 * @version    1.0.5
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

    $this->event = new PunyApp_Event();
    $this->env = new PunyApp_Env($this);
    $this->request = new PunyApp_Request($this);

    $this->databaseSettings = new PunyApp_Database_Settings();
    $this->sessionSettings = new PunyApp_Session_Settings();

    $settings = file_get_contents(
      PunyApp_Util::fullPath(
        PUNYAPP_SETTINGS_DIR . DIRECTORY_SEPARATOR . self::SETTINGS_FILENAME
      )
    );

    $settings_data = json_decode($settings);
    if (!$settings_data) {
      throw new PunyApp_Error('Invalid JSON format');
    }

    foreach ($settings_data as $cat => $values) {
      switch ($cat) {
        case 'system':
          foreach ($values as $key => $val) {
            $method = 'set' . ucfirst($key);
            $func = array($this, $method);
            if (method_exists('PunyApp_Settings', $method)
                && is_callable($func)) {
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
        default:
          break;
      }
    }

    $this->_updateTimezone();
    $this->_updateSettings();

    $init_filename = PunyApp_Util::fullPath(
      PUNYAPP_SETTINGS_DIR . DIRECTORY_SEPARATOR . self::INITIALIZE_FILENAME
    );
    require_once $init_filename;

    $this->cipher = new PunyApp_Security_Cipher($this->_generateKey());
    $this->arcfour = new PunyApp_Security_Arcfour($this->_generateKey());
    $this->token = new PunyApp_Security_Token($this);
    $this->view = new PunyApp_View($this);
    $this->database = new PunyApp_Database($this->databaseSettings);
    $this->model = new PunyApp_Model($this);
    $this->validator = new PunyApp_Validator($this);
    $this->session = new PunyApp_Session($this);
    $this->session->start();
    $this->cookie = new PunyApp_Cookie($this);

    $schema_filename = PunyApp_Util::fullPath(
      PUNYAPP_SETTINGS_DIR . DIRECTORY_SEPARATOR . self::SCHEMA_FILENAME
    );

    require_once $schema_filename;

    if (isset($schema)) {
      foreach ((array)$schema as $def) {
        $this->database->exec($def);
      }

      if ($this->database->isError()) {
        throw new PunyApp_Database_Error($this->database->getLastError());
      }
    }

    $this->removePoweredByHeader();
    $this->event->trigger('app-initialize');
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
   * @return boolean
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

    if (strpos($name, '/') !== false ||
        strpos($name, DIRECTORY_SEPARATOR) !== false) {
      $parts = preg_split('{[\x5c/]+}', $name, -1, PREG_SPLIT_NO_EMPTY);
      $name = array_pop($parts);
      return self::getLibPath(
        $name,
        empty($parts) ? $path :
          $path . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts)
      );
    }

    $name = PunyApp_Util::underscore(basename($name)) . '.php';
    $parts = preg_split('{[\x5c/]+}', $path, -1, PREG_SPLIT_NO_EMPTY);
    $const = sprintf('PUNYAPP_%s_DIR', strtoupper(array_shift($parts)));

    if (!defined($const)) {
      $filename = PunyApp_Util::fullPath($path . DIRECTORY_SEPARATOR . $name);
    } else {
      $dir = constant($const);
      if (!empty($parts)) {
        $dir .= DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts);
      }

      $filename = PunyApp_Util::fullPath($dir . DIRECTORY_SEPARATOR . $name);
      if (!@file_exists($filename)) {
        $dir .= DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts);
        $filename = PunyApp_Util::fullPath($dir . DIRECTORY_SEPARATOR . $name);
      }

      if ($filename != null && @file_exists($filename)) {
        return $filename;
      }
    }

    return false;
  }

  /**
   * Get class instance
   *
   * @param  void
   * @return object
   */
  public static function getInstance($className) {
    static $instances = array();

    if (!isset($instances[$className])) {
      $instances[$className] = new $className();
    }
    return $instances[$className];
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

    switch (strtolower($action)) {
      case 'get':
        if (func_num_args() === 3) {
          $secret_key = $value;
        }

        if ($name == null) {
          return null;
        }

        if (!array_key_exists($name, $cache)) {
          return null;
        }

        if ($secret_key == null) {
          if (array_key_exists($name, $secrets)) {
            return null;
          }
          return $cache[$name];
        }

        if (!array_key_exists($name, $secrets)) {
          return null;
        }

        if ($secrets[$name] !== $secret_key) {
          return null;
        }

        return $cache[$name];
        break;
      case 'set':
        $cache[$name] = $value;
        if ($secret_key != null) {
          $secrets[$name] = $secret_key;
        }
        return true;
        break;
      case 'delete':
        if (func_num_args() === 3) {
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
        break;
      default:
        break;
    }

    return null;
  }


  /**
   * Returns whether the server is secure by https
   *
   * @param  void
   * @return boolean  whether the server is secure by https
   */
  public function isHTTPS() {
    static $is_https = null;

    if ($is_https === null) {
      $is_https = false;
      $https = $this->env->HTTPS;
      if ($https !== null && 0 !== strcasecmp($https, 'off')) {
        $is_https = true;
      } else if (strpos($this->env->SCRIPT_URI, 'https://') === 0) {
        $is_https = true;
      }
    }
    return $is_https;
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
      $docroot = PunyApp_Util::cleanFilePath($this->env->DOCUMENT_ROOT);
      $filename = PunyApp_Util::cleanFilePath($this->env->SCRIPT_FILENAME);
      $uri = str_replace($docroot, '', $filename);

      $removals = array('<', '>', '*', '\'', '"');
      $uri = PunyApp_Util::cleanFilePath(str_replace($removals, '', dirname($uri)));
      if ($uri === '\\' || $uri === '.' || $uri == null) {
        $uri = $sep;
      }

      $uri = PunyApp_Util::cleanFilePath($uri);
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
   * A handy function that encodes the context string for HTML/XML entities
   *
   * @param  mixed  $html subject string or array or any value
   * @return mixed  encoded value
   */
  public function escapeHTML($html) {
    return PunyApp_Util::escapeHTML($html, $this->_charset);
  }

  /**
   * Send Content-type states header
   *
   * @param  string $type content-type
   * @return void
   */
  public function sendContentType($type = null) {
    if (!headers_sent()) {
      $this->removePoweredByHeader();

      if ($type === null) {
        $type = $this->_mimeType;
      }

      if ($type != null) {
        $header = sprintf('Content-Type: %s', $type);
        if ($this->_charset != null) {
          $header .= sprintf('; charset=%s', $this->_charset);
        }
        header($header);
      }
    }
  }


  /**
   * Remove X-Powered-By: PHP x.x.x header
   */
  public function removePoweredByHeader() {
    static $removed = false;

    if (!$removed && !headers_sent()) {
      if (function_exists('header_remove')) {
        header_remove('X-Powered-By');
      } else {
        header('X-Powered-By: ');
      }
      $removed = true;
    }
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
      $this->removePoweredByHeader();
      header('Location: ' . $url);
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
