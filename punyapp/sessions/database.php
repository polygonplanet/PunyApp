<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Session
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Session_Database
 */
class PunyApp_Session_Database {

  /**
   * @var PunyApp_Model
   */
  protected $_model;

  /**
   * @var int
   */
  protected $_timeout;

  /**
   * Constructor
   *
   * @param string $table_name
   * @param int $timeout
   */
  public function __construct(PunyApp_Database $database, $table_name, $timeout) {
    $this->_model = new PunyApp_Model($database, $table_name);
    $this->_timeout = $timeout;
  }

  /**
   * Open session
   *
   * @return bool
   */
  public function open() {
    return true;
  }

  /**
   * Close session
   *
   * @return bool
   */
  public function close() {
    return true;
  }

  /**
   * Read session
   *
   * @param mixed $id
   * @return mixed The value of the key or false
   */
  public function read($id) {
    $row = $this->_model->findOne(
      array(
        'where' => array('id' => '?')
      ),
      array($id)
    );

    if (empty($row['data'])) {
      return false;
    }

    return $row['data'];
  }

  /**
   * Write sessions
   *
   * @param mixed $id
   * @param mixed $data
   * @return bool
   */
  public function write($id, $data) {
    if (!$id) {
      return false;
    }

    $has = $this->_model->has(
      array(
        'where' => array('id' => '?')
      ),
      array($id)
    );

    if (!$has) {
      $session = $this->_model->newInstance();
      $session->id = $id;
      $session->data = $data;
      $session->expires = time() + $this->_timeout;
      return $session->save();
    }

    return (bool)$this->_model->update(
      array(
        'data' => ':data'
      ),
      array(
        'id' => ':id'
      ),
      array(
        ':data' => $data,
        ':id' => $id
      )
    );
  }

  /**
   * Destroy session
   *
   * @param mixed $id
   * @return bool
   */
  public function destroy($id) {
    return (bool)$this->_model->delete(
      array('id' => '?'),
      array($id)
    );
  }

  /**
   * gc sessions
   *
   * @param int $expires timestamp
   * @return bool
   */
  public function gc($expires = null) {
    if (!$expires) {
      $expires = time();
    } else {
      $expires = time() - $expires;
    }

    return (bool)$this->_model->delete(
      array('expires' =>
        array('$lt' => '?')
      ),
      array($expires)
    );
  }
}
