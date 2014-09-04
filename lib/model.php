<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Model
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       http://polygonpla.net/
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Model
 */
class PunyApp_Model {

  /**
   * @var PunyApp
   */
  public $app = null;

  /**
   * @var PunyApp_Database
   */
  public $database = null;

  /**
   * @var string tablename
   */
  public $tableName = null;

  /**
   * Find data
   *
   * @param array $fields
   * @param array $conditions
   * @param array $params
   * @return array result rows
   */
  public function find($fields = array(), $conditions = array(), $params = array()) {
    return $this->_find($fields, $conditions, null, $params);
  }

  /**
   * Find a data
   *
   * @param array $fields
   * @param array $conditions
   * @param array $params
   * @return array result row
   */
  public function findOne($fields = array(), $conditions = array(), $params = array()) {
    $results = $this->_find($fields, $conditions, 'LIMIT 1', $params);
    return isset($results, $results[0]) ? $results[0] : $results;
  }

  /**
   * Count data
   *
   * @param array $conditions
   * @param array $params
   * @return int count
   */
  public function count($conditions = array(), $params = array()) {
    $sql = sprintf('SELECT COUNT(*) FROM %s WHERE %s',
      $this->tableName,
      $this->_createConditions($conditions)
    );

    $stmt = $this->database->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn(0);
  }

  /**
   * Insert data
   *
   * @param array $fields
   * @param array $params
   * @return boolean
   */
  public function insert($fields = array(), $params = array()) {
    $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)',
      $this->tableName,
      $this->_joinValues(array_keys($fields)),
      $this->_joinValues(array_values($fields))
    );

    $stmt = $this->database->prepare($sql);
    return $stmt->execute($params);
  }

  /**
   * Update data
   *
   * @param array $fields
   * @param array $conditions
   * @param array $params
   * @return int affected rows
   */
  public function update($fields = array(), $conditions = array(), $params = array()) {
    $sql = sprintf('UPDATE %s SET %s WHERE %s',
      $this->tableName,
      $this->_joinKeyValues($fields),
      $this->_createConditions($conditions)
    );

    $stmt = $this->database->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
  }

  /**
   * Delete data
   *
   * @param array $conditions
   * @param array $params
   * @return int affected rows
   */
  public function delete($conditions = array(), $params = array()) {
    $sql = sprintf('DELETE FROM %s WHERE %s',
      $this->tableName,
      $this->_createConditions($conditions)
    );

    $stmt = $this->database->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
  }

  /**
   * Query
   *
   * @param string $sql
   * @param array $params
   * @return array result rows
   */
  public function query($sql, $params = array()) {
    $stmt = $this->database->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Exec
   *
   * @param string $sql
   * @param array $params
   * @return int affected rows
   */
  public function exec($sql, $params = array()) {
    $stmt = $this->database->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
  }

  /**
   * Find data
   *
   * @param array $fields
   * @param array $conditions
   * @param string $extra
   * @param array $params
   * @return array result rows
   */
  private function _find($fields = array(),
                         $conditions = array(),
                         $extra = null,
                         $params = array()) {

    $sql = sprintf('SELECT %s FROM %s WHERE %s',
      $this->_joinValues($fields),
      $this->tableName,
      $this->_createConditions($conditions)
    );

    if ($extra != null) {
      $sql .= $extra;
    }

    $stmt = $this->database->prepare($sql);
    $stmt->execute($params);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;
  }

  /**
   * Query
   *
   * @param string $sql
   * @param array $params
   * @return array result rows
   */
  private function _query($sql, $params = array()) {
    $stmt = $this->database->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Create conditions
   *
   * @param array $conditions
   */
  private function _createConditions($conditions = array()) {
    if (empty($conditions)) {
      $conditions = array(1 => 1);
    }

    if (!is_array($conditions)) {
      return preg_replace('{^\s*WHERE\b\s*}i', '', $conditions);
    }

    $exprs = array();
    foreach ($conditions as $key => $val) {
      if (is_array($val)) {
        $val = $key . $this->_createOperators($val);
      } else {
        $val = $key . ' = ' . $val;
      }
      $exprs[] = $val;
    }

    return implode(' AND ', $exprs);
  }

  /**
   * Create operators
   *
   * @param array $operators
   * @return string
   */
  private function _createOperators($operators = array()) {
    $value = reset($operators);
    $operator = key($operators);

    switch (strtolower($operator)) {
      case '$gt':
        return ' > ' . $value;
      case '$gte':
        return ' >= ' . $value;
      case '$lt':
        return ' < ' . $value;
      case '$lte':
        return ' <= ' . $value;
      case '$ne':
        return ' <> ' . $value;
      case '$in':
        return ' IN (' . $this->_joinValues($value) . ')';
      case '$nin':
        return ' NOT IN (' . $this->_joinValues($value) . ')';
      case '$or':
        return ' OR ' . $value;
      case '$and':
        return ' AND ' . $value;
      case '$not':
        return ' NOT ' . $value;
      case '$like':
        return ' LIKE ' . $value;
      case '$nlike':
        return ' NOT LIKE ' . $value;
    }
  }

  /**
   * Join values
   *
   * @param mixed $values
   * @return string
   */
  private function _joinValues($values) {
    if (is_array($values)) {
      return implode(', ', $values);
    }
    return (string)$values;
  }

  /**
   * Join key value pairs
   *
   * @param mixed $values
   * @return string
   */
  private function _joinKeyValues($values) {
    if (!is_array($values)) {
      return $values;
    }

    $results = array();
    foreach ($values as $key => $val) {
      $results[] = $key . ' = ' . $val;
    }
    return $this->_joinValues($results);
  }
}
