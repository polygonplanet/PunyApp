<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Dispatch
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       http://polygonpla.net/
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Dispatcher
 */
class PunyApp_Dispatcher {

  /**
   * $var PunyApp
   */
  public static $app = null;

  /**
   * Dispatch
   */
  public static function dispatch() {
    self::$app = new PunyApp();
    self::$app->initialize();
    self::_executeAction();
  }

  /**
   * Execute action
   */
  private static function _executeAction() {
    $actions = self::_getActions();

    if (!self::$app->uses($actions->ctrlFileName, $actions->dirname)) {
      self::$app->view->renderError(404);
      exit;
    }

    $class = $actions->className;
    $controller = new $class();
    self::$app->controller = $controller;

    if (!self::_isCallable($controller, $actions->actionName)) {
      if (self::_isCallable($controller, 'any')) {
        $actions->actionName = 'any';
      } else {
        self::$app->view->renderError(404);
        exit;
      }
    }
    self::_setProps($controller, $actions->actionName);

    self::$app->event->trigger('app-before-filter');
    if (is_callable(array($controller, 'beforeFilter'))) {
      $controller->beforeFilter();
    }

    $params = self::_getParams();
    call_user_func_array(array($controller, $actions->actionName), $params);

    self::$app->event->trigger('app-after-filter');
    if (is_callable(array($controller, 'afterFilter'))) {
      $controller->afterFilter();
    }
  }

  /**
   * Get actions
   *
   * @return object
   */
  private static function _getActions() {
    $controller_name = self::$app->request->controllerName;
    $action_name = self::$app->request->actionName;

    return (object) array(
      'actionName' => $action_name,
      'ctrlFileName' => PunyApp_Util::underscore($controller_name),
      'className' => PunyApp_Util::camelize($controller_name, true) . 'Controller',
      'dirname' => 'controllers'
    );
  }

  /**
   * Get actions
   *
   * @return object
   */
  private static function _getParams() {
    $results = array();
    $i = 0;
    foreach (self::$app->request->params as $key => $val) {
      if ($key === $i) {
        $results[] = $val;
        $i++;
      }
    }
    return $results;
  }

  /**
   * Callable method
   *
   * @param PunyApp_Controller $controller
   * @param string $method
   * @return boolean
   */
  private static function _isCallable($controller, $method) {
    static $ignore_methods = array(
      'beforeFilter' => true,
      'afterFilter' => true
    );

    // Disable magic methods
    if (strpos($method, '_') === 0) {
      return false;
    }

    if (array_key_exists(strtolower($method), $ignore_methods)) {
      return false;
    }

    $func = array($controller, $method);
    if (!is_callable($func)) {
      return false;
    }

    $reflection_method = new ReflectionMethod($controller, $method);
    if (!$reflection_method->isPublic()) {
      return false;
    }

    $methods = $controller->methods;
    if (empty($methods) || !is_array($methods)) {
      return false;
    }

    foreach ($methods as $name) {
      if (0 === strcasecmp($name, $method)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Set controller properties
   *
   * @param object $controller
   * @param string $action
   */
  private static function _setProps($controller, $action) {
    $controller->name = $action;

    if (!isset($controller->template) || $controller->template == null) {
      $controller->template = self::$app->request->actionName;
    }

    if (isset($controller->models) && is_array($controller->models)) {
      $models = $controller->models;
      $controller->models = (object) array();

      foreach ($models as $model) {
        self::$app->uses($model, 'models');
        $model_name = PunyApp_Util::camelize($model);
        $model_class = $model_name . 'Model';
        $controller->models->{$model_name} = new $model_class();
        $controller->models->{$model_name}->app = self::$app;
        $controller->models->{$model_name}->database = self::$app->database;
        $controller->models->{$model_name}->tableName = $model;
      }
    }

    $controller->app = self::$app;
    foreach (array('request', 'env', 'database', 'view', 'session',
                   'cookie', 'event', 'validator', 'token') as $prop) {
      $controller->{$prop} = self::$app->{$prop};
    }

    if (isset($controller->template)) {
      $controller->view->template = $controller->template;
    }
    $controller->view->validationErrors = &$controller->validationErrors;
  }
}
