<?php 

namespace SLIR;
/**
 * @package SLIR
 * @subpackage Installer
 * @since 2.0
 */
class SLIRInstallerResponse
{
  /**
   * @var string
   * @since 2.0
   */
  protected $type   = 'Generic';

  /**
   * @var string
   * @since 2.0
   */
  protected $message  = 'Unknown';

  /**
   * @var string
   * @since 2.0
   */
  protected $description  = '';

  /**
   * @param string $description
   * @return void
   * @since 2.0
   */
  public function __construct($description = '')
  {
    $this->description  = $description;
  }

  /**
   * @return string
   * @since 2.0
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * @return string
   * @since 2.0
   */
  public function getMessage()
  {
    return $this->message;
  }

  /**
   * @return string
   * @since 2.0
   */
  public function getDescription()
  {
    return $this->description;
  }
}