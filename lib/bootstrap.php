<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Bootstrap
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       http://polygonpla.net/
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

if (!defined('PDO::ATTR_DRIVER_NAME')) {
  die('To execute process requires the PDO extension.');
}

if (!extension_loaded('pdo_sqlite')) {
  die('To execute process requires the PDO_SQLite extension.');
}

if (version_compare(phpversion(), '5.2.0', '<')) {
  die('To execute process requires PHP version 5.2.0 or higher.');
}


defined('E_DEPRECATED')      or define('E_DEPRECATED', 8192);
defined('E_USER_DEPRECATED') or define('E_USER_DEPRECATED', E_USER_NOTICE);

error_reporting(E_ALL & ~E_DEPRECATED);


define('PUNYAPP', 'PUNYAPP');
define('PUNYAPP_ROOT_DIR', dirname(dirname(__FILE__)));

chdir(PUNYAPP_ROOT_DIR);

define('PUNYAPP_LIB_DIR', PUNYAPP_ROOT_DIR . DIRECTORY_SEPARATOR . 'lib');
define('PUNYAPP_APP_DIR', PUNYAPP_ROOT_DIR . DIRECTORY_SEPARATOR . 'app');
define('PUNYAPP_VENDORS_DIR', PUNYAPP_ROOT_DIR . DIRECTORY_SEPARATOR . 'vendors');

define('PUNYAPP_PUBLIC_DIR', PUNYAPP_APP_DIR . DIRECTORY_SEPARATOR . 'public');
define('PUNYAPP_VIEWS_DIR', PUNYAPP_APP_DIR . DIRECTORY_SEPARATOR . 'views');
define('PUNYAPP_MODELS_DIR', PUNYAPP_APP_DIR . DIRECTORY_SEPARATOR . 'models');
define('PUNYAPP_STORAGE_DIR', PUNYAPP_APP_DIR . DIRECTORY_SEPARATOR . 'storage');
define('PUNYAPP_SETTINGS_DIR', PUNYAPP_APP_DIR . DIRECTORY_SEPARATOR . 'settings');
define('PUNYAPP_CONTROLLERS_DIR', PUNYAPP_APP_DIR . DIRECTORY_SEPARATOR . 'controllers');

define('PUNYAPP_DATABASES_DIR', PUNYAPP_STORAGE_DIR . DIRECTORY_SEPARATOR . 'databases');
define('PUNYAPP_SESSIONS_DIR', PUNYAPP_STORAGE_DIR . DIRECTORY_SEPARATOR . 'sessions');

require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'security' . DIRECTORY_SEPARATOR . 'cipher.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'security' . DIRECTORY_SEPARATOR . 'arcfour.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'security' . DIRECTORY_SEPARATOR . 'token.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'compat.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'util.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'view.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'database.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'model.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'env.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'event.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'request.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'cookie.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'session.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'validator.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'controller.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'dispatcher.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'settings.php';
require_once PUNYAPP_LIB_DIR . DIRECTORY_SEPARATOR . 'punyapp.php';

