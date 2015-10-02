<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Validation
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014-2015 polygon planet
 */

/**
 * @name PunyApp_Validator
 */
class PunyApp_Validator {

  /**
   * @var PunyApp
   */
  public $app = null;

  /**
   * Constructor
   *
   * @param PunyApp
   */
  public function __construct(PunyApp $app) {
    $this->app = $app;
  }

  /**
   * Validate
   *
   * @param  string $name
   * @param  mixed $value
   * @param  array $args
   * @return bool
   */
  public function validate($name, $value, $args = array()) {
    $method = $this->getValidatableName($name);
    if (!$method) {
      throw new PunyApp_Error("Validator '{$name}' is not defined");
    }

    $args = (array)$args;
    array_unshift($args, $value);

    if (call_user_func_array($method, $args)) {
      return true;
    }

    return false;
  }


  /**
   * Get validatable callback name
   *
   * @param mixed $func
   * @return array or false
   */
  public function getValidatableName($func) {
    $name = $func;
    if (is_array($func) && isset($func[1])) {
      $name = $func[1];
    }

    $contexts = array($this, $this->app->controller);
    $prefixes = array('_validate_', '_validate', 'validate', '');

    foreach ($prefixes as $prefix) {
      foreach ($contexts as $context) {
        $method = $prefix . $name;
        if (is_callable(array($context, $method))) {
          return array($context, $method);
        }

        $method = $prefix . PunyApp_Util::underscore($name);
        if (is_callable(array($context, $method))) {
          return array($context, $method);
        }
      }
    }

    return false;
  }


  /**
   * Parse validation rules
   *
   * @param array $fields
   * @return array
   */
  public function parseValidateRules($fields = array()) {
    $results = array();

    foreach ((array)$fields as $name => $rules) {
      if (is_int($name)) {
        $name = $rules;
        $rules = array('required' => true);
      } else {
        if (!is_array($rules)) {
          $rules = (array)$rules;
        }

        if (key($rules) === 0) {
          $rules = array('rule' => (array)$rules);
        }
      }

      if (!is_array(reset($rules)) && !array_key_exists('rule', $rules)) {
        $rules = array('rule' => $rules);
      }

      $required = false;
      if (!empty($rules['rule'])) {
        $rule = $rules['rule'];

        if (!is_array($rule)) {
          $rule = (array)$rule;
        } else if (is_string(key($rule))) {
          $tmp_rule = array(key($rule));
          foreach (array_values($rule) as $val) {
            $tmp_rule[] = $val;
          }
          $rule = $tmp_rule;
        }

        if (isset($rule[0]) && !is_array($rule[0])) {
          $rule = array($rule);
        }

        foreach ($rule as $i => $values) {
          if (isset($values[0]) && is_string($values[0]) &&
              0 === strcasecmp($values[0], 'required')) {
            if (!isset($values[1]) || $values[1]) {
              $required = true;
            }
            unset($rule[$i]);
          }
        }
        $rule = array_values($rule);

        if (!empty($rule)) {
          $results[$name]['rule'] = $rule;
        }
      }

      if (isset($rules['required'])) {
        $required = (bool)$rules['required'];
      }

      $results[$name]['required'] = $required;

      if (isset($rules['message'])) {
        $results[$name]['message'] = $rules['message'];
      } else if ($required) {
        $results[$name]['message'] = "Missing required field '{$name}'";
      } else {
        $results[$name]['message'] = "Invalid value for field '{$name}'";
      }
    }

    return $results;
  }


  /**
   * Validate required
   *
   * @param  mixed $value
   * @return bool
   */
  protected function _validate_required($value) {
    return $value !== null;
  }

  /**
   * Validate by callback
   *
   * @param  mixed $value
   * @return bool
   */
  protected function _validate_callback($value, $callback) {
    $args = array_slice(func_get_args(), 2);
    array_unshift($args, $value);

    if ($callback != null && is_callable($callback) &&
        call_user_func_array($callback, $args)) {
      return true;
    }

    return false;
  }

  /**
   * Validate by Regular Expression
   *
   * @param  mixed $value
   * @return bool
   */
  protected function _validate_regex($value, $regex) {
    return (bool)preg_match($regex, $value);
  }

  /**
   * Validate email address
   *
   * @param  mixed $value
   * @return bool
   */
  protected function _validate_email($value) {
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
  }

  /**
   * Validate URL
   *
   * @param  mixed $value
   * @return bool
   */
  protected function _validate_url($value) {
    return filter_var($value, FILTER_VALIDATE_URL) !== false;
  }

  /**
   * Validate IP address
   *
   * @param  mixed $value
   * @return bool
   */
  protected function _validate_ip($value) {
    return filter_var($value, FILTER_VALIDATE_IP) !== false;
  }

  /**
   * Validate numeric
   *
   * @param  mixed   $value
   * @return bool
   */
  protected function _validate_numeric($value) {
    return is_numeric($value);
  }

  /**
   * Validate integer
   *
   * @param  mixed $value
   * @return bool
   */
  protected function _validate_integer($value) {
    return filter_var($value, FILTER_VALIDATE_INT) !== false;
  }

  /**
   * Validate length
   *
   * @param mixed $value
   * @param int $length
   * @return bool
   */
  protected function _validate_length($value, $length) {
    if (!is_numeric($length)) {
      return false;
    }
    return $this->app->length($value) === (int)$length;
  }

  /**
   * Validate byte length
   *
   * @param mixed $value
   * @param int $bytes
   * @return bool
   */
  protected function _validate_bytes($value, $bytes) {
    if (!is_numeric($bytes)) {
      return false;
    }
    return strlen($value) === (int)$bytes;
  }

  /**
   * Validate a value is between a set of values
   *
   * @param mixed $value
   * @param int $min
   * @param int $max
   * @return bool
   */
  protected function _validate_between($value, $min, $max) {
    if (!is_numeric($value) || !is_numeric($min) || !is_numeric($max)) {
      return false;
    }
    $value = (int)$value;
    return $value >= (int)$min && $value <= (int)$max;
  }

  /**
   * Validate a value is greater than a minimum value
   *
   * @param mixed $value
   * @param int $min
   * @return bool
   */
  protected function _validate_min($value, $min) {
    if (!is_numeric($value) || !is_numeric($min)) {
      return false;
    }
    return (int)$value >= (int)$min;
  }

  /**
   * Validate a value is less than a maximum value
   *
   * @param mixed $value
   * @param int $max
   * @return bool
   */
  protected function _validate_max($value, $max) {
    if (!is_numeric($value) || !is_numeric($max)) {
      return false;
    }
    return (int)$value <= (int)$max;
  }

  /**
   * Validate the length of value is between a set of values
   *
   * @param mixed $value
   * @param int $min
   * @param int $max
   * @return bool
   */
  protected function _validate_length_between($value, $min, $max) {
    return $this->_validate_between($this->app->length($value), $min, $max);
  }

  /**
   * Validate the byte length of value is between a set of values
   *
   * @param mixed $value
   * @param int $min
   * @param int $max
   * @return bool
   */
  protected function _validate_bytes_between($value, $min, $max) {
    return $this->_validate_between(strlen($value), $min, $max);
  }

  /**
   * Validate the length of value is greater than a minimum value
   *
   * @param mixed $value
   * @param int $min
   * @return bool
   */
  protected function _validate_min_length($value, $min) {
    return $this->_validate_min($this->app->length($value), $min);
  }

  /**
   * Validate the byte length of value is greater than a minimum value
   *
   * @param mixed $value
   * @param int $min
   * @return bool
   */
  protected function _validate_min_bytes($value, $min) {
    return $this->_validate_min(strlen($value), $min);
  }

  /**
   * Validate the length of value is less than a maximum value
   *
   * @param mixed $value
   * @param int $max
   * @return bool
   */
  protected function _validate_max_length($value, $max) {
    return $this->_validate_max($this->app->length($value), $max);
  }

  /**
   * Validate the byte length of value is less than a maximum value
   *
   * @param mixed $value
   * @param int $max
   * @return bool
   */
  protected function _validate_max_bytes($value, $max) {
    return $this->_validate_max(strlen($value), $max);
  }

  /**
   * Validate a value is contained within a list of values
   *
   * @param mixed $value
   * @param array $parameters
   * @return bool
   */
  protected function _validate_in($value, $parameters) {
    if (!is_array($parameters)) {
      return false;
    }
    return in_array($value, $parameters);
  }

  /**
   * Validate a value is not contained within a list of values
   *
   * @param mixed $value
   * @param array $parameters
   * @return bool
   */
  protected function _validate_not_in($value, $parameters) {
    if (!is_array($parameters)) {
      return false;
    }
    return !in_array($value, $parameters);
  }

  /**
   * Validate the MIME type of a file is an image MIME type
   *
   * @param mixed $value
   * @return bool
   */
  protected function _validate_image($value) {
    if (strpos($value, '.') === false) {
      return false;
    }

    $parts = explode('.', $value);
    $ext = strtolower(array_pop($parts));
    return in_array($ext, array('png', 'jpg', 'jpeg', 'gif', 'bmp', 'ico'));
  }

  /**
   * Validate that a value contains only alphabetic characters
   *
   * @param mixed $value
   * @return bool
   */
  protected function _validate_alpha($value) {
    return (bool)preg_match('/^[a-zA-Z]+$/', $value);
  }

  /**
   * Validate that a value contains only alpha-numeric characters
   *
   * @param mixed $value
   * @return bool
   */
  protected function _validate_alphanumeric($value) {
    return (bool)preg_match('/^[a-zA-Z0-9]+$/', $value);
  }

  /**
   * Validate that a value contains only words
   *
   * @param mixed $value
   * @return bool
   */
  protected function _validate_words($value) {
    return (bool)preg_match('/^\w+$/', $value);
  }
}
