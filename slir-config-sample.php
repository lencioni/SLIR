<?php
/**
 * Configuration file for SLIR (Smart Lencioni Image Resizer)
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
 * @copyright Copyright © 2009, Joe Lencioni
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 * @since 2.0
 * @date $Date$
 * @version $Revision$
 * @package SLIR
 */
 
/* $Id$ */
 
/**
 * How long (in seconds) the web browser should use its cached copy of the image
 * before checking with the server for a new version
 * 
 * @since 2.0
 * @var integer
 */
define('SLIR_BROWSER_CACHE_EXPIRES_AFTER_SECONDS',	7 * 24 * 60 * 60);

/**
 * Whether we should use the faster, symlink-based request cache as a first
 * line cache
 * 
 * @since 2.0
 * @var boolean
 */
define('SLIR_USE_REQUEST_CACHE',	TRUE);

/**
 * How much memory to allocate for memory-intensive processes such as rendering
 *
 * @since 2.0
 * @var string
 */
define('SLIR_MEMORY_TO_ALLOCATE',	'100M');

/**
 * Default quality setting to use if quality is not specified in the request.
 * Ranges from 0 (worst quality, smaller file) to 100 (best quality, biggest
 * file).
 * 
 * @since 2.0
 * @var integer
 */
define('SLIR_DEFAULT_QUALITY',	80);

/**
 * Default setting for whether JPEGs should be progressive JPEGs (interlaced)
 * or not.
 * 
 * @since 2.0
 * @var boolean
 */
define('SLIR_DEFAULT_PROGRESSIVE_JPEG',	TRUE);

/**
 * Absolute path to the web root (location of files when visiting
 * http://domainname.com/) (no trailing slash)
 * 
 * @since 2.0
 * @var string
 */
define('SLIR_DOCUMENT_ROOT',	preg_replace('/\/$/', '', $_SERVER['DOCUMENT_ROOT']));

/**
 * Path to SLIR (no trailing slash)
 * 
 * @since 2.0
 * @var string
 */
define('SLIR_DIR',				dirname($_SERVER['SCRIPT_NAME']));

/**
 * Name of directory to store cached files in (no trailing slash)
 * 
 * @since 2.0
 * @var string
 */
define('SLIR_CACHE_DIR_NAME',	'/cache');

/**
 * Absolute path to cache directory. This directory must be world-readable,
 * writable by the web server, and must end with SLIR_CACHE_DIR_NAME (no
 * trailing slash)
 * 
 * @var string
 */
define('SLIR_CACHE_DIR',		SLIR_DOCUMENT_ROOT . SLIR_DIR . SLIR_CACHE_DIR_NAME);
?>