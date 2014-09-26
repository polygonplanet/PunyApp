<?php
// PunyApp sample index

class IndexController extends PunyApp_Controller {

  /**
   * @var array models
   */
  public $models = array();

  /**
   * Before filter
   *
   * @param array $params request parameters
   */
  public function beforeFilter($params) {
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
    $this->view->title = 'PunyApp';
    $this->view->description = 'The puny developer framework for rapid compiling.';
    $this->view->render('index');
  }
}
