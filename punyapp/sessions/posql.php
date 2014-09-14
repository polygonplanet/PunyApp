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
 * @name PunyApp_Session_Posql
 *
 * Session storage class with Posql.
 */
class PunyApp_Session_Posql extends PunyApp_Session_Common {

  /**
   * @var Posql database
   */
  protected $_db = null;

  /**
   * @var bool maintains whether the database is able to vacuum
   */
  protected $_vacuumble = true;

  /**
   * @var string database creation schema
   */
  protected $_schema = "CREATE TABLE IF NOT EXISTS %s (
    id       varchar(255) NOT NULL default '',
    data     text,
    expire   integer default NULL,
    updateAt integer default NULL
  )";

  /**
   * Create Database instance
   *
   * @return Posql
   */
  public static function createDatabaseInstance() {
    $db = new Posql();
    $db->_setRegistered(true);
    $db->setAutoVacuum(false);
    $db->open(self::$sessionFileName);
    return $db;
  }

  /**
   * Check database error
   *
   * @return bool
   */
  public static function isError() {
    $result = false;
    $self = PunyApp::getInstance(self::$className);

    if ($self->_db->isError()) {
      $result = true;
    }

    return $result;
  }
}
