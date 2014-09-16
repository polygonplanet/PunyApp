<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Database/Posql
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Database_Posql
 */
class PunyApp_Database_Posql extends PunyApp_Database_Common {

  /**
   * Describe table
   *
   * @param string $table_name
   * @return array
   */
  public function describe($table_name) {
    $cache_key = sprintf('%s-%s', __METHOD__, $table_name);
    $cache = PunyApp::store('get', $cache_key);
    if (!empty($cache)) {
      return $cache;
    }

    $results = array();
    $statement = sprintf('DESCRIBE %s', $this->database->quote($table_name));
    $stmt = $this->database->query($statement);

    while (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
      $row = (array)$row;

      $name = $row['name'];
      $type = $row['type'];
      $default = $row['default'];

      $length = null;
      if (preg_match('/[(]\s*(\d+)\s*[)]/', $type, $m)) {
        $length = (int)$m[1];
      }

      $results[$name] = array(
        'type' => $type,
        'null' => true,
        'default' => $default,
        'length' => $length
      );

      if ($row['key'] === 'primary' || $row['extra'] === 'alias for rowid') {
        $results[$name]['primaryKey'] = true;
        $results[$name]['null'] = false;
      }
    }

    PunyApp::store('set', $cache_key, $results);
    return $results;
  }
}
