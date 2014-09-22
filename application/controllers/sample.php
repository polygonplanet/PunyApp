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
    $this->view->set('title', 'PunyApp - Sample');
  }


  public function beforeRender() {
    $this->sendContentType('text/html');
  }


  public function afterFilter() {
  }


  /**
   * any /index
   */
  public function anyIndex() {
    $this->getHome();
  }


  /**
   * Before /login
   */
  public function beforeLogin() {
    if (!empty($this->session->userid)) {
      $this->redirect('home');
    }

    $this->view->set('error', null);
  }

  /**
   * GET /login
   */
  public function getLogin() {
    $this->view->set('error', null);
    $this->view->render('sample/login');
  }

  /**
   * POST /login
   */
  public function postLogin() {
    if (!empty($this->session->userid)) {
      $this->redirect('home');
    }

    $error = null;
    $this->_validateToken();

    if (!$this->validate(array('id', 'pass'))) {
      $error = $this->view->getLastValidationError();
    } else {
      $has = $this->models->sample->hasUser(
        $this->request->params->id,
        $this->request->params->pass
      );

      if ($has) {
        $this->session->userid = $this->request->params->id;
        $this->redirect('home');
      }

      $error = 'Missing id or pass';
    }

    $this->view->set('error', $error);
    $this->view->render('sample/login');
  }


  public function getLogout() {
    unset($this->session->userid);
    $this->redirect('login');
  }


  /**
   * Before /register
   */
  public function beforeRegister() {
    $this->view->set(array(
      'id' => null,
      'email' => null,
      'pass' => null,
      'error' => null
    ));
  }

  /**
   * GET /register
   */
  public function getRegister() {
    $this->view->render('sample/register');
  }

  /**
   * POST /register
   */
  public function postRegister() {
    $this->_validateToken();

    $error = null;
    if ($this->validate()) {
      if ($this->models->sample->isUserId($this->request->params->id)) {
        $error = 'This id already exists';
      } else {
        if ($this->_registerUser()) {
          $this->redirect('home');
        } else {
          $this->view->renderError(500);
          exit;
        }
      }
    }

    $this->view->set(array(
      'id' => $this->request->params->id,
      'email' => $this->request->params->email,
      'pass' => '',
      'error' => $error
    ));

    $this->view->render('sample/register');
  }


  /**
   * GET /home
   */
  public function getHome() {
    if (empty($this->session->userid)) {
      $this->redirect('login');
    }

    $user = $this->models->sample->getUser($this->session->userid);
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
      $this->session->userid = $this->request->params->id;
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
