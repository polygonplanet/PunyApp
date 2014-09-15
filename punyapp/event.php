<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Event
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Event
 */
class PunyApp_Event {

  /**
   * @var PunyApp
   */
  public $app = null;

  /**
   * @var array
   */
  private $_handlers = null;


  public function __construct(PunyApp $app) {
    $this->app = $app;
    $this->_handlers = array();
  }

  /**
   * Register new event
   *
   * @param string $when
   * @param callback $handler
   * @param boolean $once = false
   * @return PunyApp_Event
   */
  public function on($when, $handler, $once = false) {
    if (!array_key_exists($when, $this->_handlers)) {
      $this->_handlers[$when] = array();
    }

    $this->_handlers[$when][] = array(
      'handler' => $handler,
      'once' => $once
    );

    return $this;
  }

  /**
   * Unregister event
   *
   * @param string $when
   * @param callback $handler = null
   * @return PunyApp_Event
   */
  public function off($when, $handler = null) {
    if (!array_key_exists($when, $this->_handlers)) {
      return $this;
    }

    if ($handler === null) {
      $this->_handlers[$when] = array();
    } else {
      $handlers = &$this->_handlers[$when];
      $count = count($handlers);
      for ($i = 0; $i < $count; $i++) {
        if (isset($handlers[$i], $handlers[$i]['handler'])) {
          $h = &$handlers[$i]['handler'];

          if ($h === $handler ||
              (is_array($h) && is_array($handler) &&
               $h[0] === $handler[0] &&
               $h[1] === $handler[1])) {
            array_splice($handlers, $i, 1);
          }
          unset($h);
        }
      }

      unset($handlers);
    }

    if (empty($this->_handlers[$when])) {
      unset($this->_handlers[$when]);
    }
    return $this;
  }


  /**
   * Register new event that called only once
   *
   * @param string $when
   * @param callback $handler
   * @return PunyApp_Event
   */
  public function once($when, $handler) {
    return $this->on($when, $handler, true);
  }


  /**
   * Trigger event
   *
   * @param string $when
   * @param array $args
   * @return PunyApp_Event
   */
  public function trigger($when, $args = array()) {
    if (!array_key_exists($when, $this->_handlers)) {
      return $this;
    }

    if (!is_array($args)) {
      $args = array();
    }
    array_unshift($args, $this->app);
    $handlers = &$this->_handlers[$when];

    $count = count($handlers);
    for ($i = 0; $i < $count; $i++) {
      if (isset($handlers[$i], $handlers[$i]['handler'])) {
        call_user_func_array($handlers[$i]['handler'], $args);
        if ($handlers[$i]['once']) {
          $handlers[$i]['handler'] = 1;
          $this->off($when, 1);
        }
      }
    }
    unset($handlers);

    return $this;
  }

  /**
   * @param void
   * @return PunyApp_Event
   */
  public function clear() {
    foreach ($this->_handlers as $when => $handlers) {
      $this->off($when);
    }
    return $this;
  }
}
