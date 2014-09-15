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

    if (!isset($this->app->session->_encryptedCookies)) {
      $this->app->session->_encryptedCookies = array();
    }
  }


  public function __get($name) {
    $value = null;
    if (isset($this->_cookies[$name])) {
      $value = $this->_cookies[$name];
      if ($this->_isEncryptedCookie($name)) {
        $value = $this->decrypt($value);
      }
    }
    return $value;
  }


  public function __set($name, $value) {
    $value = $this->encrypt($value);
    $this->_cookies[$name] = $value;
    $this->send($name, $value);
  }


  public function __isset($name) {
    return isset($this->_cookies[$name]);
  }


  public function __unset($name) {
    unset($this->_cookies[$name]);
    $this->delete($name);
  }


  public function rewind() {
    return reset($this->_cookies);
  }


  public function current() {
    return current($this->_cookies);
  }


  public function key() {
    return key($this->_cookies);
  }


  public function next() {
    return next($this->_cookies);
  }


  public function valid() {
    return key($this->_cookies) !== null;
  }

  /**
   * Send a cookie
   *
   * @param string $name
   * @param string $value
   * @param mixed $expire
   * @param boolean $secure
   * @param boolean $httponly
   * @return boolean
   */
  public function send($name, $value, $expire = -1, $secure = false, $httponly = false) {
    $result = false;

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

      $result = setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);

      if ($result) {
        $this->_cookies[$name] = $value;
        if (isset($_COOKIE) && is_array($_COOKIE)) {
          $_COOKIE[$name] = $value;
        }
        $this->_addEncryptedCookie($name);
      }
    }

    return $result;
  }

  /**
   * Delete a cookie
   *
   * @param string $name
   * @return boolean
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
        $this->_deleteEncryptedCookie($name);
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

  /**
   * Add encrypted cookie name
   *
   * @param string $name
   */
  private function _addEncryptedCookie($name) {
    $encrypted_cookies = $this->app->session->_encryptedCookies;
    if (empty($encrypted_cookies)) {
      $encrypted_cookies = array();
    }
    $encrypted_cookies[$name] = 1;
    $this->app->session->_encryptedCookies = $encrypted_cookies;
  }

  /**
   * Delete encrypted cookie name
   *
   * @param string $name
   */
  private function _deleteEncryptedCookie($name) {
    $encrypted_cookies = $this->app->session->_encryptedCookies;
    if (empty($encrypted_cookies)) {
      $encrypted_cookies = array();
    }
    unset($encrypted_cookies[$name]);
    $this->app->session->_encryptedCookies = $encrypted_cookies;
  }

  /**
   * Check whether the argument name is encrypted cookie
   *
   * @param string $name
   * @return bool
   */
  private function _isEncryptedCookie($name) {
    $encrypted_cookies = $this->app->session->_encryptedCookies;
    if (empty($encrypted_cookies)) {
      $encrypted_cookies = array();
    }

    return isset($encrypted_cookies[$name]);
  }
}
