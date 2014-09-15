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
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Env
 */
class PunyApp_Env implements Iterator {

  /**
   * Constructor
   */
  public function __construct() {
    $this->_getEnvVar(array_merge(
      isset($_ENV) ? (array)$_ENV : array(),
      isset($_SERVER) ? (array)$_SERVER : array()
    ));
  }

  /**
   * Gets the environment value(s)
   *
   * @param string $name key name
   * @return mixed environment value(s)
   */
  public function getEnv($name = null) {
    if ($name === null) {
      return $this->_getEnvVar();
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

    $env = $this->_getEnvVar();
    if (isset($env[$name])) {
      return $env[$name];
    }

    if (function_exists('getenv')) {
      $result = getenv($name);
      if ($result === false) {
        $result = null;
      }
    }

    if ($result === null && function_exists('apache_getenv')) {
      $result = apache_getenv($name);
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


  /**
   * Get env var
   *
   * @param array $var
   * @return array
   */
  private static function &_getEnvVar($var = null) {
    static $env = array();

    if ($var !== null) {
      $env = $var;
    }

    return $env;
  }



  public function __get($name) {
    return $this->_getEnv($name);
  }


  public function __set($name, $value) {
    $env = &$this->_getEnvVar();
    $env[$name] = $value;
    unset($env);
  }


  public function __isset($name) {
    return $this->_getEnv($name) !== null;
  }


  public function __unset($name) {
    $env = &$this->_getEnvVar();
    unset($env[$name]);
    unset($env);
  }


  public function rewind() {
    $env = &$this->_getEnvVar();
    $result = reset($env);
    unset($env);
    return $result;
  }


  public function current() {
    $env = &$this->_getEnvVar();
    $result = current($env);
    unset($env);
    return $result;
  }


  public function key() {
    $env = &$this->_getEnvVar();
    $result = key($env);
    unset($env);
    return $result;
  }


  public function next() {
    $env = &$this->_getEnvVar();
    $result = next($env);
    unset($env);
    return $result;
  }


  public function valid() {
    $env = &$this->_getEnvVar();
    $result = key($env) !== null;
    unset($env);
    return $result;
  }
}
