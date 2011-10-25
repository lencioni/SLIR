<?php
require_once realpath(__DIR__ . '/../core/slir.class.php');

abstract class SLIRTestCase extends PHPUnit_Framework_TestCase
{
  /**
   * @var SLIR
   */
  protected $slir;

  /**
   * @return void
   */
  protected function setUp()
  {
    $this->slir = new SLIR();
    $this->slir->getConfig();
    SLIRConfig::$defaultImagePath = null;
    SLIRConfig::$forceQueryString = false;
    SLIRConfig::$enableErrorImages = false;

    // Try to fix documentRoot for CLI
    SLIRConfig::$documentRoot = realpath(__DIR__ . '/../../');
  }

  /**
   * @return void
   */
  protected function tearDown()
  {
    unset($this->slir);
  }
}
