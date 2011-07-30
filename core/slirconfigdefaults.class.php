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
 * @copyright Copyright © 2011, Joe Lencioni
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 * @since 2.0
 * @package SLIR
 */

/**
 * SLIR Config Class
 * 
 * @since 2.0
 * @author Joe Lencioni <joe@shiftingpixel.com>
 * @package SLIR
 */
class SLIRConfigDefaults
{
	/**
	 * Path to default the source image to if the requested image cannot be found.
	 * 
	 * This should match the style of path you would normally pass to SLIR in the URL (not the full path on the filesystem).
	 * 
	 * For example, if your website was http://mysite.com and your document root was /var/www/, and your default image was at http://mysite.com/images/default.png, you would set $defaultImagePath = '/images/default.png';
	 * 
	 * If NULL, SLIR will throw an exception if the requested image cannot be found.
	 * 
	 * @since 2.0
	 * @var string
	 */
	public static $defaultImagePath	= NULL;

	/**
	 * Default quality setting to use if quality is not specified in the request. Ranges from 0 (worst quality, smaller file) to 100 (best quality, largest filesize).
	 * 
	 * @since 2.0
	 * @var integer
	 */
	public static $defaultQuality	= 80;

	/**
	 * Default setting for whether JPEGs should be progressive JPEGs (interlaced) or not.
	 * 
	 * @since 2.0
	 * @var boolean
	 */
	public static $defaultProgressiveJPEG	= TRUE;

	/**
	 * How long (in seconds) the web browser should use its cached copy of the image
	 * before checking with the server for a new version
	 * 
	 * @since 2.0
	 * @var integer
	 */
	public static $browserCacheTTL	= 604800; // 7 days = 7 * 24 * 60 * 60

	/**
	 * If TRUE, enables the faster, symlink-based request cache as a first-line cache. If FALSE, the request cache is disabled.
	 * 
	 * The request cache seems to have issues on some Windows servers.
	 * 
	 * @since 2.0
	 * @var boolean
	 */
	public static $enableRequestCache	= TRUE;

	/**
	 * How much memory (in megabytes) SLIR is allowed to allocate for memory-intensive processes such as rendering and cropping.
	 *
	 * @since 2.0
	 * @var integer
	 */
	public static $maxMemoryToAllocate	= 100;

	/**
	 * Default crop mode setting to use if crop mode is not specified in the request.
	 * 
	 * Possible values are:
	 * SLIR::CROP_CLASS_CENTERED
	 * SLIR::CROP_CLASS_TOP_CENTERED
	 * SLIR::CROP_CLASS_SMART
	 * SLIR::CROP_CLASS_FACE (not finished--does not work)
	 * 
	 * @since 2.0
	 * @var string
	 */
	public static $defaultCropper	= SLIR::CROP_CLASS_CENTERED;

	/**
	 * If TRUE, SLIR will generate and output images from error messages. If FALSE, error messages will be plaintext.
	 * 
	 * @since 2.0
	 * @var boolean
	 */
	public static $enableErrorImages	= TRUE;

	/**
	 * Absolute path to the web root (location of files when visiting http://domainname.com/) (no trailing slash).
	 * 
	 * For example, if the files for your website are located in /var/www/ on your server, this should be '/var/www'
	 * 
	 * @since 2.0
	 * @var string
	 */
	public static $documentRoot	= NULL;

	/**
	 * Absolute path to SLIR (no trailing slash) from the root directory on your server's filesystem.
	 * 
	 * @since 2.0
	 * @var string
	 */
	public static $pathToSLIR	= NULL;

	/**
	 * Absolute path to cache directory (no trailing slash). This directory must be world-readable, writable by the web server. Ideally, this directory should be located outside of the web tree.
	 * 
	 * If not specified, defaults to $pathToSlir . '/cache' inside of the directory SLIR is located.
	 * 
	 * @var string
	 */
	public static $pathToCacheDir	= NULL;

	/**
	 * Path to the error log file. Needs to be writable by the web server. Ideally, this should be located outside of the web tree.
	 * 
	 * If not specified, defaults to 'slir-error-log' in the directory SLIR is located.
	 * 
	 * @since 2.0
	 * @var string
	 */
	public static $pathToErrorLog	= NULL;

	/**
	 * If TRUE, forces SLIR to always use the query string for parameters instead of mod_rewrite.
	 *
	 * @since 2.0
	 * @var boolean
	 */
	public static $forceQueryString	= FALSE;

	/**
	 * In conjunction with $garbageCollectDivisor is used to manage probability that the garbage collection routine is started.
	 * 
	 * @since 2.0
	 * @var integer
	 */
	public static $garbageCollectProbability	= 1;

	/**
	 * Coupled with $garbageCollectProbability defines the probability that the garbage collection process is started on every request.
	 * 
	 * The probability is calculated by using $garbageCollectProbability/$garbageCollectDivisor, e.g. 1/100 means there is a 1% chance that the garbage collection process starts on each request.
	 * 
	 * @since 2.0
	 * @var integer
	 */
	public static $garbageCollectDivisor	= 200;

	/**
	 * Specifies the number of seconds after which data will be seen as 'garbage' and potentially cleaned up (deleted from the cache). 
	 * 
	 * @since 2.0
	 * @var integer
	 */
	public static $garbageCollectFileCacheMaxLifetime	= 604800; // 7 days = 7 * 24 * 60 * 60

	/**
	 * If TRUE, SLIR will copy EXIF information should from the source image to the rendered image.
	 *
	 * This can be particularly useful (necessary?) if you use an embedded color profile.
	 * 
	 * @since 2.0
	 * @var boolean
	 */
	public static $copyEXIF	= FALSE;

	/**
	 * Initialize variables that require some dynamic processing.
	 * 
	 * @since 2.0
	 * @return void
	 */
	public static function init()
	{
		if (!defined('__DIR__'))
		{
			define('__DIR__', dirname(__FILE__));
		}

		if (self::$documentRoot === NULL)
		{
			self::$documentRoot	= rtrim(realpath($_SERVER['DOCUMENT_ROOT']), '/');
		}

		if (self::$pathToSLIR === NULL)
		{
			self::$pathToSLIR	= self::$documentRoot . dirname($_SERVER['SCRIPT_NAME']);
		}

		if (self::$pathToCacheDir === NULL)
		{
			self::$pathToCacheDir 	= self::$pathToSLIR . '/cache';
		}

		if (self::$pathToErrorLog === NULL)
		{
			self::$pathToErrorLog	= self::$pathToSLIR . '/slir-error-log';
		}
	}
}