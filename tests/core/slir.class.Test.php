<?php
require_once 'tests/slirTestCase.class.php';

class SLIRTest extends SLIRTestCase
{
  /**
   * @test
   */
  public function escapeOutputBuffering()
  {
    ob_start();
    ob_start();
    ob_start();
    $inceptionLevel = ob_get_level();

    SLIR::escapeOutputBuffering();

    $this->assertLessThan($inceptionLevel, ob_get_level());
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Not enough parameters
   */
  public function processRequestFromURLNoParameters()
  {
    $_SERVER['REQUEST_URI'] = '';
    $this->slir->processRequestFromURL();
  }

  /**
   * @test
   * @outputBuffering enabled
   */
  public function processRequestFromURLOnlyWidth()
  {
    $_SERVER['REQUEST_URI'] = '/slir/w50/slir/tests/images/camera-logo.png';
    $this->slir->processRequestFromURL();
  }
}
