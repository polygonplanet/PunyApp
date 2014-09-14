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
 * @name PunyApp_Session_MySQL
 *
 * Session storage class with MySQL.
 */
class PunyApp_Session_MySQL extends PunyApp_Session_Common {

  /**
   * @var PDO database
   */
  protected $_db = null;

  /**
   * @var string database creation schema
   */
  protected $_schema = "CREATE TABLE IF NOT EXISTS %s (
    id       varchar(128) NOT NULL default '',
    data     text,
    expire   int(11) default NULL,
    updateAt int(11) default NULL,
    PRIMARY KEY (id)
  )";

  /**
   * Create Database instance
   *
   * @return PDO
   */
  public static function createDatabaseInstance() {
    $instance = PunyApp::getInstance('PDO');
    return $instance;
  }
}
