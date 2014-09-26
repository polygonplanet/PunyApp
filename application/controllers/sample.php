<?php
// PunyApp sample login from

class SampleController extends PunyApp_Controller {

  /**
   * @var array
   */
  public $models = array('sample');

  /**
   * @var SampleModel
   */
  public $sample;

  /**
   * @var array
   */
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
        // Custom validator
        array('MyPassword')
      ),
      'message' => 'Min 4 characters, max 20 characters, enables [a-zA-Z0-9_.!?@=+-].'
    )
  );

  /**
   * Custom validator
   *
   * @param mixed $value
   * @return bool
   */
  public function validateMyPassword($value) {
    return (bool)preg_match('/^[a-zA-Z0-9_.!?@=+-]{4,20}$/', $value);
  }


  /**
   * Before filter
   *
   * @param array $params request parameters
   */
  public function beforeFilter($params) {
    $this->view->title = 'PunyApp - Sample';
  }

  /**
   * After filter
   *
   * @param array $params request parameters
   */
  public function afterFilter($params) {
  }

  /**
   * Before render
   *
   * @param array $params request parameters
   */
  public function beforeRender($params) {
    $this->sendContentType('text/html');
  }


  /**
   * Any /index
   *
   * @param array $params request parameters
   */
  public function anyIndex($params) {
    $this->getHome($params);
  }


  /**
   * Before /login
   *
   * @param array $params request parameters
   */
  public function beforeLogin($params) {
    if (!empty($this->session->userid)) {
      $this->redirect('home');
    }
    $this->view->error = null;
  }

  /**
   * GET /login
   *
   * @param array $params request parameters
   */
  public function getLogin($params) {
    $this->view->error = null;
    $this->view->render('sample/login');
  }

  /**
   * POST /login
   *
   * @param array $params request parameters
   */
  public function postLogin($params) {
    if (!empty($this->session->userid)) {
      $this->redirect('home');
    }

    if (!$this->_validateToken()) {
      $this->view->error = 'Bad Request';
    } else if (!$this->validate(array('id', 'pass'))) {
      $this->view->error = $this->view->getLastValidationError();
    } else {
      $has = $this->sample->hasUser($params['id'], $params['pass']);

      if ($has) {
        $this->session->userid = $params['id'];
        $this->redirect('home');
      }
      $this->view->error = 'Missing id or pass';
    }

    $this->view->render('sample/login');
  }


  /**
   * GET /logout
   *
   * @param array $params request parameters
   */
  public function getLogout($params) {
    unset($this->session->userid);
    $this->redirect('login');
  }


  /**
   * Before /register
   *
   * @param array $params request parameters
   */
  public function beforeRegister($params) {
    $this->view->id = null;
    $this->view->email = null;
    $this->view->pass = null;
    $this->view->error = null;
  }

  /**
   * GET /register
   *
   * @param array $params request parameters
   */
  public function getRegister($params) {
    $this->view->render('sample/register');
  }

  /**
   * POST /register
   *
   * @param array $params request parameters
   */
  public function postRegister($params) {
    if (!$this->_validateToken()) {
      $this->view->error = 'Bad Request';
    } else if ($this->validate()) {
      if ($this->sample->isUserId($params['id'])) {
        $this->view->error = 'This id already exists';
      } else if ($this->_registerUser($params)) {
        $this->redirect('home');
      } else {
        return $this->view->renderError(500);
      }
    }

    $this->view->id = $params['id'];
    $this->view->email = $params['email'];
    $this->view->pass = '';
    $this->view->render('sample/register');
  }


  /**
   * GET /home
   *
   * @param array $params request parameters
   */
  public function getHome($params) {
    if (empty($this->session->userid)) {
      $this->redirect('login');
    }

    $this->view->user = $this->sample->getUser($this->session->userid);
    $this->view->render('sample/home');
  }


  /**
   * Add user
   *
   * @param array $params request parameters
   * @return bool
   */
  private function _registerUser($params) {
    $res = $this->sample->addUser($params['id'], $params['email'], $params['pass']);

    if ($res) {
      $this->session->userid = $params['id'];
      return true;
    }
    return false;
  }


  /**
   * Validate from
   *
   * @return bool
   */
  private function _validateToken() {
    if (isset($this->request->params->token) &&
        $this->token->validate($this->request->params->token)) {
      return true;
    }
    return false;
  }
}
