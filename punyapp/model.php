<?php
/**
 * PunyApp: https://github.com/polygonplanet/PunyApp
 * @license MIT
 */

/**
 * @name PunyApp_Model
 */
class PunyApp_Model {

  /**
   * @var PunyApp_Database
   */
  private $_database = null;

  /**
   * @var string tablename
   */
  private $_tableName = null;

  /**
   * @var array fields
   */
  private $_fields = array();

  /**
   * Constructor
   *
   * @param PunyApp_Database $database
   * @param string $table_name
   */
  public function __construct(PunyApp_Database $database, $table_name) {
    $this->_fields = array();
    $this->_database = $database;
    $this->_tableName = $table_name;
  }

  /**
   * Find data
   *
   * @example
   * <code>
   * $all_rows = $this->find();
   * </code>
   *
   * @example
   * <code>
   * $rows = $this->find(
   *   array(
   *     'fields' => array('id', 'name'),
   *     'where' => array('id' => '?')
   *   ),
   *   array($id)
   * );
   * </code>
   *
   * @example
   * <code>
   * $rows = $this->find(
   *   array(
   *     'distinct' => false,
   *     'fields' => array(
   *       'U.id AS id', 'U.name AS name',
   *       'U.category AS cat', 'P.url AS url'
   *     ),
   *     'as' => 'U',
   *     'joins' => array(
   *       'type' => 'LEFT',
   *       'table' => 'profile',
   *       'as' => 'P',
   *       'on' => array('U.id' => 'P.id')
   *     ),
   *     'where' => array(
   *       'name' => ':name'
   *     ),
   *     'group' => 'cat',
   *     'order' => 'id DESC',
   *     'limit' => 10,
   *     'offset' => 5
   *   ),
   *   array(
   *     ':name' => $name
   *   )
   * );
   * </code>
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
    $stmt = $this->_database->prepare($statement);
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
   * @return bool
   */
  public function insert($fields = array(), $params = array()) {
    return $this->_add('INSERT', $fields, $params);
  }

  /**
   * Replace data
   *
   * @param array $fields
   * @param array $params
   * @return bool
   */
  public function replace($fields = array(), $params = array()) {
    return $this->_add('REPLACE', $fields, $params);
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
    $params = (array)$params;

    // Add modified time
    $this->_setSpecialFields(array('modified'), $fields, $params);

    $statement = sprintf('UPDATE %s SET %s WHERE %s',
      $this->_tableName,
      $this->_joinKeyValues($fields),
      $this->_createConditions($conditions)
    );

    $stmt = $this->_database->prepare($statement);
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
    $statement = sprintf('DELETE FROM %s WHERE %s',
      $this->_tableName,
      $this->_createConditions($conditions)
    );

    $stmt = $this->_database->prepare($statement);
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
    $stmt = $this->_database->prepare($statement);
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
    $stmt = $this->_database->prepare($statement);
    $stmt->execute((array)$params);
    return $stmt->rowCount();
  }

  /**
   * Return instance of PunyApp_Database
   *
   * @return PunyApp_Database
   */
  public function getDatabase() {
    return $this->_database;
  }

  /**
   * Set database
   *
   * @param PunyApp_Database
   */
  public function setDatabase(PunyApp_Database $database) {
    $this->_database = $database;
  }

  /**
   * Return the table name
   *
   * @return string
   */
  public function getTableName() {
    return $this->_tableName;
  }

  /**
   * Set the table name
   *
   * @param string
   */
  public function setTableName($table_name) {
    $this->_tableName = $table_name;
  }

  /**
   * Create new instance
   *
   * @return PunyApp_Model
   */
  public function newInstance() {
    $class = __CLASS__;
    $instance = new $class($this->_database, $this->_tableName);
    return $instance;
  }

  /**
   * Save fields
   *
   * @return bool
   */
  public function save() {
    if (empty($this->_fields)) {
      return false;
    }

    $fields = array();
    $params = array();
    foreach ($this->_fields as $key => $val) {
      $fields[$key] = ':' . $key;
      $params[':' . $key] = $val;
    }

    return $this->insert($fields, $params);
  }

  /**
   * Escape characters that work as wildcard string in a LIKE pattern.
   * The wildcard characters "%" and "_" should be escaped.
   *
   * @param string
   * @return string
   */
  public function escapeLike($pattern) {
    static $translates = array(
      '%' => '\%',
      '_' => '\_'
    );

    $bs = '\\';
    $pattern = str_replace($bs, $bs . $bs, $pattern);
    $pattern = strtr($pattern, $translates);

    return $pattern;
  }


  /**
   * Magic methods
   */
  public function __get($name) {
    return isset($this->_fields[$name]) ? $this->_fields[$name] : null;
  }


  public function __set($name, $value) {
    $this->_fields[$name] = $value;
  }


  public function __isset($name) {
    return isset($this->_fields[$name]);
  }


  public function __unset($name) {
    unset($this->_fields[$name]);
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
    $stmt = $this->_database->prepare($statement);
    $stmt->execute((array)$params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;
  }

  /**
   * Add data
   *
   * @param array $fields
   * @param array $params
   * @return bool
   */
  private function _add($command, $fields = array(), $params = array()) {
    $params = (array)$params;

    // Add created time and modified time
    $this->_setSpecialFields(array('created', 'modified'), $fields, $params);

    $statement = sprintf('%s INTO %s (%s) VALUES (%s)',
      strtoupper($command),
      $this->_tableName,
      $this->_joinValues(array_keys($fields)),
      $this->_joinValues(array_values($fields))
    );

    $stmt = $this->_database->prepare($statement);
    return $stmt->execute($params);
  }

  /**
   * Add values to special fields
   *
   * @param array $names
   * @param array $fields
   * @param array $params
   * @return bool
   */
  private function _setSpecialFields($names = array(), &$fields, &$params) {
    $special_fields = $this->_hasSpecialFields();
    if (!is_array($special_fields)) {
      return false;
    }

    $prepare_names = false;
    foreach ($params as $key => $val) {
      if (is_string($key)) {
        $prepare_names = true;
        break;
      }
    }

    $result = false;
    foreach ($names as $name) {
      if (isset($special_fields[$name]) && $special_fields[$name] !== false &&
          !array_key_exists($name, $fields) &&
          (!$prepare_names || !array_key_exists(':' . $name, $params))) {

        $now = $this->_getSpecialFieldValue($special_fields[$name]);
        if ($now === false) {
          continue;
        }

        if ($prepare_names) {
          $fields[$name] = ':' . $name;
          $params[':' . $name] = $now;
        } else {
          $fields[$name] = '?';
          if (empty($params)) {
            $params[] = $now;
          } else {
            array_splice($params, count($fields) - 1, 0, $now);
          }
        }
        $result = true;
      }
    }

    return $result;
  }

  /**
   * Checks whether the special fields is available
   *
   * @param string $field_name
   * @return mixed
   */
  private function _hasSpecialFields($field_name = null) {
    $cache_key = sprintf('%s-%s', __METHOD__, $this->_tableName);
    $cache = PunyApp::store('get', $cache_key);
    if (!empty($cache)) {
      if ($field_name === null) {
        return $cache;
      }
      return isset($cache[$field_name]) ? $cache[$field_name] : null;
    }

    $results = array(
      'created' => false,
      'modified' => false
    );

    $fields = $this->_database->driver->describe($this->_tableName);
    foreach ($results as $key => $val) {
      if (isset($fields[$key])) {
        $results[$key] = $fields[$key];
      }
    }

    PunyApp::store('set', $cache_key, $results);
    if ($field_name === null) {
      return $results;
    }
    return isset($results[$field_name]) ? $results[$field_name] : null;
  }

  /**
   * Get the special fields value
   *
   * @param array $field
   * @return mixed
   */
  private function _getSpecialFieldValue($field) {
    if (!is_array($field) || !isset($field['name'])) {
      return false;
    }

    $name = strtolower($field['name']);
    $length = $field['length'];
    $now = PunyApp::now();
    $now_length = strlen((string)$now);
    $time = time();
    $time_length = strlen((string)$time);

    switch ($name) {
      case 'date':
        return date('Y-m-d');
      case 'datetime':
      case 'timestamp':
        return date('Y-m-d H:i:s');
      case 'time':
        return date('H:i:s');
      case 'char':
      case 'character':
      case 'varchar':
      case 'character varying':
      case 'text':
        if ($length === null || $length >= $now_length) {
          return (string)$now;
        } else if ($length >= $time_length) {
          return (string)$time;
        }
        return '';
      default:
        if ($length >= $now_length) {
          return $now;
        } else if ($length === null || $length >= $time_length) {
          return $time;
        }
    }

    return null;
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
            $val = $this->_tableName;
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
      'from' => $this->_tableName,
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

    $key = key($conditions);
    if (!$this->_isOperator($key)) {
      $conditions = array('AND' => $conditions);
    }

    $result = $this->_joinByOperator($conditions, $key);
    if (substr($result, 0, 1) === '(' && substr($result, -1) === ')') {
      $result = substr($result, 1, -1);
    }
    return $result;
  }

  /**
   *
   * @param array $items
   * @param string $operator
   */
  private function _joinByOperator($items, $operator) {
    $exprs = array();

    foreach ($items as $key => $val) {
      if (is_array($val)) {
        if ($this->_isOperator($key)) {
          $val = '(' . $this->_joinByOperator($val, $key) . ')';
        } else {
          $val = $key . $this->_createOperators($val);
        }
      } else {
        $val = $key . ' = ' . $val;
      }
      $exprs[] = $val;
    }

    return implode(' ' . strtoupper($operator) . ' ', $exprs);
  }

  /**
   * Check whether argument is operator
   *
   * @param string $operator
   * @return bool
   */
  private function _isOperator($operator) {
    switch (strtolower(trim($operator))) {
      case 'and':
      case 'or':
      case 'xor':
      case 'not':
        return true;
      default:
        return false;
    }
  }

  /**
   * Create operators
   *
   * @param array $operators
   * @return string
   */
  private function _createOperators($operators = array()) {
    if (is_int(key($operators))) {
      $operators = array(reset($operators) => next($operators));
    }
    $value = reset($operators);
    $operator = key($operators);

    switch (strtolower(ltrim(trim($operator), '$'))) {
      case '=':
      case '==':
      case '===':
      case 'eq':
        return ' = ' . $value;
      case '>':
      case 'gt':
        return ' > ' . $value;
      case '>=':
      case 'gte':
        return ' >= ' . $value;
      case '<':
      case 'lt':
        return ' < ' . $value;
      case '<=':
      case 'lte':
        return ' <= ' . $value;
      case '<>':
      case '!=':
      case '!==':
      case 'ne':
        return ' <> ' . $value;
      case 'in':
        return ' IN (' . $this->_joinValues($value) . ')';
      case 'not in':
      case 'notin':
      case 'nin':
        return ' NOT IN (' . $this->_joinValues($value) . ')';
      case 'or':
        return ' OR ' . $value;
      case 'and':
        return ' AND ' . $value;
      case 'not':
        return ' NOT ' . $value;
      case 'like':
        return ' LIKE ' . $value;
      case 'not like':
      case 'notlike':
      case 'nlike':
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
