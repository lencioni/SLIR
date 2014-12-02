<?php
/**
 * Installer class for SLIR (Smart Lencioni Image Resizer)
 *
 * This file is part of SLIR (Smart Lencioni Image Resizer).
 *
 * Copyright (c) 2014 Joe Lencioni <joe.lencioni@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright Copyright © 2014, Joe Lencioni
 * @license MIT
 * @since 2.0
 * @package SLIR
 */

require_once 'slir.class.php';

/**
 * SLIR installer class
 *
 * @since 2.0
 * @author Joe Lencioni <joe@shiftingpixel.com>
 * @package SLIR
 * @subpackage Installer
 */
class SLIRInstaller
{
  /**
   * @var string
   * @since 2.0
   */
  const DEFAULT_PAGE_TITLE  = 'Install SLIR (Smart Lencioni Image Resizer)';

  /**
   * @var string
   * @since 2.0
   */
  const DEFAULT_CONTENT_TITLE = '<h1>Install <abbr title="Smart Lencioni Image Resizer">SLIR</abbr></h1>';

  /**
   * @var string
   * @since 2.0
   */
  const SAMPLE_CONFIG_FILEPATH  = '../slirconfig-sample.class.php';

  /**
   * @var string
   * @since 2.0
   */
  const DEFAULT_CONFIG_FILEPATH = 'slirconfigdefaults.class.php';

  /**
   * @since 2.0
   * @var SLIR
   */
  private $slir;

  /**
   * @since 2.0
   * @var array
   */
  private $templateCache;

  /**
   * @since 2.0
   * @return void
   */
  public function __construct()
  {
    $this->slir = new SLIR();

    $this->slir->escapeOutputBuffering();

    echo $this->renderTemplate('header.html', array(
      self::DEFAULT_PAGE_TITLE,
      self::DEFAULT_CONTENT_TITLE,
    ));

    if (!defined('__DIR__')) {
      define('__DIR__', dirname(__FILE__));
    }

    echo '<p>Installing <abbr title="Smart Lencioni Image Resizer">SLIR</abbr>&hellip;</p>';

    $tasks  = array(
      'PHP Version'     => 'checkPHPVersion',
      'Config File'     => 'initializeConfigFile',
      'Config Entropy'  => 'checkConfigEntropy',
      'Error Log'       => 'initializeErrorLog',
    );

    echo '<div class="responses">';
    foreach ($tasks as $label => $function) {
      echo "<p><strong>$label</strong>: ";
      flush();

      $response = $this->$function();
      echo $this->renderResponse($response);
      echo '</p>';
      flush();

      if ($this->responseIsFatal($response)) {
        echo $this->renderFatalResponseReceivedMessage();
        break;
      }
    }
    echo '</div>';

    echo $this->renderTemplate('footer.html', array());
  }

  /**
   * @since 2.0
   * @return string
   */
  private function renderFatalResponseReceivedMessage()
  {
    return '<p>Installation has not successfully completed. Please address the issues above and re-run installation.</p>';
  }

  /**
   * Determines if the response is positive
   *
   * @since 2.0
   * @param SLIRInstallerResponse $response
   * @return boolean
   */
  private function responseIsPositive(SLIRInstallerResponse $response)
  {
    return is_a($response, 'PositiveSLIRInstallerResponse');
  }

  /**
   * Determines if the response is negative
   *
   * @since 2.0
   * @param SLIRInstallerResponse $response
   * @return boolean
   */
  private function responseIsNegative(SLIRInstallerResponse $response)
  {
    return is_a($response, 'NegativeSLIRInstallerResponse');
  }

  /**
   * Determines if the response is fatal
   *
   * @since 2.0
   * @param SLIRInstallerResponse $response
   * @return boolean
   */
  private function responseIsFatal(SLIRInstallerResponse $response)
  {
    return is_a($response, 'FatalSLIRInstallerResponse');
  }

  /**
   * Gets the contents of the template file and stores it in a variable to help prevent excessive disk reads.
   *
   * @param string $filename
   * @return string
   * @since 2.0
   */
  private function getTemplate($filename)
  {
    if (!isset($this->templateCache[$filename])) {
      $this->templateCache[$filename] = file_get_contents("templates/$filename");
    }
    return $this->templateCache[$filename];
  }

  /**
   * @param string $filename
   * @param array $variables
   * @return string
   * @since 2.0
   */
  private function renderTemplate($filename, array $variables)
  {
    return vsprintf($this->getTemplate($filename), $variables);
  }

  /**
   * @param SLIRInstallerResponse $response
   * @return string
   * @since 2.0
   */
  public function renderResponse(SLIRInstallerResponse $response)
  {
    return $this->renderTemplate('response.html', array(
      $response->getType(),
      $response->getMessage(),
      $response->getDescription(),
    ));
  }

  /**
   * @param array $responses
   * @return string
   * @since 2.0
   */
  public function renderResponses(array $responses)
  {
    $r  = '';
    foreach ($responses as $response) {
      $r  .= $this->renderResponse($response);
    }
    return $r;
  }

  /**
   * @return string
   * @since 2.0
   */
  private function getConfigPath()
  {
    return $this->slir->getConfigPath();
  }

  /**
   * @return string
   * @since 2.0
   */
  private function getSampleConfigPath()
  {
    return $this->slir->resolveRelativePath(self::SAMPLE_CONFIG_FILEPATH);
  }

  /**
   * @return string
   * @since 2.0
   */
  private function getDefaultConfigPath()
  {
    return $this->slir->resolveRelativePath(self::DEFAULT_CONFIG_FILEPATH);
  }

  /**
   * @since 2.0
   * @param integer $number
   * @param string $singularPattern
   * @param string $pluralPattern
   * @return string
   */
  private function renderQuantity($number, $singularPattern = '%d thing', $pluralPattern = '%d things')
  {
    if ($number === 1) {
      return sprintf($singularPattern, $number);
    } else {
      return sprintf($pluralPattern, $number);
    }
  }

  /**
   * Checks the version of PHP to make sure it is new enough for SLIR to work properly.
   */
  private function checkPHPVersion()
  {
    $minimumVersion = '5.1.2';

    if (version_compare(PHP_VERSION, $minimumVersion) >= 0) {
      return new PositiveSLIRInstallerResponse('Your PHP version is new enough for SLIR to work properly.');
    } else {
      return new NegativeSLIRInstallerResponse(sprintf('You are running a version of PHP that is too old for SLIR to work properly. Please upgrade to version %s or newer.', $minimumVersion));
    }
  }

  /**
   * Initializes SLIR's configuration file if it needs to be done.
   *
   * @return SLIRInstallerResponse
   * @since 2.0
   */
  private function initializeConfigFile()
  {
    $config = $this->getConfigPath();

    if (file_exists($config)) {
      $this->slir->getConfig();
      return new PositiveSLIRInstallerResponse(vsprintf('Config file exists. Edit <code>%s</code> if you want to override any of the default settings found in <code>%s</code>.', array(
        $config,
        $this->getDefaultConfigPath(),
      )));
    }

    if (file_exists($this->getSampleConfigPath())) {
      if (copy($this->getSampleConfigPath(), $config)) {
        $this->slir->getConfig();
        return new PositiveSLIRInstallerResponse(vsprintf('Sample config file was successfully copied to <code>%s</code>. Edit this file if you want to override any of the default settings found in <code>%s</code>.', array(
          $config,
          $this->getDefaultConfigPath(),
        )));
      } else {
        return new FatalSLIRInstallerResponse(vsprintf('Could not initialize configuration file. Please copy <code>%s</code> to <code>%s</code> and then edit it if you want to override any of the default settings.', array(
          $this->getSampleConfigPath(),
          $config,
        )));
      }
    }

    return new FatalSLIRInstallerResponse(vsprintf('Could not find <code>%s</code> or <code>%s</code>. Please try downloading the latest version of SLIR and running the installer again.', array(
      $config,
      $this->getSampleConfigPath(),
    )));
  }

  /**
   * Checks the config file being used against the default configuration and determines if anything needs to be updated.
   *
   * @since 2.0
   * @return SLIRInstallerResponse
   */
  private function checkConfigEntropy()
  {
    $this->slir->getConfig();

    $reflectDefaults  = new ReflectionClass('SLIRConfigDefaults');
    $reflectConfig    = new ReflectionClass('SLIRConfig');

    $defaultProperties  = $reflectDefaults->getStaticProperties();
    $configProperties   = $reflectConfig->getStaticProperties();

    $additions      = array_diff(array_keys($configProperties), array_keys($defaultProperties));

    if (count($additions) === 0) {
      return new PositiveSLIRInstallerResponse('There are no settings in your config file that are not also found in the default config file.');
    } else {
      return new NegativeSLIRInstallerResponse(vsprintf('There %s in your config file that was not found in the default config file. %s most likely leftover from a previous version and should be addressed. Check the following %s in <code>%s</code> against what is found in <code>%s</code>: <code>$%s</code>', array(
        $this->renderQuantity(count($additions), 'is %d setting', 'are %d settings'),
        $this->renderQuantity(count($additions), 'This setting was', 'These settings were'),
        $this->renderQuantity(count($additions), 'setting', 'settings'),
        $this->getConfigPath(),
        $this->getDefaultConfigPath(),
        implode('</code>, <code>$', $additions),
      )));
    }
  }

  /**
   * Checks to see if the SLIR error log exists and is writable.
   *
   * @since 2.0
   * @return SLIRInstallerResponse
   */
  private function initializeErrorLog()
  {
    if (!file_exists(SLIRConfig::$pathToErrorLog)) {
      // Error log does not exist, try creating it
      if (file_put_contents(SLIRConfig::$pathToErrorLog, '') === false) {
        // Error log was unable to be created
        return new NegativeSLIRInstallerResponse(sprintf('Error log does not exist and could not be created at <code>%s</code>. Please create this file and make sure the web server has permission to write to it. If you would like to change the path of this file, set $pathToErrorLog in slirconfig.class.php and run the installer again.', SLIRConfig::$pathToErrorLog));
      } else {
        // Everything worked well
        return new PositiveSLIRInstallerResponse(sprintf('Error log successfully created at <code>%s</code>. If you would like to change the path of this file, set $pathToErrorLog in slirconfig.class.php and run the installer again.', SLIRConfig::$pathToErrorLog));
      }
    } else if (!is_writable(SLIRConfig::$pathToErrorLog)) {
      // Error log exists, but is not writable
      return new NegativeSLIRInstallerResponse(sprintf('Error log exists at <code>%s</code> but is not writable. Please make sure the web server has permission to write to this file. If you would like to change the path of this file, set $pathToErrorLog in slirconfig.class.php and run the installer again.', SLIRConfig::$pathToErrorLog));
    } else {
      // Everything is good
      return new PositiveSLIRInstallerResponse(sprintf('Error log exists at <code>%s</code> and is writable by the web server. If you would like to change the path of this file, set $pathToErrorLog in slirconfig.class.php and run the installer again.', SLIRConfig::$pathToErrorLog));
    }
  }
}

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