<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Database/Common
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Database_Common
 */
abstract class PunyApp_Database_Common {

  /**
   * @var PunyApp_Database
   */
  public $database = null;

  /**
   * Constructor
   *
   * @param PunyApp_Database
   */
  public function __construct(PunyApp_Database $database) {
    $this->database = $database;
  }

  /**
   * Describe table
   *
   * @param string $table_name
   * @return array
   */
  abstract public function describe($table_name);
}
