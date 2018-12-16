<?php
/**
 * PunyApp: https://github.com/polygonplanet/PunyApp
 * @license MIT
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
      $types = $this->_parseColumnType($row->Type);

      $results[$row->Field] = array(
        'type' => $row->Type,
        'null' => $row->Null === 'YES',
        'default' => $row->Default
      ) + $types;

      if (!empty($row->Key) && $row->Key === 'PRI') {
        $results[$row->Field]['primaryKey'] = true;
      }
    }

    PunyApp::store('set', $cache_key, $results);
    return $results;
  }
}
