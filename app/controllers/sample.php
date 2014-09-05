<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 */

class SampleController extends PunyApp_Controller {

  public $models = array('sample');

  public $validationRules = array(
    'id' => array(
      'required' => true,
      'rule' => array('regex', '/^[a-z0-9]{1,10}$/i'),
      'message' => 'Only letters and integers, max 10 characters'
    ),
    'email' => array(
      'required' => true,
      'rule' => array('email'),
      'message' => 'Invalid email address'
    ),
    'pass' => array(
      'required' => true,
      'rule' => array(
        array('minLength', 4),
        array('maxLength', 20),
        array('regex', '/^[a-zA-Z0-9_.!?@=+-]{4,20}$/')
      ),
      'message' => 'Min 4 characters, max 20 characters, enables [a-zA-Z0-9_.!?@=+-].'
    )
  );


  public function beforeFilter() {
    $this->sendContentType('text/html');
    $this->view->set('title', 'PunyApp - Sample');
  }


  public function afterFilter() {
  }


  public function index() {
    $this->home();
  }


  public function login() {
    if (!empty($this->session->userId)) {
      return $this->home();
    }

    $error = null;

    if ($this->request->method === 'POST') {
      $this->_validateToken();

      if (!$this->validate(array('id', 'pass'))) {
        $error = $this->view->getLastValidationError();
      } else {
        $is_user = $this->models->sample->isUser(
          $this->request->params->id,
          $this->request->params->pass
        );

        if ($is_user) {
          $this->session->userId = $this->request->params->id;
          return $this->home();
        }

        $error = 'Missing id or pass';
      }
    }

    $this->view->set('error', $error);
    $this->view->render('sample/login');
  }


  public function logout() {
    unset($this->session->userId);
    $this->login();
  }


  public function register() {
    if ($this->request->method === 'POST') {
      $this->_validateToken();

      if ($this->validate()) {
        $this->_registerUser();
        return $this->home();
      }
    }

    $this->view->set(array(
      'id' => $this->request->params->id,
      'email' => $this->request->params->email,
      'pass' => ''
    ));

    $this->view->render('sample/register');
  }


  public function home() {
    if (empty($this->session->userId)) {
      return $this->login();
    }

    $user = $this->models->sample->getUser($this->session->userId);
    $this->view->set('user', $user);
    $this->view->render('sample/home');
  }


  private function _registerUser() {
    $res = $this->models->sample->addUser(
      $this->request->params->id,
      $this->request->params->email,
      $this->request->params->pass
    );

    if ($res) {
      $this->session->userId = $this->request->params->id;
      return true;
    }

    return false;
  }


  private function _validateToken() {
    if (!isset($this->request->params->token) ||
        !$this->token->validate($this->request->params->token)) {
      $this->view->renderError(500);
      exit;
    }
  }
}
