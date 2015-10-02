<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Database/PgSQL
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014-2015 polygon planet
 */

/**
 * @name PunyApp_Database_PgSQL
 */
class PunyApp_Database_PgSQL extends PunyApp_Database_Common {

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

    // Statement from CakePHP
    $statement = 'SELECT DISTINCT table_schema AS schema, column_name AS name,
      data_type AS type, is_nullable AS null, column_default AS default,
      ordinal_position AS position, character_maximum_length AS char_length,
      character_octet_length AS oct_length
      FROM information_schema.columns
      WHERE table_name = ? ORDER BY position';

    $stmt = $this->database->prepare($statement);
    $stmt->execute(array($table_name));

    while (($row = $stmt->fetch(PDO::FETCH_OBJ))) {
      $type = $row->type;
      if (!empty($row->oct_length) && $row->char_length === null) {
        if ($row->type === 'character varying') {
          $length = null;
          $type = 'text';
        } else if ($row->type === 'uuid') {
          $length = 36;
        } else {
          $length = intval($row->oct_length);
        }
      } else if (!empty($row->char_length)) {
        $length = intval($row->char_length);
      } else {
        $types = $this->_parseColumnType($row->type);
        $length = $types['length'];
      }

      if (empty($length)) {
        $length = null;
      }

      if (preg_match('/^nextval\(\'.+\'::.+\)/', $row->default)) {
        $default = null;
      } else {
        $default = preg_replace('/^\'(.*)\'$/', '$1',
                   preg_replace('/::.*/', '', $row->default));
      }

      $name = preg_replace('/\s+without.+$/i', '', $row->type);

      $results[$row->name] = array(
        'type' => $type,
        'null' => $row->null === 'YES',
        'default' => $default,
        'length' => $length,
        'name' => $name,
        'offset' => null
      );
    }

    PunyApp::store('set', $cache_key, $results);
    return $results;
  }
}
