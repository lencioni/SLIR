<?php
require_once 'core/slir.class.php';

abstract class SLIRTestCase extends PHPUnit_Framework_TestCase
{
  protected function setUp()
  {
    $slir = new SLIR();
    $slir->getConfig();
    SLIRConfig::$defaultImagePath = null;
    SLIRConfig::$forceQueryString = false;

    // Try to fix documentRoot for CLI
    SLIRConfig::$documentRoot = preg_replace('`/slir/?$`', '', SLIRConfig::$documentRoot);
  }
}
