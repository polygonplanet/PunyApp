<?php

class PunyAppTest extends PHPUnit_Framework_TestCase {

  public function setUp() {
    require_once dirname(dirname(__FILE__)) . '/punyapp/bootstrap.php';
    $this->assertTrue(true);
  }

  /**
   * "already sent. You cannot change the session module's ini settings at this time"エラーがでるため
   * runInSeparateProcessをつけてテストする
   * https://qiita.com/1000k/items/65f64dd95c020639edbf
   * @runInSeparateProcess
   */
  public function testPunyApp() {
    ob_start();
    PunyApp_Dispatcher::dispatch();
    $contents = ob_get_contents();
    ob_end_clean();

    $this->assertTrue(!preg_match('/Error|Notice|Warning|Fatal/i', $contents));
    $this->assertContains('<html', $contents);
    $this->assertContains('<h1>PunyApp</h1>', $contents);
    $this->assertContains('framework', $contents);
  }
}
