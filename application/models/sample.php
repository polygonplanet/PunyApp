<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 */

class SampleModel extends PunyApp_Model {

  public function addUser($user_id, $email, $pass) {
    if ($this->isUserId($user_id)) {
      return false;
    }

    return $this->insert(array(
      'userId' => ':userId',
      'email' => ':email',
      'pass' => ':pass',
      'updateAt' => ':updateAt'
    ), array(
      ':userId' => $user_id,
      ':email' => $email,
      ':pass' => sha1($pass),
      ':updateAt' => PunyApp::now()
    ));
  }


  public function deleteUser($user_id) {
    return $this->delete(array('userId' => '?'), array($user_id));
  }


  public function getUser($user_id) {
    return $this->findOne(
      array('id', 'userId', 'email'),
      array('userId' => '?'),
      array($user_id)
    );
  }


  public function isUserId($user_id) {
    return $this->count(array('userId' => '?'), array($user_id)) > 0;
  }


  public function isUser($user_id, $pass) {
    return $this->count(array(
      'userId' => ':userId',
      'pass' => ':pass'
    ), array(
      ':userId' => $user_id,
      ':pass' => sha1($pass)
    )) > 0;
  }
}
