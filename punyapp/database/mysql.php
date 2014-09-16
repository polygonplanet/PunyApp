<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Database/MySQL
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Database_MySQL
 */
class PunyApp_Database_MySQL extends PunyApp_Database_Common {

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
    $statement = sprintf('SHOW FULL COLUMNS FROM %s', $table_name);
    $stmt = $this->database->query($statement);

    while (($row = $stmt->fetch(PDO::FETCH_OBJ))) {
      $length = null;
      if (preg_match('/[(]\s*(\d+)\s*[)]/', $row->Type, $m)) {
        $length = (int)$m[1];
      }

      $results[$row->Field] = array(
        'type' => $row->Type,
        'null' => $row->Null === 'YES',
        'default' => $row->Default,
        'length' => $length
      );

      if (!empty($row->Key) && $row->Key === 'PRI') {
        $results[$row->Field]['primaryKey'] = true;
      }
    }

    PunyApp::store('set', $cache_key, $results);
    return $results;
  }
}
