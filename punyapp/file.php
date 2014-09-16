<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   File
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_File
 */
class PunyApp_File {

  /**
   * @var string file path
   */
  public $path = null;

  /**
   * @var resource file handler
   */
  public $handle = null;

  /**
   * @var bool
   */
  private $_opened = false;

  /**
   * Constructor
   *
   * @param string $path file path
   * @param bool $create
   */
  public function __construct($path, $create = false) {
    $this->path = $path;

    if ($create) {
      $this->create();
    }
    if (!$this->exists()) {
      throw new PunyApp_Error("Cannot access file '{$this->path}'");
    }
  }

  /**
   * Destructor
   */
  public function __destruct() {
    $this->close();
  }

  /**
   * Create file
   *
   * @return bool
   */
  public function create() {
    if (!$this->exists()) {
      return touch($this->path);
    }
    return false;
  }

  /**
   * Check whether file is exists
   *
   * @return bool
   */
  public function exists() {
    clearstatcache();
    if (file_exists($this->path) && is_file($this->path)) {
      return true;
    }
    return false;
  }

  /**
   * Open file
   *
   * @param string $mode
   * @return bool
   */
  public function open($mode) {
    if (is_resource($this->handle) && $this->_opened) {
      return true;
    }

    $this->_opened = false;
    if (strpos($mode, 'b') === false) {
      $mode .= 'b';
    }

    $this->handle = fopen($this->path, $mode);
    if ($this->handle && is_resource($this->handle)) {
      if ($this->isWriteMode($mode)) {
        stream_set_write_buffer($this->handle, 0);
      }
      $this->_opened = true;
    }

    return $this->_opened;
  }

  /**
   * Close file
   *
   * @return bool
   */
  public function close() {
    if (is_resource($this->handle) && fclose($this->handle)) {
      $this->handle = null;
      $this->_opened = false;
      return true;
    }
    return false;
  }

  /**
   * Read data
   *
   * @param int $bytes
   * @return string
   */
  public function read($bytes = null) {
    $data = '';

    if ($this->open('r')) {
      if (!flock($this->handle, LOCK_SH)) {
        return false;
      }

      if (is_int($bytes)) {
        $data = fread($this->handle, $bytes);
      } else {
        while (!feof($this->handle)) {
          $data .= fread($this->handle, 0x2000);
        }
      }

      flock($this->handle, LOCK_UN);
      if (!is_int($bytes)) {
        $this->close();
      }
    }

    return $data;
  }

  /**
   * Write data
   *
   * @param string $data
   * @return bool
   */
  public function write($data) {
    $result = false;

    if ($this->open('r+')) {
      if (!flock($this->handle, LOCK_EX)) {
        return false;
      }

      $abort = ignore_user_abort(1);
      if (ftruncate($this->handle, 0) && rewind($this->handle) &&
          fwrite($this->handle, $data, strlen($data)) !== false) {
        $result = true;
      }
      ignore_user_abort($abort);
      flock($this->handle, LOCK_UN);
    }

    return $result;
  }

  /**
   * Append data
   *
   * @param string $data
   * @return bool
   */
  public function append($data) {
    $result = false;

    if ($this->open('a')) {
      if (!flock($this->handle, LOCK_EX)) {
        return false;
      }

      $abort = ignore_user_abort(1);
      if (fwrite($this->handle, $data, strlen($data)) !== false) {
        $result = true;
      }
      ignore_user_abort($abort);
      flock($this->handle, LOCK_UN);
    }

    return $result;
  }


  /**
   * Checks whether fopen()'s mode is write
   *
   * @param string $mode
   * @return bool
   */
  public function isWriteMode($mode) {
    return strpos($mode, '+') !== false ||
           strpos($mode, 'a') !== false ||
           strpos($mode, 'w') !== false ||
           strpos($mode, 'c') !== false ||
           strpos($mode, 'x') !== false;
  }
}
