<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Session
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       http://polygonpla.net/
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Session_SQLite
 *
 * Session storage class with SQLite.
 */
class PunyApp_Session_SQLite extends PunyApp_Session_Common {

  /**
   * @var PDO database
   */
  protected $_db = null;

  /**
   * Create Database instance
   *
   * @return PDO
   */
  public static function createDatabaseInstance() {
    return new PDO('sqlite:' . self::$sessionFileName);
  }
}
