<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Security/Token
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       http://polygonpla.net/
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Security_Token
 */

class PunyApp_Security_Token {

  /**
   * @const string
   */
  const NAME = "\0__token";

  /**
   * @const int
   */
  const MAX = 10;

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
   * Generate token
   *
   * @return string
   */
  public function generate() {
    $this->_initialize();

    $key = sha1(uniqid(mt_rand(), true));
    $this->app->session->{self::NAME} = array($key => 1) +
      array_slice($this->app->session->{self::NAME}, 0, self::MAX - 1, true);

    return $key;
  }

  /**
   * Validate token
   *
   * @param string $token
   * @return bool
   */
  public function validate($token) {
    $this->_initialize();

    $token = (string)filter_var($token);
    $tokens = $this->app->session->{self::NAME};

    if (isset($tokens[$token])) {
      unset($tokens[$token]);
      $this->app->session->{self::NAME} = $tokens;
      return true;
    }

    return false;
  }

  /**
   * Initialize token
   */
  private function _initialize() {
    if (!isset($this->app->session->{self::NAME}) ||
        !is_array($this->app->session->{self::NAME})) {
      $this->app->session->{self::NAME} = array();
    }
  }
}
