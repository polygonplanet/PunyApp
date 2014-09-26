<?php
// PunyApp sample index

class IndexController extends PunyApp_Controller {

  /**
   * @var array models
   */
  public $models = array();

  /**
   * Called before the controller action
   *
   * @param array $params request parameters
   */
  public function beforeFilter($params) {
  }

  /**
   * Called after the controller action is run and rendered
   *
   * @param array $params request parameters
   */
  public function afterFilter($params) {
  }

  /**
   * Called before the render action
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
    $this->view->title = 'PunyApp';
    $this->view->description = 'The puny developer framework for rapid compiling.';
    $this->view->render('index');
  }
}
