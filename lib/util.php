<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Util
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       http://polygonpla.net/
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Util
 */
class PunyApp_Util {

  /**
   * Get current time in milliseconds
   *
   * @return int
   */
  public static function now() {
    $milliseconds = round(microtime(true) * 1000);
    return $milliseconds;
  }

  /**
   * Convert dashes to CamelCase
   *
   * @param string $string target string
   * @param boolean $ucfirst = false
   * @return string
   */
  public static function camelize($string, $ucfirst = false) {
    $parts = preg_split('/[-_]/', strtolower($string), -1, PREG_SPLIT_NO_EMPTY);

    if ($ucfirst) {
      $first = '';
    } else {
      $first = array_shift($parts);
    }

    return $first . implode('', array_map('ucfirst', $parts));
  }

  /**
   * Convert CamelCase to Underscored string
   *
   * @param string $camelcased target string
   * @return string
   */
  public static function underscore($camelcased) {
    return strtolower(preg_replace('/(?<=\w)([A-Z])/', '_$1', $camelcased));
  }

  /**
   * Return string length
   *
   * @param string $string
   * @param string $charset
   * @return int
   */
  public static function length($string, $charset = null) {
    static $has_mbstring = null, $has_iconv = null;

    if ($has_mbstring === null) {
      $has_mbstring = extension_loaded('mbstring');
      $has_iconv = extension_loaded('iconv');
    }

    if ($has_mbstring) {
      if ($charset == null) {
        return mb_strlen($string);
      } else {
        return mb_strlen($string, $charset);
      }
    }

    if ($has_iconv) {
      if ($charset == null) {
        return iconv_strlen($string);
      } else {
        return iconv_strlen($string, $charset);
      }
    }

    return strlen(utf8_decode($string));
  }

  /**
   * Returns full path as realpath() function behaves
   *
   * @param  string $path file path
   * @param  boolean $check_exists checks then return valid path or FALSE
   * @return string  the file full path
   */
  public static function fullPath($path, $check_exists = false) {
    static $backslash = '\\', $slash = '/', $colon = ':', $is_win = null;

    if ($is_win === null) {
      $is_win = 0 === strncasecmp(PHP_OS, 'win', 3);
    }

    $result = '';
    $fullpath = $path;
    $pre0 = substr($path, 0, 1);
    $pre1 = substr($path, 1, 1);

    if ((!$is_win && $pre0 !== $slash)
      || ($is_win && $pre1 !== $colon)) {
      $fullpath = getcwd() . $slash . $path;
    }

    $fullpath = strtr($fullpath, $backslash, $slash);
    $items = explode($slash, $fullpath);
    $new_items = array();

    foreach ($items as $item) {
      if ($item == null || 0 === strpos($item, '.')) {
        if ($item === '..') {
          array_pop($new_items);
        }
        continue;
      }
      $new_items[] = $item;
    }

    $fullpath = implode($slash, $new_items);
    if (!$is_win) {
      $fullpath = $slash . $fullpath;
    }
    $result = $fullpath;

    if ($check_exists) {
      clearstatcache();
      if (!@file_exists($result)) {
        $result = false;
      }
    }
    return $result;
  }

  /**
   * Returns the normalized file path
   *
   * @param  string $path file path
   * @param  string $delim directory delimiter
   * @return string  file path that is cleaned
   */
  public static function cleanFilePath($path, $delimiter = '/') {
    $result = '';

    if (is_string($path)) {
      $delim = (string)$delimiter;
      $result = strtr(trim($path), '\\', '/');
      $pre = '';

      $pos = strpos($result, '://');
      if ($pos === false) {
        $pos = 0;
      } else {
        $pos += 3;
        $pre = substr($result, 0, $pos);
      }

      $result = $pre . implode($delim,
        array_filter(explode('/', substr($result, $pos)), 'strlen'));
    }

    return $result;
  }

  /**
   * Encodes the context string for HTML entities
   *
   * @param  mixed  $html subject string or array or any value
   * @param  string $charset
   * @return mixed  encoded value
   */
  public static function escapeHTML($html, $charset = null) {
    $result = null;

    if (is_array($html)) {
      $result = array();
      foreach ($html as $key => $val) {
        $key = self::escapeHTML($key);
        $val = self::escapeHTML($val);
        $result[$key] = $val;
      }
    } else if (is_string($html) && !self::isHTMLEscaped($html)) {
      if ($charset != null) {
        $result = htmlspecialchars($html, ENT_QUOTES, $charset);
      } else {
        $result = htmlspecialchars($html, ENT_QUOTES);
      }
    } else {
      $result = $html;
    }

    return $result;
  }

  /**
   * Checks whether the value is escaped as HTML string
   *
   * @param string $string
   * @return bool
   */
  public static function isHTMLEscaped($string) {
    return (bool)preg_match(
      '/^(?:[^<>"\'&]|&(?:[a-z]\w{0,24}|#(?:x[0-9a-f]{1,8}|[0-9]{1,10}));)*$/i',
      $string
    );
  }

  /**
   * Encode base64 for URL
   *
   * @param string $data
   * @return string
   */
  public static function base64URLEncode($data) {
    return rtrim(
      strtr(
        base64_encode($data),
        '+/',
        '-_'
      ),
      '='
    );
  }

  /**
   * Decode base64 for URL
   *
   * @param string $data
   * @return string
   */
  public static function base64URLDecode($data) {
    return base64_decode(
      str_pad(
        strtr($data, '-_', '+/'),
        strlen($data) % 4,
        '=',
        STR_PAD_RIGHT
      )
    );
  }

  /**
   * Get files
   *
   * @param string $dir
   * @param string $ext
   * @return array
   */
  public static function getFiles($dir, $ext = null) {
    $results = array();

    $pattern = '/*';
    if ($ext != null) {
      $pattern .= $ext;
    }

    $files = @glob(rtrim(self::cleanFilePath($dir), '/') . $pattern);
    if (!$files) {
      return array();
    }

    foreach ($files as $file) {
      if (is_file($file)) {
        $results[] = $file;
      }
      if (is_dir($file)) {
        $results = array_merge($results, self::getFiles($file, $ext));
      }
    }

    return $results;
  }
}
