<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Security/Cipher
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       http://polygonpla.net/
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Security_Arcfour
 */
class PunyApp_Security_Arcfour {

  /**
   * Constructor
   *
   * @param string $key
   */
  public function __construct($key = null) {
    if ($key !== null) {
      $this->_setKey($key);
    }
  }

  /**
   * Encrypt
   *
   * @param  string $data
   * @return string encrypted data
   */
  public function encrypt($data) {
    return $this->_crypt($data);
  }

  /**
   * Decrypt
   *
   * @param string $data
   * @return string decrypted data
   */
  public function decrypt($data) {
    return $this->_crypt($data);
  }


  public function __get($name) {
    return null;
  }


  public function __set($name, $value) {
    if ($name === 'key') {
      $this->_setKey($value);
    }
  }

  /**
   * initialize key
   *
   * @param string $key key
   */
  private function _setKey($key) {
    $key = (string)$key;

    if ($key == null || $key === $this->_getCacheKey()) {
      return;
    }

    $s = range(0, 255);
    $l = strlen($key);

    for ($i = 0; $i < 256; $i++) {
      $c[$i] = ord($key[$i % $l]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
      $j = ($j + $s[$i] + $c[$i]) % 256;
      $t = $s[$i];
      $s[$i] = $s[$j];
      $s[$j] = $t;
    }

    $this->_setCacheKey($s);
  }

  /**
   * Crypt
   *
   * @param string $a string
   * @return string
   */
  private function _crypt($a) {
    $s = $this->_getCacheKey();
    $l = strlen($a);

    for ($k = $j = $i = 0; $k < $l; $k++) {
      $i = ($i + 1) % 256;
      $j = ($j + $s[$i]) % 256;

      $t = $s[$i];
      $s[$i] = $s[$j];
      $s[$j] = $t;

      $t = ($s[$i] + $s[$j]) % 256;
      $a[$k] = chr(ord($a[$k]) ^ $s[$t]);
    }

    return $a;
  }


  private function _getCacheKey() {
    $class = __CLASS__;
    return PunyApp::cache('get', $class . '-key', $class . '-skey');
  }

  private function _setCacheKey($key) {
    $class = __CLASS__;
    PunyApp::cache('set', $class . '-key', $key, $class . '-skey');
  }
}
