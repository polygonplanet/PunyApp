<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 */

class IndexController extends PunyApp_Controller {

  public $models = array();


  public function beforeFilter() {
    $this->sendContentType('text/html');
  }


  public function afterFilter() {
  }


  public function index() {
    $this->view->set('title', 'PunyApp');
    $this->view->set('description', 'The puny developer framework for rapid compiling.');
    $this->view->render();
  }
}
