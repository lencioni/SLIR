<?php
/**
 * Installer class for SLIR (Smart Lencioni Image Resizer)
 * 
 * This file is part of SLIR (Smart Lencioni Image Resizer).
 * 
 * SLIR is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * SLIR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with SLIR.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @copyright Copyright © 2011, Joe Lencioni
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
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
	const PAGE_TEMPLATE	= 'page.html';

	const DEFAULT_PAGE_TITLE	= 'Install SLIR (Smart Lencioni Image Resizer)';
	const DEFAULT_CONTENT_TITLE	= '<h1>Install <abbr title="Smart Lencioni Image Resizer">SLIR</abbr></h1>';

	/**
	 * @var SLIR
	 */
	private $slir;

	/**
	 * @var array
	 */
	private $templateCache;

	/**
	 * @since 2.0
	 * @return void
	 */
	public function __construct()
	{
		$this->slir			= new SLIR();

		$vars	= array(
			'pageTitle'		=> self::DEFAULT_PAGE_TITLE,
			'contentTitle'	=> self::DEFAULT_CONTENT_TITLE,
			'body'			=> '<p>Installing <abbr title="Smart Lencioni Image Resizer">SLIR</abbr>&hellip;</p>',
		);

		$responses		= array();

		$responses[]	= $this->initializeConfig();
		$vars['body']	.= sprintf('<div class="responses">%s</div>', $this->renderResponses($responses));
		
		echo $this->renderTemplate(self::PAGE_TEMPLATE, $vars);
	}

	/**
	 * @param string $path
	 * @return string
	 * @since 2.0
	 */
	private function resolveRelativePath($path)
	{
		$path	= __DIR__ . '/' . $path;
		
		while (strstr($path, '../'))
		{
			$path = preg_replace('/\w+\/\.\.\//', '', $path);
		}

		return $path;
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
		if (!isset($this->templateCache[$filename]))
		{
			$this->templateCache[$filename]	= file_get_contents("templates/$filename");
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
	 * @param array $responses
	 * @return string
	 * @since 2.0
	 */
	public function renderResponses(array $responses)
	{
		$r	= '';
		foreach ($responses as $response)
		{
			$r	.= $this->renderTemplate('response.html', array(
					$response->getType(),
					$response->getLabel(),
					$response->getMessage(),
					$response->getDescription(),
				));
		}
		return $r;
	}

	/**
	 * Initializes SLIR's configuration file if it needs to be done.
	 * 
	 * @return SLIRInstallerResponse
	 * @since 2.0
	 */
	private function initializeConfig()
	{
		$task			= 'Config';
		$sampleConfig	= '../slirconfig-sample.class.php';
		$config			= $this->slir->configFilename();
		$defaultConfig	= 'slirconfigdefaults.class.php';

		if (file_exists($config))
		{
			return new PositiveSLIRInstallerResponse($task, vsprintf('Config file exists. Edit <code>%s</code> to override the default settings in <code>%s</code>.', array(
				$this->resolveRelativePath($config),
				$this->resolveRelativePath($defaultConfig),
			)));
		}
		
		if (file_exists($sampleConfig))
		{
			if (copy($sampleConfig, $config))
			{
				return new PositiveSLIRInstallerResponse($task, vsprintf('Sample config file was successfully copied to <code>%s</code>. Edit this file to override the default settings in <code>%s</code>.', array(
					$this->resolveRelativePath($config),
					$this->resolveRelativePath($defaultConfig),
				)));
			}
			else
			{
				return new NegativeSLIRInstallerResponse($task,	vsprintf('Could not initialize configuration file. Please copy <code>%s</code> to <code>%s</code>.', array(
					$this->resolveRelativePath($sampleConfig),
					$this->resolveRelativePath($config),
				)));
			}
		}

		return new NegativeSLIRInstallerResponse($task, vsprintf('Could not find <code>%s</code> or <code>%s</code>. Please try downloading the latest version of SLIR.', array(
			$this->resolveRelativePath($config),
			$this->resolveRelativePath($sampleConfig),
		)));
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
	protected $type		= 'Generic';

	/**
	 * @var string
	 * @since 2.0
	 */
	protected $label	= '';

	/**
	 * @var string
	 * @since 2.0
	 */
	protected $message	= 'Unknown';

	/**
	 * @var string
	 * @since 2.0
	 */
	protected $description	= '';

	/**
	 * @param string $label
	 * @param string $description
	 * @return void
	 * @since 2.0
	 */
	public function __construct($label, $description = '')
	{
		$this->label		= $label;
		$this->description	= $description;
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
	public function getLabel()
	{
		return $this->label;
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
	protected $type		= 'Positive';

	/**
	 * @var string
	 * @since 2.0
	 */
	protected $message	= 'Success!';
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
	protected $type		= 'Negative';

	/**
	 * @var string
	 * @since 2.0
	 */
	protected $message	= 'Failed!';
}