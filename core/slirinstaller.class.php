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
	/**
	 * @var string
	 * @since 2.0
	 */
	const DEFAULT_PAGE_TITLE	= 'Install SLIR (Smart Lencioni Image Resizer)';

	/**
	 * @var string
	 * @since 2.0
	 */
	const DEFAULT_CONTENT_TITLE	= '<h1>Install <abbr title="Smart Lencioni Image Resizer">SLIR</abbr></h1>';

	/**
	 * @var string
	 * @since 2.0
	 */
	const SAMPLE_CONFIG_FILEPATH	= '../slirconfig-sample.class.php';

	/**
	 * @var string
	 * @since 2.0
	 */
	const DEFAULT_CONFIG_FILEPATH	= 'slirconfigdefaults.class.php';

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
		$this->slir	= new SLIR();
		$this->slir->escapeOutputBuffering();

		echo $this->renderTemplate('header.html', array(
			self::DEFAULT_PAGE_TITLE,
			self::DEFAULT_CONTENT_TITLE,
		));

		if (!defined('__DIR__'))
		{
			define('__DIR__', dirname(__FILE__));
		}

		echo '<p>Installing <abbr title="Smart Lencioni Image Resizer">SLIR</abbr>&hellip;</p>';

		$tasks	= array(
			'initializeConfigFile',
		);

		echo '<div class="responses">';
		foreach($tasks as $task)
		{
			echo $this->renderResponse($this->$task());
		}
		echo '</div>';

		echo $this->renderTemplate('footer.html', array());
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
	 * @param SLIRInstallerResponse $response
	 * @return string
	 * @since 2.0
	 */
	public function renderResponse(SLIRInstallerResponse $response)
	{
		return $this->renderTemplate('response.html', array(
			$response->getType(),
			$response->getLabel(),
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
		$r	= '';
		foreach ($responses as $response)
		{
			$r	.= $this->renderResponse($response);
		}
		return $r;
	}

	/**
	 * Initializes SLIR's configuration file if it needs to be done.
	 * 
	 * @return SLIRInstallerResponse
	 * @since 2.0
	 */
	private function initializeConfigFile()
	{
		$task	= 'Config File';
		$config	= '../' . $this->slir->configFilename();

		if (file_exists($config))
		{
			return new PositiveSLIRInstallerResponse($task, vsprintf('Config file exists. Edit <code>%s</code> to override the default settings in <code>%s</code>.', array(
				$this->resolveRelativePath($config),
				$this->resolveRelativePath(self::DEFAULT_CONFIG_FILEPATH),
			)));
		}
		
		if (file_exists(self::SAMPLE_CONFIG_FILEPATH))
		{
			if (copy(self::SAMPLE_CONFIG_FILEPATH, $config))
			{
				return new PositiveSLIRInstallerResponse($task, vsprintf('Sample config file was successfully copied to <code>%s</code>. Edit this file to override the default settings in <code>%s</code>.', array(
					$this->resolveRelativePath($config),
					$this->resolveRelativePath(self::DEFAULT_CONFIG_FILEPATH),
				)));
			}
			else
			{
				return new NegativeSLIRInstallerResponse($task,	vsprintf('Could not initialize configuration file. Please copy <code>%s</code> to <code>%s</code>.', array(
					$this->resolveRelativePath(self::SAMPLE_CONFIG_FILEPATH),
					$this->resolveRelativePath($config),
				)));
			}
		}

		return new NegativeSLIRInstallerResponse($task, vsprintf('Could not find <code>%s</code> or <code>%s</code>. Please try downloading the latest version of SLIR.', array(
			$this->resolveRelativePath($config),
			$this->resolveRelativePath(self::SAMPLE_CONFIG_FILEPATH),
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