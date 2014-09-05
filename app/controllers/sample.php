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
    $this->app->removePoweredByHeader();
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
    if (!empty($this->session->userId)) {
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
    if (!empty($this->session->userId)) {
      $this->redirect('home');
    }

    $error = null;
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
        $this->redirect('home');
      }

      $error = 'Missing id or pass';
    }

    $this->view->set('error', $error);
    $this->view->render('sample/login');
  }


  public function getLogout() {
    unset($this->session->userId);
    $this->redirect('login');
  }


  /**
   * Before /register
   */
  public function beforeRegister() {
    $this->view->set(array(
      'id' => null,
      'email' => null,
      'pass' => null
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

    if ($this->validate()) {
      $this->_registerUser();
      $this->redirect('home');
    }

    $this->view->set(array(
      'id' => $this->request->params->id,
      'email' => $this->request->params->email,
      'pass' => ''
    ));

    $this->view->render('sample/register');
  }


  /**
   * GET /home
   */
  public function getHome() {
    if (empty($this->session->userId)) {
      $this->redirect('login');
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
