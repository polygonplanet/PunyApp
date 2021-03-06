<?php
// PunyApp sample model

class SampleModel extends PunyApp_Model {

  public function addUser($userid, $email, $pass) {
    if ($this->isUserId($userid)) {
      return false;
    }

    $sample = $this->newInstance();
    $sample->userid = $userid;
    $sample->email = $email;
    $sample->pass = PunyApp::hash($pass);
    return $sample->save();
  }


  public function deleteUser($userid) {
    return $this->delete(
      array('userid' => '?'),
      array($userid)
    );
  }


  public function getUser($userid) {
    return $this->findOne(
      array(
        'fields' => array('id', 'userid', 'email'),
        'where' => array('userid' => '?')
      ),
      array($userid)
    );
  }


  public function isUserId($userid) {
    return $this->has(
      array(
        'where' => array('userid' => '?')
      ),
      array($userid)
    );
  }


  public function hasUser($userid, $pass) {
    return $this->has(
      array(
        'where' => array(
          'userid' => ':userid',
          'pass' => ':pass'
        )
      ),
      array(
        ':userid' => $userid,
        ':pass' => PunyApp::hash($pass)
      )
    );
  }
}
