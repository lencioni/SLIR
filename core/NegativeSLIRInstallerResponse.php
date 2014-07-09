<?php 
namespace SLIR;
/**
 * @package SLIR
 * @subpackage Installer
 */
class NegativeSLIRInstallerResponse extends SLIRInstallerResponse
{
  /**
   * @var string
   * @since 2.0
   */
  protected $type   = 'Negative';

  /**
   * @var string
   * @since 2.0
   */
  protected $message  = 'Failed!';
}