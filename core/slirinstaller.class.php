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
 
/**
 * SLIR installer class
 * 
 * @since 2.0
 * @author Joe Lencioni <joe@shiftingpixel.com>
 * @version $Revision$
 * @package SLIR
 */
class SLIRInstaller
{
	const PAGE_TEMPLATE	= 'page.html';

	const DEFAULT_PAGE_TITLE	= 'Install SLIR (Smart Lencioni Image Resizer)';
	const DEFAULT_CONTENT_TITLE	= '<h1>Install <abbr title="Smart Lencioni Image Resizer">SLIR</abbr></h1>';

	/**
	 * @since 2.0
	 * @return void
	 */
	public function __construct()
	{
		$vars	= array(
			'pageTitle'		=> self::DEFAULT_PAGE_TITLE,
			'contentTitle'	=> self::DEFAULT_CONTENT_TITLE,
			'body'			=> '<p>Installing SLIR&hellip;</p><p>Test</p>',
		);
		
		echo $this->renderTemplate(self::PAGE_TEMPLATE, $vars);
	}

	/**
	 * @param string $filename
	 * @param array $variables
	 * @return string
	 */
	private function renderTemplate($filename, array $variables)
	{
		return vsprintf(file_get_contents("templates/$filename"), $variables);
	}
}