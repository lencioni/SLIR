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
  public function processRequesFromURLNoParameters()
  {
    $_SERVER['REQUEST_URI'] = '';
    $slir = new SLIR();
    $slir->processRequestFromURL();
  }
}
