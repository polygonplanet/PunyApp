<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Settings
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Settings
 */
class PunyApp_Settings {

  /**
   * @var bool whether application run for debug or release
   */
  protected $_debug = true;

  /**
   * @var string internal character-code (default = utf-8)
   */
  protected $_charset = 'utf-8';

  /**
   * @var string internal mime-type (default = 'text/html')
   */
  protected $_mimeType = 'text/html';

  /**
   * @var string internal language (e.g. 'ja' or 'en' etc.)
   */
  protected $_lang = 'neutral';

  /**
   * @var string timezone (e.g. 'Asia/Tokyo' or 'America/Chicago' etc.)
   */
  protected $_timezone = null;

  /**
   * @var string memory limit (e.g. '128M')
   */
  protected $_memoryLimit = '128M';

  /**
   * @var int max script execution time (default = 300 Sec. = 5 Min.)
   */
  protected $_maxExecutionTime = 300;

  /**
   * @var string whether flush the output buffering as implicit
   */
  protected $_implicitFlush = false;

  /**
   * @var string the separator which separates URL arguments
   */
  protected $_argSeparatorOutput = '&';

  /**
   * @var string the security salt
   */
  protected $_salt = null;

  /**
   * @var bool whether to log error
   */
  protected $_logError = true;

  /**
   * @var int the maximum item counts of log error
   */
  protected $_logErrorMax = 200;

  /**
   * Update settings with new variables
   */
  protected function _updateSettings() {
    ini_set('display_errors', $this->_debug ? 1 : 0);
    ini_set('log_errors', false);
    ini_set('track_errors', false);
    ini_set('default_mimetype', $this->_mimeType);
    ini_set('default_charset', $this->_charset);
    ini_set('arg_separator.output', $this->_argSeparatorOutput);
    ini_set('memory_limit', $this->_memoryLimit);

    if (!ini_get('safe_mode') &&
      (int)$this->_maxExecutionTime > (int)ini_get('max_execution_time')) {
      set_time_limit($this->_maxExecutionTime);
    }

    if ($this->_implicitFlush) {
      ob_implicit_flush(1);
    }

    if (extension_loaded('mbstring')) {
      $lang = 'neutral';

      if (0 === strncasecmp($this->_charset, 'utf', 3)
       || 0 === strncasecmp($this->_charset, 'ucs', 3)) {
        $lang = 'uni';
      } else if ($this->_lang != null) {
        $lang = $this->_lang;
      }

      mb_language($lang);
      mb_internal_encoding($this->_charset);
      mb_regex_encoding($this->_charset);
      mb_http_output('pass');
      mb_detect_order('auto');
      ini_set('mbstring.http_input', 'pass');
      ini_set('mbstring.func_overload', 0);
    }

    if (extension_loaded('iconv')) {
      iconv_set_encoding('internal_encoding', $this->_charset);
    }
  }

  /**
   * Updates the internal timezone
   */
  protected function _updateTimezone() {
    static $prev_timezone, $enable_timezone = null;

    if ($enable_timezone === null) {
      $enable_timezone = function_exists('date_default_timezone_set')
                      && function_exists('date_default_timezone_get');

      if ($enable_timezone) {
        $prev_timezone = @date_default_timezone_get();
        @date_default_timezone_set($prev_timezone);
        if ($this->_timezone == null) {
          $this->_timezone = $prev_timezone;
        }
      }
    }

    if ($enable_timezone) {
      if ($this->_timezone != null && $this->_timezone !== $prev_timezone) {
        @date_default_timezone_set($this->_timezone);
        $prev_timezone = $this->_timezone;
      }
    }
  }

  /**
   * Gets the debug mode
   *
   * @return bool  return TRUE if run as the debug mode
   */
  public function getDebug() {
    return $this->_debug;
  }

  /**
   * Sets the debug mode
   *
   * @param  bool $debug give TRUE if run as the debug mode
   */
  public function setDebug($debug) {
    $this->_debug = (bool)$debug;
  }

  /**
   * Gets internal character encoding
   *
   * @return string   the internal character encoding
   */
  public function getCharset() {
    return $this->_charset;
  }

  /**
   * Sets internal character encoding
   *
   * @param  string $charset  the internal character encoding
   */
  public function setCharset($charset) {
    if (is_string($charset)) {
      $this->_charset = $charset;
      $this->_updateSettings();
    }
  }

  /**
   * Gets internal mime-type
   *
   * @return string   the internal mime-type
   */
  public function getMimeType() {
    return $this->_mimeType;
  }

  /**
   * Sets internal mime-type
   *
   * @param  string $mime_type  the internal mime-type
   */
  public function setMimeType($mime_type) {
    if (is_string($mime_type)) {
      $this->_mimeType = $mime_type;
      $this->_updateSettings();
    }
  }

  /**
   * Gets internal language
   *
   * @return string   the internal language
   */
  public function getLang() {
    return $this->_lang;
  }

  /**
   * Sets internal language
   *
   * @param  string $lang  the internal language
   */
  public function setLang($lang) {
    if (is_string($lang)) {
      $this->_lang = $lang;
      $this->_updateSettings();
    }
  }

  /**
   * Gets internal timezone
   *
   * @return string   the internal timezone
   */
  public function getTimezone() {
    return $this->_timezone;
  }

  /**
   * Sets internal timezone
   *
   * @param  string $timezone  the internal timezone
   */
  public function setTimezone($timezone) {
    if (is_string($timezone)) {
      $this->_timezone = $timezone;
      $this->_updateTimezone();
    }
  }

  /**
   * Gets internal output argument separator
   *
   * @return string   the internal output argument separator
   */
  public function getArgSeparatorOutput() {
    return $this->_argSeparatorOutput;
  }

  /**
   * Sets internal output argument separator
   *
   * @param  string $arg_separator_output  the internal output argument separator
   */
  public function setArgSeparatorOutput($arg_separator_output) {
    if (is_string($arg_separator_output)) {
      $this->_argSeparatorOutput = $arg_separator_output;
      $this->_updateSettings();
    }
  }

  /**
   * Gets internal memory limit value
   *
   * @return string   the internal memory limit value
   */
  public function getMemoryLimit() {
    return $this->_memoryLimit;
  }

  /**
   * Sets internal memory limit value
   *
   * @param  string $memory_limit  the internal memory limit value
   */
  public function setMemoryLimit($memory_limit) {
    if (is_string($memory_limit) || is_numeric($memory_limit)) {
      $this->_memoryLimit = $memory_limit;
      $this->_updateSettings();
    }
  }

  /**
   * Gets internal max script execution time
   *
   * @return number   the internal max script execution time
   */
  public function getMaxExecutionTime() {
    return $this->_maxExecutionTime;
  }

  /**
   * Sets internal max script execution time
   *
   * @param  number $max_execution_time  the internal max execution time
   */
  public function setMaxExecutionTime($max_execution_time) {
    if (is_string($max_execution_time) || is_numeric($max_execution_time)) {
      $this->_maxExecutionTime = $max_execution_time;
      $this->_updateSettings();
    }
  }

  /**
   * Gets the implicit flush mode
   *
   * @return bool  whether flush the output buffering as implicit
   */
  public function getImplicitFlush() {
    return $this->_implicitFlush;
  }

  /**
   * Sets the implicit flush mode
   *
   * @param  bool $implicit_flush whether flush the output buffering as implicit
   */
  public function setImplicitFlush($implicit_flush) {
    $this->_implicitFlush = (bool)$implicit_flush;
    $this->_updateSettings();
  }

  /**
   * Gets the salt
   *
   * @return string salt
   */
  public function getSalt() {
    return $this->_salt;
  }

  /**
   * Sets the security salt
   *
   * @param string $salt
   */
  public function setSalt($salt) {
    $this->_salt = (string)$salt;
  }

  /**
   * Gets the logError
   *
   * @return bool whether to log error
   */
  public function getLogError() {
    return $this->_logError;
  }

  /**
   * Sets the logError
   *
   * @param bool $log_error
   */
  public function setLogError($log_error) {
    $this->_logError = (bool)$log_error;
  }

  /**
   * Gets the logErrorMax
   *
   * @return int the maximum item counts of log error
   */
  public function getLogErrorMax() {
    return $this->_logErrorMax;
  }

  /**
   * Sets the logErrorMax
   *
   * @param int $log_error_max
   */
  public function setLogErrorMax($log_error_max) {
    $this->_logErrorMax = (int)$log_error_max;
  }
}
