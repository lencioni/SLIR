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
 * @copyright Copyright © 2010, Joe Lencioni
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 * @since 2.0
 * @date $Date$
 * @version $Revision$
 * @package SLIR
 */
 
/* $Id$ */
c
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
 * Whether EXIF information should be copied from the source image
 * 
 * @since 2.0
 * @var boolean
 */
define('SLIR_COPY_EXIF',			FALSE);

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
 * Default crop mode setting to use if crop mode is not specified in the request.
 * Possible values are SLIR::CROP_CLASS_CENTERED and SLIR::CROP_CLASS_SMART.
 * 
 * @since 2.0
 * @var string
 */
define('SLIR_DEFAULT_CROP_CLASS',	SLIR::CROP_CLASS_CENTERED);

/**
 * Default setting for whether JPEGs should be progressive JPEGs (interlaced)
 * or not.
 * 
 * @since 2.0
 * @var boolean
 */
define('SLIR_DEFAULT_PROGRESSIVE_JPEG',	TRUE);

/**
 * Whether SLIR should log errors
 *
 * @since 2.0
 * @var boolean
 */
define('SLIR_LOG_ERRORS',		TRUE);

/**
 * Whether SLIR should generate and output images from error messages
 * 
 * @since 2.0
 * @var boolean
 */
define('SLIR_ERROR_IMAGES',		TRUE);

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
 * trailing slash). Ideally, this should be located outside of the web tree.
 * 
 * @var string
 */
define('SLIR_CACHE_DIR',		SLIR_DOCUMENT_ROOT . SLIR_DIR . SLIR_CACHE_DIR_NAME);

/**
 * Path to the error log file. Needs to be writable by the web server. Ideally,
 * this should be located outside of the web tree.
 * 
 * @since 2.0
 * @var string
 */
define('SLIR_ERROR_LOG_PATH',	SLIR_DOCUMENT_ROOT . SLIR_DIR . '/slir-error-log');

/**
 * If TRUE, forces SLIR to always use the query string for parameters instead
 * of mod_rewrite.
 *
 * @since 2.0
 * @var boolean
 */
define('SLIR_FORCE_QUERY_STRING',	FALSE);