<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Env
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       http://polygonpla.net/
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Env
 */
class PunyApp_Env implements Iterator {

  /**
   * @var array
   */
  private $_env = null;

  /**
   * Constructor
   */
  public function __construct() {
    $this->_env = array_merge(
      isset($_ENV) ? (array)$_ENV : array(),
      isset($_SERVER) ? (array)$_SERVER : array()
    );
  }

  /**
   * Gets the environment value(s)
   *
   * @param string $name key name
   * @return mixed environment value(s)
   */
  public function getEnv($name = null) {
    if ($name === null) {
      return $this->_env;
    }
    return $this->_getEnv($name);
  }

  /**
   * Gets an environment variable from available sources
   *
   * @param  string  environment variable name
   * @return mixed   environment variable value, or NULL
   */
  private function _getEnv($name, $recursive = 0) {
    $result = null;

    if (isset($this->_env[$name])) {
      return $this->_env[$name];
    }

    if (function_exists('getenv')) {
      $result = @getenv($name);
      if ($result === false) {
        $result = null;
      }
    }

    if ($result === null && function_exists('apache_getenv')) {
      $result = @apache_getenv($key);
      if ($result === false) {
        $result = null;
      }
    }

    if ($result === null && $recursive < 2) {
      $recursive++;

      if ($recursive === 2 && substr($name, 0, 4) !== 'HTTP') {
        $name = 'HTTP_' . $name;
      }

      if (strpos($name, '_') === false) {
        return $this->_getEnv(strtoupper(PunyApp_Util::underscore($name)), $recursive);
      } else {
        return $this->_getEnv(strtoupper($name), $recursive);
      }
    }

    return $result;
  }


  public function __get($name) {
    return $this->_getEnv($name);
  }


  public function __set($name, $value) {
    $this->_env[$name] = $value;
  }


  public function __isset($name) {
    return $this->_getEnv($name) !== null;
  }


  public function __unset($name) {
    unset($this->_env[$name]);
  }


  public function rewind() {
    return reset($this->_env);
  }


  public function current() {
    return current($this->_env);
  }


  public function key() {
    return key($this->_env);
  }


  public function next() {
    return next($this->_env);
  }


  public function valid() {
    return key($this->_env) !== null;
  }
}
