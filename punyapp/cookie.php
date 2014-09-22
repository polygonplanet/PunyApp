<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Cookie
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Cookie
 */
class PunyApp_Cookie implements Iterator {

  /**
   * @var PunyApp
   */
  public $app = null;

  /**
   * @var array cookie vars
   */
  private $_cookies = array();

  /**
   * Constructor
   *
   * @param PunyApp
   */
  public function __construct(PunyApp $app) {
    $this->app = $app;
    $this->_cookies = array();

    if (isset($_COOKIE) && is_array($_COOKIE)) {
      $this->_cookies = $_COOKIE;
    }
  }

  /**
   * Get raw cookie value
   *
   * @param string $name
   * @return string
   */
  public function getRaw($name) {
    $value = null;
    if (isset($this->_cookies[$name])) {
      $value = $this->_cookies[$name];
    }
    return $value;
  }


  public function __get($name) {
    $value = null;
    if (isset($this->_cookies[$name])) {
      $value = $this->decrypt($this->_cookies[$name]);
    }
    return $value;
  }


  public function __set($name, $value) {
    $value = $this->encrypt($value);
    $this->_cookies[$name] = $value;
    $this->send($name);
  }


  public function __isset($name) {
    return isset($this->_cookies[$name]);
  }


  public function __unset($name) {
    unset($this->_cookies[$name]);
    $this->delete($name);
  }


  public function rewind() {
    $value = reset($this->_cookies);
    $key = key($this->_cookies);
    if ($key !== null) {
      $value = $this->decrypt($value);
    }
    return $value;
  }


  public function current() {
    $value = current($this->_cookies);
    $key = key($this->_cookies);
    if ($key !== null) {
      $value = $this->decrypt($value);
    }
    return $value;
  }


  public function key() {
    return key($this->_cookies);
  }


  public function next() {
    $value = next($this->_cookies);
    $key = key($this->_cookies);
    if ($key !== null) {
      $value = $this->decrypt($value);
    }
    return $value;
  }


  public function valid() {
    return key($this->_cookies) !== null;
  }

  /**
   * Send a cookie
   *
   * @param string $name
   * @param bool $raw
   * @param mixed $expire
   * @param bool $secure
   * @param bool $httponly
   * @return bool
   */
  public function send($name, $raw = false, $expire = -1, $secure = false, $httponly = false) {
    $result = false;

    if (!isset($this->_cookies[$name])) {
      return false;
    }

    if (!headers_sent()) {
      $domain = $this->app->env->HTTP_HOST;
      $path = $this->app->getBaseURI();

      if ($expire === -1) {
        $expire = time() + 315360000; // 10 years
      } else if (is_numeric($expire)) {
        $expire = (int)$expire;
      } else {
        $expire = strtotime($expire);
      }

      $value = $this->_cookies[$name];
      if ($raw) {
        $value = $this->decrypt($value);
      }

      $result = setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);

      if ($result) {
        $this->_cookies[$name] = $value;
        if (isset($_COOKIE) && is_array($_COOKIE)) {
          $_COOKIE[$name] = $value;
        }
      }
    }

    return $result;
  }

  /**
   * Delete a cookie
   *
   * @param string $name
   * @return bool
   */
  public function delete($name) {
    $result = false;

    if (!headers_sent()) {
      $domain = $this->app->env->HTTP_HOST;
      $path = $this->app->getBaseURI();

      $result = setcookie($name, '', time() - 3600, $path, $domain);

      if ($result) {
        unset($this->_cookies[$name]);
        if (isset($_COOKIE) && is_array($_COOKIE)) {
          unset($_COOKIE[$name]);
        }
      }
    }

    return $result;
  }

  /**
   * Encrypt data
   *
   * @param string $data
   * @return string
   */
  protected function encrypt($data) {
    return $this->app->encrypt($data);
  }

  /**
   * Decrypt data
   *
   * @param string $data
   * @return string
   */
  protected function decrypt($data) {
    return $this->app->decrypt($data);
  }
}
