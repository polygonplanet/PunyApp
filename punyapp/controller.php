<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Controller
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       http://polygonpla.net/
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Controller
 */
class PunyApp_Controller {

  /**
   * @var object models
   */
  public $models = null;

  /**
   * @var PunyApp
   */
  public $app = null;

  /**
   * @var PunyApp_Request
   */
  public $request = null;

  /**
   * @var PunyApp_Env
   */
  public $env = null;

  /**
   * @var PunyApp_Database
   */
  public $database = null;

  /**
   * @var PunyApp_Session
   */
  public $session = null;

  /**
   * @var PunyApp_Cookie
   */
  public $cookie = null;

  /**
   * @var PunyApp_Event
   */
  public $event = null;

  /**
   * @var PunyApp_View
   */
  public $view = null;

  /**
   * @var PunyApp_Model
   */
  public $model = null;

  /**
   * @var PunyApp_Validator
   */
  public $validator = null;

  /**
   * @var PunyApp_Security_Token
   */
  public $token = null;

  /**
   * @var string name
   */
  public $name = null;

  /**
   * @var string method name
   */
  public $methodName = null;

  /**
   * @var array methods
   */
  public $methods = array();

  /**
   * @var string template
   */
  public $template = null;

  /**
   * @var array validation errors
   */
  public $validationErrors = array();

  /**
   * @var array validation rules
   */
  public $validationRules = array();

  /**
   * Constructor
   */
  public function __construct() {
    $this->validationErrors = array();

    if ($this->name == null) {
      $this->name = substr(get_class($this), 0, -10);
    }

    $child_methods = get_class_methods($this);
    $parent_methods = get_class_methods('PunyApp_Controller');

    $this->methods = array_values(array_diff($child_methods, $parent_methods));
  }

  /**
   * Set header
   *
   * @param string $name header name
   * @param string $value header value
   * @return bool
   */
  protected function header($name, $value = null) {
    return PunyApp::header('set', $name, $value);
  }

  /**
   * Redirect
   *
   * @param string $url
   */
  protected function redirect($url) {
    $args = func_get_args();
    $this->app->event->trigger('app-before-redirect', $args);
    $this->app->redirect($url);
  }

  /**
   * Send Content-type states header
   *
   * @param string $type content-type
   */
  protected function sendContentType($type = null) {
    $this->app->sendContentType($type);
  }

  /**
   * Validate request parameters
   *
   * @param array $rules
   * @return bool
   */
  protected function validate($rules = array()) {
    $args = func_get_args();
    $this->app->event->trigger('app-before-validate', $args);

    $valid = true;

    if (func_num_args() === 0) {
      $rules = $this->validationRules;
    }
    $rules = $this->validator->parseValidateRules($rules);

    foreach ($rules as $name => $rule) {
      $message = $rule['message'];

      if (!empty($rule['required']) && !isset($this->request->params->{$name})) {
        $this->validationErrors[$name] = $message;
        $valid = false;
      }

      if (array_key_exists('rule', $rule) && !empty($rule['rule']) &&
          isset($this->request->params->{$name})) {

        $value = $this->request->params->{$name};

        foreach ($rule['rule'] as $val) {
          if (is_array($val) && isset($val[0])) {
            $method = array_shift($val);
            $args = (array)$val;
            if (!$this->validator->validate($method, $value, $args)) {
              $this->validationErrors[$name] = $message;
              $valid = false;
            }
          }
        }
      }
    }

    return $valid;
  }


  /**
   * Called before the controller action
   */
  public function beforeFilter() {
  }

  /**
   * Called after the controller action is run and rendered
   */
  public function afterFilter() {
  }

  /**
   * Called before the render action
   */
  public function beforeRender() {
  }
}
