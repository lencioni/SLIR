<?php

namespace SLIR;
/**
 * @package SLIR
 * @subpackage Installer
 * @since 2.0
 */
class PositiveSLIRInstallerResponse extends SLIRInstallerResponse
{
  /**
   * @var string
   * @since 2.0
   */
  protected $type   = 'Positive';

  /**
   * @var string
   * @since 2.0
   */
  protected $message  = 'Success!';
}