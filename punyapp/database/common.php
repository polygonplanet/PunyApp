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
 * @copyright  Copyright (c) 2014-2015 polygon planet
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

  /**
   * Parse column description
   *
   * @param string $type column type (e.g., "varchar(255)")
   * @return array
   */
  protected function _parseColumnType($type) {
    $name = null;
    $length = null;
    $offset = null;

    if (preg_match('/([\w\s]+)(?:\(\s*(\d+)(?:\s*,\s*(\d+)|)\s*\)|)/', $type, $matches)) {
      $name = trim($matches[1]);
      if (isset($matches[2])) {
        $length = $matches[2] - 0;
        if (isset($matches[3])) {
          $offset = $matches[3] - 0;
        }
      }
    }

    return array(
      'name' => $name,
      'length' => $length,
      'offset' => $offset
    );
  }
}
