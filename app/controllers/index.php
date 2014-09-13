<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 */

class IndexController extends PunyApp_Controller {

  public $models = array();


  public function beforeRender() {
    $this->sendContentType('text/html');
  }


  public function beforeFilter() {
  }


  public function afterFilter() {
  }


  /**
   * any /index
   */
  public function anyIndex() {
    $this->view->set('title', 'PunyApp');
    $this->view->set('description', 'The puny developer framework for rapid compiling.');
    $this->view->render('index');
  }
}
