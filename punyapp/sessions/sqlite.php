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
 * @link       https://github.com/polygonplanet/PunyApp
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
   * @var bool maintains whether the database is able to vacuum
   */
  protected $_vacuumble = true;

  /**
   * Create Database instance
   *
   * @return PDO
   */
  public static function createDatabaseInstance() {
    return new PDO('sqlite:' . self::$sessionFileName);
  }
}
