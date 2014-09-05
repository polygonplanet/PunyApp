<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   View
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       http://polygonpla.net/
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_View
 */
class PunyApp_View {

  /**
   * @var string template
   */
  public $template = null;

  /**
   * @var array template vars
   */
  private static $_vars = array();

  /**
   * @var string template
   */
  private static $_template = null;

  /**
   * @var PunyApp
   */
  public $app = null;

  /**
   * @var array validation error messages
   */
  public $validationErrors = array();

  /**
   * Constructor
   *
   * @param PunyApp
   */
  public function __construct(PunyApp $app) {
    $this->app = $app;
  }

  /**
   * Set template vars
   *
   * @param mixed $name
   * @param mixed $value
   */
  public function set($name, $value = null) {
    $vars = $name;
    if (!is_array($vars)) {
      $vars = array($name => $value);
    }

    foreach ($vars as $key => $val) {
      self::$_vars[$key] = $val;
    }
  }

  /**
   * Check the template variable name is exists
   *
   * @param string $name
   * @return bool
   */
  public function has($name) {
    return array_key_exists($name, self::$_vars);
  }

  /**
   * Delete a template variable
   *
   * @param string $name
   * @return bool
   */
  public function delete($name) {
    if ($this->has($name)) {
      unset(self::$_vars[$name]);
      return true;
    }
    return false;
  }

  /**
   * Escape HTML entities
   *
   * @param mixed $html
   * @return mixed
   */
  public function escapeHTML($html) {
    return $this->app->escapeHTML($html);
  }

  /**
   * Render template
   *
   * @param string $template
   */
  public function render($template = null) {
    static $called = false;

    if ($called) {
      return;
    }
    $called = true;

    $args = func_get_args();
    $this->_beforeRender($args);

    if ($template == null) {
      $template = $this->template;
    }

    if ($template == null) {
      throw new PunyApp_Error('Missing template');
    }

    self::$_template = $template;
    $this->app->event->trigger('app-before-render');
    $this->_render();
    $this->app->event->trigger('app-after-render');
  }

  /**
   * Render template errors
   *
   * @param int $code
   */
  public function renderError($code) {
    static $called = false;

    if ($called) {
      return;
    }
    $called = true;

    $args = func_get_args();
    $this->_beforeRender($args);

    $this->app->sendResponseCode($code);
    $this->app->sendContentType('text/html');

    self::$_template = (string)$code;
    $this->app->event->trigger('app-before-render-error');
    $this->_renderError();
    $this->app->event->trigger('app-after-render-error');
  }

  /**
   * Generate token
   *
   * @return string
   */
  public function generateToken() {
    return $this->app->token->generate();
  }

  /**
   * Get the last validation error message
   *
   * @return string
   */
  public function getLastValidationError() {
    $error = end($this->validationErrors);
    reset($this->validationErrors);
    return $this->escapeHTML($error);
  }

  /**
   * Get the validation error message
   *
   * @param string $name
   * @return string
   */
  public function getValidationError($name) {
    if (!empty($this->validationErrors) &&
        isset($this->validationErrors[$name])) {
      return $this->escapeHTML($this->validationErrors[$name]);
    }
    return null;
  }

  /**
   * Get the validation error messages
   *
   * @param string $attributes
   * @return string
   */
  public function getValidationErrors($attributes = array('style' => 'color:red')) {
    $results = array();
    $attr = $this->_buildHTMLAttributes($attributes);
    if ($attr == null) {
      $results[] = '<ul>';
    } else {
      $results[] = sprintf('<ul %s>', $attr);
    }

    foreach ((array)$this->validationErrors as $name => $message) {
      $results[] = sprintf('<li>%s</li>', $this->escapeHTML($message));
    }
    $results[] = '</ul>';

    return implode("\n", $results);
  }

  /**
   * Build HTML attributes
   *
   * @param mixed $attributes
   * @return string
   */
  private function _buildHTMLAttributes($attributes = array()) {
    $results = array();

    foreach ((array)$attributes as $key => $val) {
      switch (strtolower($key)) {
        case 'style':
          if (!is_array($val)) {
            $style = array($val);
          } else {
            $style = array();
            foreach ($val as $k => $v) {
              $style[] = sprintf('%s:%s', $k, $v);
            }
          }
          $results[] = sprintf('style="%s"',
            str_replace('"', "'", implode(';', $style))
          );
          break;
        default:
          if (is_int($key)) {
            $results[] = $val;
          } else {
            $results[] = sprintf('%s="%s"',
              $this->escapeHTML($key),
              $this->escapeHTML($val)
            );
          }
          break;
      }
    }

    return implode(' ', $results);
  }

  /**
   * Before render
   *
   * @param array $args
   */
  private function _beforeRender($args) {
    if (isset($this->app->controller) &&
        is_callable(array($this->app->controller, 'beforeRender'))) {
      call_user_func_array(array($this->app->controller, 'beforeRender'), $args);
    }

    return PunyApp::header('send');
  }

  /**
   * Render template contents
   */
  private function _render() {
    $this->_setDefaultVars();
    extract(self::$_vars);
    require_once PunyApp::getLibPath(self::$_template, 'views/contents');
  }

  /**
   * Render template errors
   */
  private function _renderError() {
    $this->_setDefaultVars();
    extract(self::$_vars);
    require_once PunyApp::getLibPath(self::$_template, 'views/errors');
  }

  /**
   * Render template errors
   */
  private function _setDefaultVars() {
    self::$_vars['charset'] = $this->app->getCharset();
    self::$_vars['base_uri'] = $this->app->getBaseURI();
  }
}
