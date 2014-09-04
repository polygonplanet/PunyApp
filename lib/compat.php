<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Compat
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       http://polygonpla.net/
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * Provides missing functionality in the form of constants and functions
 *   for older versions of PHP
 */

/**
 * Provides the error constant E_*
 *
 * @link        http://php.net/errorfunc.constants
 * @since       PHP 5      (E_STRICT)
 * @since       PHP 5.2.0  (E_RECOVERABLE_ERROR)
 * @since       PHP 5.3.0  (E_DEPRECATED)
 * @since       PHP 5.3.0  (E_USER_DEPRECATED)
 */
defined('E_STRICT')            or define('E_STRICT',             2048);
defined('E_RECOVERABLE_ERROR') or define('E_RECOVERABLE_ERROR',  4096);
defined('E_DEPRECATED')        or define('E_DEPRECATED',         8192);
defined('E_USER_DEPRECATED')   or define('E_USER_DEPRECATED',   16384);

/**
 * Provides filesystem constants
 *
 * @link        http://php.net/ref.filesystem
 * @since       PHP 5
 */
defined('FILE_USE_INCLUDE_PATH')   or define('FILE_USE_INCLUDE_PATH',    1);
defined('FILE_IGNORE_NEW_LINES')   or define('FILE_IGNORE_NEW_LINES',    2);
defined('FILE_SKIP_EMPTY_LINES')   or define('FILE_SKIP_EMPTY_LINES',    4);
defined('FILE_APPEND')             or define('FILE_APPEND',              8);
defined('FILE_NO_DEFAULT_CONTEXT') or define('FILE_NO_DEFAULT_CONTEXT', 16);

/**
 * Provides lcfirst()
 *
 * @link        http://php.net/function.lcfirst
 * @since       PHP 5 >= 5.3.0
 */
if (!function_exists('lcfirst')) {
  function lcfirst($str) {
    $str[0] = strtolower($str[0]);
    return (string)$str;
  }
}
