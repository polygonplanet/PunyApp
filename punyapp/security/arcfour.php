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
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Security_Arcfour
 */
class PunyApp_Security_Arcfour {

  const BLOCKSIZE = 1;

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
    return $this->_crypt($this->_padPKCS5($data, self::BLOCKSIZE));
  }

  /**
   * Decrypt
   *
   * @param string $data
   * @return string decrypted data
   */
  public function decrypt($data) {
    return $this->_unpadPKCS5($this->_crypt($data));
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

    if ($key == null || $key === $this->_getStoreKey()) {
      return;
    }

    $matrix = range(0, 255);
    $len = strlen($key);

    for ($i = 0; $i < 256; $i++) {
      $c[$i] = ord($key[$i % $len]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
      $j = ($j + $matrix[$i] + $c[$i]) % 256;
      $t = $matrix[$i];
      $matrix[$i] = $matrix[$j];
      $matrix[$j] = $t;
    }

    $this->_setStoreKey($matrix);
  }

  /**
   * Crypt
   *
   * @param string $data string
   * @return string
   */
  private function _crypt($data) {
    $data = (string)$data;
    $matrix = $this->_getStoreKey();
    $len = strlen($data);

    for ($k = $j = $i = 0; $k < $len; $k++) {
      $i = ($i + 1) % 256;
      $j = ($j + $matrix[$i]) % 256;

      $t = $matrix[$i];
      $matrix[$i] = $matrix[$j];
      $matrix[$j] = $t;

      $t = ($matrix[$i] + $matrix[$j]) % 256;
      $data[$k] = chr(ord($data[$k]) ^ $matrix[$t]);
    }

    return $data;
  }


  private function _padPKCS5($data, $blocksize) {
    $pad = $blocksize - (strlen($data) % $blocksize);
    return $data . str_repeat(chr($pad), $pad);
  }


  private function _unpadPKCS5($data) {
    $pad = ord(substr($data, -1));
    if ($pad > strlen($data)) {
      return false;
    }

    if (strspn($data, chr($pad), strlen($data) - $pad) !== $pad) {
      return false;
    }
    return substr($data, 0, -1 * $pad);
  }


  private function _getStoreKey() {
    $class = __CLASS__;
    return PunyApp::store('get', $class . '-key', $class . '-skey');
  }

  private function _setStoreKey($key) {
    $class = __CLASS__;
    PunyApp::store('set', $class . '-key', $key, $class . '-skey');
  }
}
