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
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Dispatcher
 */
class PunyApp_Dispatcher {

  /**
   * @var PunyApp
   */
  public static $app = null;

  /**
   * Dispatch
   */
  public static function dispatch() {
    self::$app = new PunyApp();
    self::$app->initialize();
    self::_execute();
  }

  /**
   * Execute action
   */
  private static function _execute() {
    $actions = self::_getActions();

    if (empty($actions->actionName) ||
        !self::$app->uses($actions->controllerFileName, $actions->dirname)) {
      return self::$app->view->renderError(404);
    }

    $class = $actions->className;
    self::$app->controller = new $class();
    self::$app->controller->app = self::$app;

    $methods = self::_getMethods($actions->actionName);
    if ($methods->error || $methods->method == null) {
      return self::$app->view->renderError($methods->error ? 500 : 404);
    }

    self::_setProps($actions->actionName, $methods);
    self::_executeActions($methods);
  }


  /**
   * Execute actions
   *
   * @param object $methods
   */
  private static function _executeActions($methods) {
    $params = self::_getParams();

    self::$app->event->trigger('app-before-filter', array($params));
    if (is_callable(array(self::$app->controller, 'beforeFilter'))) {
      call_user_func(array(self::$app->controller, 'beforeFilter'), $params);
    }

    if ($methods->before != null) {
      call_user_func(array(self::$app->controller, $methods->before), $params);
    }

    call_user_func(array(self::$app->controller, $methods->method), $params);

    if ($methods->after != null) {
      call_user_func(array(self::$app->controller, $methods->after), $params);
    }

    self::$app->event->trigger('app-after-filter', array($params));
    if (is_callable(array(self::$app->controller, 'afterFilter'))) {
      call_user_func(array(self::$app->controller, 'afterFilter'), $params);
    }
  }

  /**
   * Get actions
   *
   * @return object
   */
  private static function _getActions() {
    $path = null;
    $action = self::$app->request->controllerName;
    $filename = null;
    $action_name = null;
    $controller_name = null;
    $dirname = 'controllers';

    foreach (array($action, 'any') as $name) {
      $path = self::$app->getLibPath($name, $dirname);
      if ($path != null) {
        $controller_name = $name;
        break;
      }
    }

    if ($controller_name != null) {
      $filename = PunyApp_Util::underscore($controller_name);
      $action_name = self::$app->request->actionName;
      self::$app->request->controllerName = $controller_name;
      self::$app->request->actionName = $action;
    }

    return (object) array(
      'actionName' => $action_name,
      'controllerName' => $controller_name,
      'controllerFileName' => $filename,
      'className' => PunyApp_Util::camelize($controller_name, true) . 'Controller',
      'dirname' => $dirname
    );
  }

  /**
   * Get method names
   *
   * @param string $action_name
   * @return object
   */
  private static function _getMethods($action_name) {
    $methods = (object) array(
      'method' => null,
      'before' => null,
      'after' => null,
      'error' => false
    );

    $method = strtolower(self::$app->request->method);
    if ($method == null) {
      $methods->error = true;
      return $methods;
    }

    $method_names = array(
      $method . ucfirst($action_name),
      $method . '_' . $action_name,
      'any' . ucfirst($action_name),
      'any_' . $action_name
    );
    foreach ($method_names as $method_name) {
      if (self::_isCallable($method_name)) {
        $methods->method = $method_name;
        break;
      }
    }

    if ($methods->method == null) {
      if (self::_isCallable($action_name)) {
        $methods->method = $action_name;
      } else if (self::_isCallable('any')) {
        $methods->method = 'any';
        $action_name = 'any';
      }
    }

    if ($methods->method == null) {
      return $methods;
    }

    foreach (array('before', 'after') as $prefix) {
      $actions = array(
        $prefix . ucfirst($action_name),
        $prefix . '_' . $action_name
      );

      foreach ($actions as $name) {
        if (is_callable(array(self::$app->controller, $name))) {
          $methods->{$prefix} = $name;
          break;
        }
      }
    }

    return $methods;
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
   * @param string $method
   * @return boolean
   */
  private static function _isCallable($method) {
    static $ignore_prefixes = array(
      'before' => true,
      'after' => true,
      'validate' => true
    );

    // Disable magic methods
    if (strpos($method, '_') === 0) {
      return false;
    }

    foreach ($ignore_prefixes as $pre) {
      $pre_len = strlen($pre);
      if (strlen($method) > $pre_len &&
          0 === strncasecmp($method, $pre, $pre_len)) {
        return false;
      }
    }

    $func = array(self::$app->controller, $method);
    if (!is_callable($func)) {
      return false;
    }

    $reflection_method = new ReflectionMethod(self::$app->controller, $method);
    if (!$reflection_method->isPublic()) {
      return false;
    }

    $methods = self::$app->controller->methods;
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
   * @param string $action
   * @param object $methods
   */
  private static function _setProps($action, $methods) {
    self::$app->controller->name = $action;
    self::$app->controller->methodName = $methods->method;

    if (!isset(self::$app->controller->template) ||
        self::$app->controller->template == null) {
      self::$app->controller->template = self::$app->request->actionName;
    }

    if (isset(self::$app->controller->models) &&
        is_array(self::$app->controller->models)) {

      $models = self::$app->controller->models;
      self::$app->controller->models = (object) array();

      foreach ($models as $model) {
        self::$app->uses($model, 'models');
        $model_name = PunyApp_Util::camelize($model);
        $model_class = $model_name . 'Model';
        self::$app->controller->models->{$model_name} = new $model_class(
          self::$app->database,
          $model
        );
      }
    }

    self::$app->controller->app = self::$app;
    foreach (array('request', 'env', 'database', 'view', 'session',
                   'cookie', 'event', 'validator', 'token') as $prop) {
      self::$app->controller->{$prop} = self::$app->{$prop};
    }

    if (isset(self::$app->controller->template)) {
      self::$app->controller->view->setTemplate(self::$app->controller->template);
    }
    self::$app->controller->view->validationErrors = &self::$app->controller->validationErrors;
  }
}
