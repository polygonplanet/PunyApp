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
 * @link       https://github.com/polygonplanet/PunyApp
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
   * @param array $query
   * @param array $params
   * @return array result rows
   */
  public function find($query = array(), $params = array()) {
    return $this->_find($query, (array)$params);
  }

  /**
   * Find a data
   *
   * @param array $query
   * @param array $params
   * @return array result row
   */
  public function findOne($query = array(), $params = array()) {
    $query['limit'] = 1;
    $results = $this->_find($query, (array)$params);
    return isset($results, $results[0]) ? $results[0] : array();
  }

  /**
   * Find a data
   *
   * @param array $query
   * @param array $params
   * @return mixed result column
   */
  public function findColumn($query = array(), $params = array()) {
    $statement = $this->_buildStatement($query);
    $stmt = $this->database->prepare($statement);
    $stmt->execute((array)$params);
    return $stmt->fetchColumn(0);
  }

  /**
   * Count data
   *
   * @param array $query
   * @param array $params
   * @return int count
   */
  public function count($query = array(), $params = array()) {
    $query['fields'] = 'COUNT(*)';
    return (int)$this->findColumn($query, $params);
  }

  /**
   * Has data
   *
   * @param array $query
   * @param array $params
   * @return bool
   */
  public function has($query = array(), $params = array()) {
    return $this->count($query, $params) > 0;
  }

  /**
   * Insert data
   *
   * @param array $fields
   * @param array $params
   * @return boolean
   */
  public function insert($fields = array(), $params = array()) {
    $statement = sprintf('INSERT INTO %s (%s) VALUES (%s)',
      $this->tableName,
      $this->_joinValues(array_keys($fields)),
      $this->_joinValues(array_values($fields))
    );

    $stmt = $this->database->prepare($statement);
    return $stmt->execute((array)$params);
  }

  /**
   * Replace data
   *
   * @param array $fields
   * @param array $params
   * @return boolean
   */
  public function replace($fields = array(), $params = array()) {
    $statement = sprintf('REPLACE INTO %s (%s) VALUES (%s)',
      $this->tableName,
      $this->_joinValues(array_keys($fields)),
      $this->_joinValues(array_values($fields))
    );

    $stmt = $this->database->prepare($statement);
    return $stmt->execute((array)$params);
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
    $statement = sprintf('UPDATE %s SET %s WHERE %s',
      $this->tableName,
      $this->_joinKeyValues($fields),
      $this->_createConditions($conditions)
    );

    $stmt = $this->database->prepare($statement);
    $stmt->execute((array)$params);
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
    $statement = sprintf('DELETE FROM %s WHERE %s',
      $this->tableName,
      $this->_createConditions($conditions)
    );

    $stmt = $this->database->prepare($statement);
    $stmt->execute((array)$params);
    return $stmt->rowCount();
  }

  /**
   * Query
   *
   * @param string $statement
   * @param array $params
   * @return array result rows
   */
  public function query($statement, $params = array()) {
    $stmt = $this->database->prepare($statement);
    $stmt->execute((array)$params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Exec
   *
   * @param string $statement
   * @param array $params
   * @return int affected rows
   */
  public function exec($statement, $params = array()) {
    $stmt = $this->database->prepare($statement);
    $stmt->execute((array)$params);
    return $stmt->rowCount();
  }

  /**
   * Query
   *
   * @param string $statement
   * @param array $params
   * @return array result rows
   */
  private function _query($statement, $params = array()) {
    $stmt = $this->database->prepare($statement);
    $stmt->execute((array)$params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Find data
   *
   * @param array $query
   * @param array $params
   * @return array result rows
   */
  private function _find($query = array(), $params = array()) {
    $statement = $this->_buildStatement($query);
    $stmt = $this->database->prepare($statement);
    $stmt->execute((array)$params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;
  }

  /**
   * Build statement
   *
   * @param mixed $query
   * @return string
   */
  private function _buildStatement($query) {
    $query = $this->_parseQuery($query);

    $statement = null;
    if (!is_array($query)) {
      $statement = $query;
    } else {
      $statement = $this->_buildQuery($query);
    }

    if (0 !== strncasecmp($statement, 'select', 6)) {
      $statement = 'SELECT ' . $statement;
    }
    return $statement;
  }

  /**
   * Build query
   *
   * @param array $query
   * @return string
   */
  private function _buildQuery($query) {
    static $index = array(
      'distinct' => 0,
      'fields' => 1,
      'from' => 2,
      'as' => 3,
      'joins' => 4,
      'where' => 5,
      'group' => 6,
      'having' => 7,
      'order' => 8,
      'limit' => 9,
      'offset' => 10
    );

    $results = array();
    $as = false;
    foreach ($query as $key => $val) {
      if ($val === null) {
        continue;
      }

      $i = $index[$key];
      $clause = strtoupper($key);
      switch ($key) {
        case 'distinct':
          if ($val) {
            $results[$i] = $clause;
          }
          break;
        case 'fields':
          $results[$i] = $this->_joinValues($val);
          break;
        case 'from':
          if ($val == null) {
            $val = $this->tableName;
          } else {
            $alias = $this->_parseAlias($val);
            if (!is_array($alias)) {
              $val = (string)$alias;
            } else {
              if (isset($alias['as'])) {
                $as = true;
              }
              $val = $this->_buildAlias($alias);
            }
          }
          $results[$i] = $clause . ' ' . $val;
          break;
        case 'where':
          $results[$i] = $clause . ' ' . $this->_createConditions($val);
          break;
        case 'joins':
          $joins = $this->_parseJoins($val);
          if (!is_array($joins)) {
            $results[$i] = $joins;
          } else {
            $results[$i] = $this->_buildJoins($joins);
          }
          break;
        case 'group':
        case 'order':
          $clause .= ' BY';
          // FALLTHROUGH
        default:
          $results[$i] = $clause . ' ' . $val;
          break;
      }
    }

    if ($as && array_key_exists($index['as'], $results)) {
      unset($results[$index['as']]);
    }

    ksort($results);
    return implode(' ', $results);
  }

  /**
   * Parse query
   *
   * @param mixed $query
   * @return mixed
   */
  private function _parseQuery($query) {
    if (!is_array($query)) {
      return (string)$query;
    }

    $results = array();
    if (is_int(key($query))) {
      $query = array(
        'fields' => $query
      );
    }

    foreach ($query as $key => $val) {
      $clause = rtrim(strtolower($key), 's');
      switch ($clause) {
        case 'distinct':
          $results['distinct'] = $val;
          break;
        case 'field':
        case 'column':
        case 'select':
          $results['fields'] = $val;
          break;
        case 'from':
        case 'table':
          $results['from'] = $val;
          break;
        case 'alia':
        case 'a':
          $results['as'] = $val;
          break;
        case 'join':
          $results['joins'] = $val;
          break;
        case 'where':
        case 'condition':
          $results['where'] = $val;
          break;
        case 'group':
        case 'groupby':
          $results['group'] = $val;
          break;
        case 'having':
          $results['having'] = $val;
          break;
        case 'order':
        case 'orderby':
          $results['order'] = $val;
          break;
        case 'limit':
          $results['limit'] = $val;
          break;
        case 'offset':
          $results['offset'] = $val;
          break;
        default:
          throw new PunyApp_Database_Error("Unknown key '{$key}' on query");
      }
    }

    return $results + array(
      'fields' => '*',
      'from' => $this->tableName,
      'where' => array()
    );
  }

  /**
   * Build joins
   *
   * @param array $joins
   * @return string
   */
  private function _buildJoins($joins) {
    static $index = array(
      'type' => 0,
      'table' => 1,
      'as' => 2,
      'on' => 3
    );

    $results = array();
    foreach (array_values($joins) as $join) {
      if (!is_array($join)) {
        $results[] = (string)$join;
        continue;
      }

      $as = false;
      $clauses = array();
      foreach ($join as $key => $val) {
        if ($val === null) {
          continue;
        }

        $i = $index[$key];
        $clause = strtoupper($key);
        switch ($key) {
          case 'type':
            $clauses[$i] = strtoupper($val) . ' JOIN';
            break;
          case 'table':
            $alias = $this->_parseAlias($val);
            if (!is_array($alias)) {
              $clauses[$i] = (string)$alias;
            } else {
              if (isset($alias['as'])) {
                $as = true;
              }
              $clauses[$i] = $this->_buildAlias($alias);
            }
            break;
          case 'as':
            $clauses[$i] = $clause . ' ' . $val;
            break;
          case 'on':
            $clauses[$i] = $clause . ' ' . $this->_createConditions($val);
            break;
        }
      }

      if ($as && array_key_exists($index['as'], $clauses)) {
        unset($clauses[$index['as']]);
      }

      ksort($clauses);
      $results[] = implode(' ', $clauses);
    }

    return implode(' ', $results);
  }

  /**
   * Parse joins
   *
   * @param mixed $joins
   * @return mixed
   */
  private function _parseJoins($joins) {
    if (!is_array($joins)) {
      return (string)$joins;
    }

    if (!isset($joins[0])) {
      $joins = array($joins);
    }

    $results = array();
    foreach ($joins as $join) {
      $clauses = array();
      foreach ($join as $key => $val) {
        $clause = rtrim(strtolower($key), 's');
        switch ($clause) {
          case 'alia':
          case 'a':
            $clauses['as'] = $val;
            break;
          case 'table':
            $clauses['table'] = $val;
            break;
          case 'type':
            $clauses['type'] = $val;
            break;
          case 'on':
          case 'where':
          case 'condition':
            $clauses['on'] = $val;
            break;
          default:
            throw new PunyApp_Database_Error("Unknown key '{$key}' on JOIN");
        }
      }
      $results[] = $clauses;
    }

    return $results;
  }

  /**
   * Build alias
   *
   * @param array $alias
   * @return string
   */
  private function _buildAlias($alias) {
    static $index = array(
      'table' => 0,
      'as' => 1
    );

    $results = array();
    foreach ($alias as $key => $val) {
      if ($val === null) {
        continue;
      }

      $i = $index[$key];
      $clause = strtoupper($key);
      switch ($key) {
        case 'table':
          $results[$i] = $val;
          break;
        case 'as':
          $results[$i] = $clause . ' ' . $val;
          break;
      }
    }

    ksort($results);
    return implode(' ', $results);
  }

  /**
   * Parse alias
   *
   * @param mixed $alias
   * @return mixed
   */
  private function _parseAlias($alias) {
    if (!is_array($alias)) {
      return (string)$alias;
    }

    $results = array();
    if (count($alias) === 1 &&
        is_string(reset($alias)) && is_string(key($alias))) {
      $alias = array(
        'alias' => reset($alias),
        'table' => key($alias)
      );
    }

    foreach ($alias as $key => $val) {
      $clause = rtrim(strtolower($key), 's');
      switch ($clause) {
        case 'alia':
        case 'a':
          $results['as'] = $val;
          break;
        case 'table':
        case 'name':
        case 'tablename':
          $results['table'] = $val;
          break;
        default:
          throw new PunyApp_Database_Error("Unknown key '{$key}' on alias");
      }
    }

    return $results;
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
