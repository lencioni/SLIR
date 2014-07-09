<?php 

namespace SLIR;

/**
 * @package SLIR
 * @subpackage Installer
 */
class FatalSLIRInstallerResponse extends NegativeSLIRInstallerResponse
{
  /**
   * @var string
   * @since 2.0
   */
  protected $type   = 'Fatal';
}