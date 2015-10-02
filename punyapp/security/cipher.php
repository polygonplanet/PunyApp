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
 * @copyright  Copyright (c) 2014-2015 polygon planet
 */

/**
 * @name PunyApp_Security_Cipher
 */
class PunyApp_Security_Cipher {

  /**
   * @var string cipher
   */
  public $cipher = 'blowfish';

  /**
   * @var string mode
   */
  public $mode = 'ecb';

  /**
   * Constructor
   *
   * @param string $key
   */
  public function __construct($key = null) {
    if ($key != null) {
      $this->_setStoreKey($key);
    }
  }

  /**
   * Check whether cipher enabled
   *
   * @return bool
   */
  public function isEnabled() {
    return extension_loaded('mcrypt') && defined('MCRYPT_BLOWFISH');
  }

  /**
   * Encrypt
   *
   * @param  string $data
   * @return string encrypted data
   */
  public function encrypt($data) {
    $size = mcrypt_get_block_size($this->cipher, $this->mode);
    $input = $this->_padPKCS5($data, $size);

    $td = mcrypt_module_open($this->cipher, '', $this->mode, '');
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    mcrypt_generic_init($td, $this->_getStoreKey(), $iv);
    $data = mcrypt_generic($td, $input);

    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);

    return $data;
  }

  /**
   * Decrypt
   *
   * @param  string $data
   * @return string decrypted data
   */
  public function decrypt($data) {
    $td = mcrypt_module_open($this->cipher, '', $this->mode, '');
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    mcrypt_generic_init($td, $this->_getStoreKey(), $iv);

    $data = mdecrypt_generic($td, $data);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);

    $data = $this->_unpadPKCS5($data);
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


  public function __get($name) {
    return null;
  }


  public function __set($name, $value) {
    if ($name === 'key') {
      $this->_setStoreKey($value);
    } else {
      $this->{$name} = $value;
    }
  }
}
