<?php

class PunyAppTest extends PHPUnit_Framework_TestCase {

  public function setUp() {
    require dirname(dirname(__FILE__)) . '/bootstrap.php';
    $this->assertTrue(true);
  }


  public function testPunyApp() {
    ob_start();
    PunyApp_Dispatcher::dispatch();
    $contents = ob_get_contents();
    ob_end_clean();

    $this->assertTrue(strpos(strtolower($contents), 'error') === false);
    $this->assertTrue(strpos(strtolower($contents), 'notice') === false);
    $this->assertTrue(strpos(strtolower($contents), 'warn') === false);
    $this->assertTrue(strpos(strtolower($contents), 'fatal') === false);

    $this->assertContains('<html', $contents);
    $this->assertContains('<h1>PunyApp</h1>', $contents);
    $this->assertContains('framework', $contents);
  }
}
