<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Cache
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014-2015 polygon planet
 */

/**
 * @name PunyApp_Cache
 */
class PunyApp_Cache implements Iterator {

  /**
   * @const file extension
   */
  const EXT = '.json';

  /**
   * @var PunyApp_File
   */
  private $_file = null;

  /**
   * @var array cache
   */
  private $_cache = null;

  /**
   * Constructor
   *
   * @param string $name cache name
   */
  public function __construct($name) {
    $filename = PUNYAPP_CACHE_DIR . DIRECTORY_SEPARATOR . $name . self::EXT;
    $this->_file = new PunyApp_File($filename, true);
    $this->_cache = $this->_read();
  }

  /**
   * Read cache
   *
   * @return array
   */
  private function _read() {
    $data = $this->_file->read();
    $this->_file->close();
    if ($data == null) {
      $data = '{}';
    }
    return json_decode($data, true);
  }

  /**
   * Write cache
   *
   * @param array $cache
   */
  private function _write($cache) {
    $this->_file->write(json_encode($cache));
    $this->_file->close();
  }


  public function __get($name) {
    return isset($this->_cache[$name]) ? $this->_cache[$name] : null;
  }


  public function __set($name, $value) {
    $this->_cache[$name] = $value;
    $this->_write($this->_cache);
  }


  public function __isset($name) {
    return isset($this->_cache[$name]);
  }


  public function __unset($name) {
    unset($this->_cache[$name]);
    $this->_write($this->_cache);
  }


  public function rewind() {
    return reset($this->_cache);
  }


  public function current() {
    return current($this->_cache);
  }


  public function key() {
    return key($this->_cache);
  }


  public function next() {
    return next($this->_cache);
  }


  public function valid() {
    return key($this->_cache) !== null;
  }
}
