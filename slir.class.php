<?php
/**
 * Class definition file for SLIR (Smart Lencioni Image Resizer)
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
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public
 * License version 3 (GPLv3)
 * @since 2.0
 * @package SLIR
 */
 
/* $Id$ */

/**
 * SLIR (Smart Lencioni Image Resizer)
 * Resizes images, intelligently sharpens, crops based on width:height ratios,
 * color fills transparent GIFs and PNGs, and caches variations for optimal
 * performance.
 *
 * I love to hear when my work is being used, so if you decide to use this,
 * feel encouraged to send me an email. I would appreciate it if you would
 * include a link on your site back to Shifting Pixel (either the SLIR page or
 * shiftingpixel.com), but don’t worry about including a big link on each page
 * if you don’t want to–one will do just nicely. Feel free to contact me to
 * discuss any specifics (joe@shiftingpixel.com).
 *
 * REQUIREMENTS:
 *     - PHP 5.1.0+
 *     - GD
 *
 * RECOMMENDED:
 *     - mod_rewrite
 *
 * USAGE:
 * To use, place an img tag with the src pointing to the path of SLIR (typically
 * "/slir/") followed by the parameters, followed by the path to the source
 * image to resize. All parameters follow the pattern of a one-letter code and
 * then the parameter value:
 *     - Maximum width = w
 *     - Maximum height = h
 *     - Crop ratio = c
 *     - Quality = q
 *     - Background fill color = b
 *     - Progressive = p
 *
 * EXAMPLES:
 *
 * Resizing a JPEG to a max width of 100 pixels and a max height of 100 pixels:
 * <code><img src="/slir/w100-h100/path/to/image.jpg" alt="Don't forget your alt
 * text" /></code>
 *
 * Resizing and cropping a JPEG into a square:
 * <code><img src="/slir/w100-h100-c1:1/path/to/image.jpg" alt="Don't forget
 * your alt text" /></code>
 *
 * Resizing a JPEG without interlacing (for use in Flash):
 * <code><img src="/slir/w100-p0/path/to/image.jpg" alt="Don't forget your alt
 * text" /></code>
 *
 * Matting a PNG with #990000:
 * <code><img src="/slir/b900/path/to/image.png" alt="Don't forget your alt
 * text" /></code>
 *
 * Without mod_rewrite (not recommended)
 * <code><img src="/slir/?w=100&amp;h=100&amp;c=1:1&amp;image=/path/to/image.jpg"
 * alt="Don't forget your alt text" /></code>
 *
 * @author Joe Lencioni <joe@shiftingpixel.com>
 * @date $Date$
 * @version $Revision$
 * @package SLIR
 *
 * @uses PEL
 * 
 * @todo lock files when writing?
 * @todo Prevent SLIR from calling itself
 * @todo Percentage resizing?
 * @todo Animated GIF resizing?
 * @todo Seam carving?
 * @todo Crop zoom?
 * @todo Crop offsets?
 * @todo Periodic cache clearing?
 * @todo Remote image fetching?
 * @todo Alternative support for ImageMagick?
 * @todo Prevent files in cache from being read directly?
 * @todo split directory initialization and variable checking into a separate
 * install/upgrade script with friendly error messages, an opportunity to give a
 * tip, and a button that tells me they are using it on their site if they like
 * @todo document new code
 * @todo clean up new code
 */

class SLIR
{
	/**
	 * @since 2.0
	 * @var string
	 */
	const VERSION	= '2.0b3';

	/**
	 * Path to source image
	 *
	 * @since 2.0
	 * @var string
	 */
	private $imagePath;

	/**
	 * Maximum width for resized image, in pixels
	 *
	 * @since 2.0
	 * @var integer
	 */
	private $maxWidth;

	/**
	 * Maximum height for resized image, in pixels
	 *
	 * @since 2.0
	 * @var integer
	 */
	private $maxHeight;

	/**
	 * Quality setting for resized image. Ranges from 0 (worst quality, smaller
	 * file) to 100 (best quality, biggest file). If not specified, will default
	 * to SLIR_DEFAULT_QUALITY setting in slir-config.php
	 *
	 * @since 2.0
	 * @var integer
	 */
	private $quality;

	/**
	 * Ratio of width:height to crop image to.
	 *
	 * For example, if a square shape is desired, the crop ratio should be "1:1"
	 * or if a long rectangle is desired, the crop ratio could be "4:1". Stored
	 * as an associative array with keys being 'width' and 'height'.
	 *
	 * @since 2.0
	 * @var array
	 */
	private $cropRatio;

	/**
	 * Whether JPEG should be a progressive JPEG (interlaced) or not. If not
	 * specified, will default to SLIR_DEFAULT_PROGRESSIVE_JPEG setting in
	 * slir-config.php
	 *
	 * @since 2.0
	 * @var bool
	 */
	private $progressiveJPEGs;

	/**
	 * A color, in hexadecimal format (RRGGBB), to fill in as the background
	 * color for PNG images
	 *
	 * Longhand values (e.g. "FF0000") and shorthand values (e.g. "F00") are
	 * both acceptable
	 *
	 * @since 2.0
	 * @var string
	 */
	private $backgroundFillColor;

	/**
	 * Information about the source image
	 *
	 * Generated in part by {@link http://us2.php.net/getimagesize getimagesize()}
	 *
	 * @since 2.0
	 * @var array
	 */
	private $source;

	/**
	 * Information about the rendered image
	 *
	 * @since 2.0
	 * @var string
	 */
	private $rendered;

	/**
	 * Whether or not the cache has already been initialized
	 *
	 * @since 2.0
	 * @var boolean
	 */
	private $isCacheInitialized	= FALSE;

	/**
	 * The magic starts here
	 *
	 * @since 2.0
	 */
	public function __construct()
	{
		$this->getConfig();

		// Check the cache based on the request URI
		if (SLIR_USE_REQUEST_CACHE && $this->isRequestCached())
			$this->serveRequestCachedImage();
		
		// Set up our error handler after the request cache to help keep
		// everything humming along nicely
		require 'slirexception.class.php';
		set_error_handler(array('SLIRException', 'error'));
		
		// Set all parameters for resizing
		$this->setParameters($this->getParameters());

		// See if there is anything we actually need to do
		if ($this->isSourceImageDesired())
			$this->serveSourceImage();

		// Determine rendered dimensions
		$this->getRenderProperties();

		// Check the cache based on the properties of the rendered image
		if (!$this->isRenderedCached() || !$this->serveRenderedCachedImage())
		{
			// Image is not cached in any way, so we need to render the image,
			// cache it, and serve it up to the client
			$this->render();
			$this->serveRenderedImage();
		} // if

	} // __construct()

	/**
	 * Helps control the parameters as they are set
	 *
	 * @since 2.0
	 * @param string $name
	 * @param mixed $value
	 * @todo Prevent SLIR from calling itself
	 */
	private function __set($name, $value)
	{
		switch($name)
		{
			case 'i':
			case 'image':
			case 'imagePath':
				// Images must be local files, so for convenience we strip the
				// domain if it's there
				$this->imagePath	= '/' . trim(preg_replace('/^(s?f|ht)tps?:\/\/[^\/]+/i', '', (string) urldecode($value)), '/');
				
				// Strip query string from the image path
				$this->imagePath	= preg_replace('/\?.*/', '', $this->imagePath);

				// Make sure the image path is secure
				if (!$this->isPathSecure($this->imagePath))
				{
					header('HTTP/1.1 400 Bad Request');
					throw new SLIRException('Image path may not contain ":", ".'
						. '.", "<", or ">"');
				}
				// Make sure the image file exists
				else if (!$this->imageExists())
				{
					header('HTTP/1.1 404 Not Found');
					throw new SLIRException('Image does not exist: ' . SLIR_DOCUMENT_ROOT . $this->imagePath);
				}
				// Everything seems to check out just fine, proceeding normally
				else
				{
					// Set the image info (width, height, mime type, etc.)
					$this->source	= $this->getImageInfo();

					// Make sure the file is actually an image
					if (!$this->isImage())
					{
						header('HTTP/1.1 400 Bad Request');
						throw new SLIRException('Requested file is not an '
							. 'accepted image type: ' . SLIR_DOCUMENT_ROOT
							. $this->imagePath);
					} // if
				} // if
			break;

			case 'w':
			case 'width':
			case 'maxWidth':
				$this->maxWidth		= (int) $value;
			break;

			case 'h':
			case 'height':
			case 'maxHeight':
				$this->maxHeight	= (int) $value;
			break;

			case 'q':
			case 'quality':
				$this->quality		= (int) $value;

				if ($this->quality < 0 || $this->quality > 100)
				{
					header('HTTP/1.1 400 Bad Request');
					throw new SLIRException('Quality must be between 0 and 100: '
						. $this->quality);
				}
			break;

			case 'p':
			case 'progressive':
				$this->progressiveJPEGs	= (bool) $value;
			break;

			case 'c':
			case 'cropRatio':
				$ratio				= explode(':', (string) $value);
				if (count($ratio) >= 2)
				{
					$this->cropRatio	= array(
						'width'		=> (float) $ratio[0],
						'height'	=> (float) $ratio[1],
						'ratio'		=> (float) $ratio[0] / (float) $ratio[1]
					);
				}
				else
				{
					header('HTTP/1.1 400 Bad Request');
					throw new SLIRException('Crop ratio must be in width:height'
						. ' format: ' . (string) $value);
				} // if
			break;

			case 'b';
			case 'backgroundFillColor':
				$this->backgroundFillColor	= preg_replace('/[^0-9a-fA-F]/', '',
					(string) $value);

				$bglen	= strlen($this->backgroundFillColor);
				if($bglen == 3)
				{
					$this->backgroundFillColor = $this->backgroundFillColor[0]
						.$this->backgroundFillColor[0]
						.$this->backgroundFillColor[1]
						.$this->backgroundFillColor[1]
						.$this->backgroundFillColor[2]
						.$this->backgroundFillColor[2];
				}
				else if ($bglen != 6)
				{
					header('HTTP/1.1 400 Bad Request');
					throw new SLIRException('Background fill color must be in '
						.'hexadecimal format, longhand or shorthand: '
						. $this->backgroundFillColor);
				} // if
			break;
		} // switch
	} // __set()

	/**
	 * Includes the configuration file
	 *
	 * @since 2.0
	 */
	private function getConfig()
	{
		if (file_exists('slir-config.php'))
		{
			require 'slir-config.php';
		}
		else if (file_exists('slir-config-sample.php'))
		{
			if (copy('slir-config-sample.php', 'slir-config.php'))
				require 'slir-config.php';
			else
				throw new SLIRException('Could not load configuration file. '
					. 'Please copy "slir-config-sample.php" to '
					. '"slir-config.php".');
		}
		else
		{
			throw new SLIRException('Could not find "slir-config.php" or '
				. '"slir-config-sample.php"');
		} // if
	} // getConfig()

	/**
	 * Renders specified changes to the image
	 *
	 * @since 2.0
	 */
	private function render()
	{
		// We don't want to run out of memory
		ini_set('memory_limit', SLIR_MEMORY_TO_ALLOCATE);

		// Set up a blank canvas for our rendered image (destination)
		$this->rendered['image']	= imagecreatetruecolor(
										$this->rendered['width'],
										$this->rendered['height']
									);

		// Read in the original image
		$this->source['image']		= $this->rendered['functions']['create'](SLIR_DOCUMENT_ROOT . $this->imagePath);
		
		// GIF/PNG transparency and background color
		$this->background();

		// Resample the original image into the resized canvas we set up earlier
		if ($this->source['width'] != $this->rendered['width'] || $this->source['height'] != $this->rendered['height'])
		{
			ImageCopyResampled(
				$this->rendered['image'],
				$this->source['image'],
				0,
				0,
				0,
				0,
				$this->rendered['width'],
				$this->rendered['height'],
				$this->source['width'],
				$this->source['height']
			);
		}
		else // No resizing is needed, so make a clean copy
		{
			ImageCopy(
				$this->rendered['image'],
				$this->source['image'],
				0,
				0,
				0,
				0,
				$this->source['width'],
				$this->source['height']
			);
		} // if

		
		// Cropping
		if ($this->isCroppingNeeded())
			$this->crop();
		
		// Sharpen
		if ($this->rendered['sharpen'])
			$this->sharpen();

		// Set interlacing
		if ($this->rendered['progressive'])
			imageinterlace($this->rendered['image'], 1);

	} // render()
	
	/**
	 * Crops the rendered image
	 * 
	 * @since 2.0
	 * @return boolean
	 * @todo add cropping method preference (smart or centered)
	 */
	private function crop()
	{
		// Determine crop offset
		$offset		= array(
			'top'	=> 0,
			'left'	=> 0
		);
		
		if ($this->cropRatio['ratio'] > $this->source['ratio'])
		{
			// Image is too tall so we will crop the top and bottom
			$o					= $this->offset(FALSE);
			if ($o === FALSE)
				return TRUE;
			else
				$offset['top']		= $o;
		}
		else
		{
			// Image is too wide so we will crop the left and right
			$o					= $this->offset(TRUE);
			if ($o === FALSE)
				return TRUE;
			else
				$offset['left']		= $o;
		} // if
		
		// Set up a blank canvas for our cropped image (destination)
		$cropped	= imagecreatetruecolor(
						$this->rendered['cropWidth'],
						$this->rendered['cropHeight']
						);
						
		// Copy rendered image to cropped image
		ImageCopy(
			$cropped,
			$this->rendered['image'],
			0,
			0,
			$offset['left'],
			$offset['top'],
			$this->rendered['width'],
			$this->rendered['height']
		);
		
		// Replace pre-cropped image with cropped image
		imagedestroy($this->rendered['image']);
		$this->rendered['image']	= $cropped;
		unset($cropped);
		
		return TRUE;
	} // crop()

	/**
	 * Turns on transparency for rendered image if no background fill color is
	 * specified, otherwise, fills background with specified color
	 *
	 * @since 2.0
	 */
	private function background()
	{
		if (!$this->isBackgroundFillOn())
		{
			// If this is a GIF or a PNG, we need to set up transparency
			imagealphablending($this->rendered['image'], FALSE);
			imagesavealpha($this->rendered['image'], TRUE);
		}
		else
		{
			// Fill the background with the specified color for matting purposes
			$background	= imagecolorallocate(
				$this->rendered['image'],
				hexdec($this->backgroundFillColor[0].$this->backgroundFillColor[1]),
				hexdec($this->backgroundFillColor[2].$this->backgroundFillColor[3]),
				hexdec($this->backgroundFillColor[4].$this->backgroundFillColor[5])
			);

			imagefill($this->rendered['image'], 0, 0, $background);
		} // if
	} // background()

	/**
	 * Sharpens the image based on two things:
	 *   (1) the difference between the original size and the final size
	 *   (2) the final size
	 *
	 * @since 2.0
	 */
	private function sharpen()
	{
		$sharpness	= $this->calculateSharpnessFactor(
			$this->source['width'] * $this->source['height'],
			$this->rendered['width'] * $this->rendered['height']
		);

		$sharpenMatrix	= array(
			array(-1, -2, -1),
			array(-2, $sharpness + 12, -2),
			array(-1, -2, -1)
		);

		$divisor	= $sharpness;
		$offset		= 0;

		imageconvolution(
			$this->rendered['image'],
			$sharpenMatrix,
			$divisor,
			$offset
		);
	} // sharpen()

	/**
	 * Calculates sharpness factor to be used to sharpen an image based on the
	 * area of the source image and the area of the destination image
	 *
	 * @since 2.0
	 * @author Ryan Rud
	 * @link http://adryrun.com
	 *
	 * @param integer $sourceArea Area of source image
	 * @param integer $destinationArea Area of destination image
	 * @return integer Sharpness factor
	 */
	private function calculateSharpnessFactor($sourceArea, $destinationArea)
	{
		$final	= sqrt($destinationArea) * (750.0 / sqrt($sourceArea));
		$a		= 52;
		$b		= -0.27810650887573124;
		$c		= .00047337278106508946;

		$result = $a + $b * $final + $c * $final * $final;

		return max(round($result), 0);
	} // calculateSharpnessFactor()

	/**
	 * @since 2.0
	 * @param string $cacheFilePath
	 * @return string Contents of the image
	 */
	private function copyIPTC($cacheFilePath)
	{
		$data	= '';

		$iptc	= $this->source['iptc'];

		// Originating program
		$iptc['2#065']	= array('Smart Lencioni Image Resizer');

		// Program version
		$iptc['2#070']	= array(SLIR::VERSION);

		foreach($iptc as $tag => $iptcData)
		{
			$tag	= substr($tag, 2);
			$data	.= $this->makeIPTCTag(2, $tag, $iptcData[0]);
		}

		// Embed the IPTC data
		return iptcembed($data, $cacheFilePath);
	} // copyIPTC()

	/**
	 * @since 2.0
	 * @author Thies C. Arntzen
	 */
	function makeIPTCTag($rec, $data, $value)
	{
		$length = strlen($value);
		$retval = chr(0x1C) . chr($rec) . chr($data);

		if($length < 0x8000)
		{
			$retval .= chr($length >> 8) .  chr($length & 0xFF);
		}
		else
		{
			$retval .= chr(0x80) .
					   chr(0x04) .
					   chr(($length >> 24) & 0xFF) .
					   chr(($length >> 16) & 0xFF) .
					   chr(($length >> 8) & 0xFF) .
					   chr($length & 0xFF);
		}

		return $retval . $value;
	} // makeIPTCTag()

	/**
	 * Determines the parameters to use for resizing
	 *
	 * @since 2.0
	 */
	private function getParameters()
	{
		if (!$this->isUsingQueryString()) // Using the mod_rewrite version
			return $this->getParametersFromPath();
		else // Using the query string version
			return $_GET;
	} // getParameters()

	/**
	 * For requests that are using the mod_rewrite syntax
	 *
	 * @since 2.0
	 */
	private function getParametersFromPath()
	{
		$params	= array();

		// The parameters should be the first set of characters after the
		// SLIR path
		$request	= str_replace(SLIR_DIR, '', (string) $_SERVER['REQUEST_URI']);
		$request	= explode('/', trim($request, '/'));

		if (count($request) < 2)
		{
			header('HTTP/1.1 400 Bad Request');
			throw new SLIRException('Not enough parameters were given.', 'Available parameters:
w = Maximum width
h = Maximum height
c = Crop ratio (width:height)
q = Quality (0-100)
b = Background fill color (RRGGBB or RGB)
p = Progressive (0 or 1)

Example usage:
<img src="' . SLIR_DIR . '/w300-h300-c1:1/path/to/image.jpg" alt="Don\'t forget '
.'your alt text!" />'
			);

		} // if

		// The parameters are separated by hyphens
		$rawParams	= array_filter(explode('-', array_shift($request)));

		// The image path should be all of the remaining values in the array
		$params['i']	= implode('/', $request);

		foreach ($rawParams as $rawParam)
		{
			// The name of each parameter should be the first character of the
			// parameter string
			$name	= $rawParam[0];
			// The value of each parameter should be the remaining characters of
			// the parameter string
			$value	= substr($rawParam, 1, strlen($rawParam) - 1);

			$params[$name]	= $value;
		} // foreach

		$params	= array_filter($params);

		return $params;
	} // getParametersFromPath()

	/**
	 * Sets up parameters for image resizing
	 *
	 * @since 2.0
	 * @param array $params Associative array of parameters
	 */
	private function setParameters($params)
	{
		// Set image path first
		if (isset($params['i']) && $params['i'] != '' && $params['i'] != '/')
		{
			$this->__set('i', $params['i']);
			unset($params['i']);
		}
		else
		{
			header('HTTP/1.1 400 Bad Request');
			throw new SLIRException('Source image was not specified.');
		} // if

		// Set the rest of the parameters
		foreach($params as $name => $value)
		{
			$this->__set($name, $value);
		} // foreach

		// If either a max width or max height are not specified or larger than
		// the source image we default to the dimension of the source image so
		// they do not become constraints on our resized image.
		if (!$this->maxWidth || $this->maxWidth > $this->source['width'])
			$this->maxWidth		= $this->source['width'];

		if (!$this->maxHeight ||  $this->maxHeight > $this->source['height'])
			$this->maxHeight	= $this->source['height'];

	} // setParameters()

	/**
	 * Determines if the request is using the mod_rewrite version or the query
	 * string version
	 *
	 * @since 2.0
	 * @return bool
	 */
	private function isUsingQueryString()
	{
		if (isset($_SERVER['QUERY_STRING'])
			&& trim($_SERVER['QUERY_STRING']) != ''
			&& count(array_intersect(array('i', 'w', 'h', 'q', 'c', 'b'), array_keys($_GET)))
			)
			return TRUE;
		else
			return FALSE;
	} // isUsingQueryString()

	/**
	 * Checks to see if the image path is secure
	 *
	 * For security, directories may not contain ':' and images may not contain
	 * '..', '<', or '>'.
	 *
	 * @since 2.0
	 * @param string $path
	 * @return bool
	 */
	private function isPathSecure($path)
	{
		if (strpos(dirname($path), ':') || preg_match('/(\.\.|<|>)/', $path))
			return FALSE;
		else
			return TRUE;
	} // isImagePathSecure()

	/**
	 * Determines if the source image exists
	 *
	 * @since 2.0
	 * @return bool
	 */
	private function imageExists()
	{
		return is_file(SLIR_DOCUMENT_ROOT . $this->imagePath);
	} // imageExists()

	/**
	 * Retrieves information about the source image such as width and height
	 *
	 * @since 2.0
	 * @return array
	 */
	private function getImageInfo()
	{
		$info			= getimagesize(SLIR_DOCUMENT_ROOT . $this->imagePath, $extraInfo);

		if ($info == FALSE)
		{
			header('HTTP/1.1 400 Bad Request');
			throw new SLIRException('getimagesize failed (source file may not '
				. 'be an image): ' . SLIR_DOCUMENT_ROOT . $this->imagePath);
		}

		$info['width']	=& $info[0];
		$info['height']	=& $info[1];
		$info['ratio']	= $info['width']/$info['height'];

		// IPTC
		if(is_array($extraInfo) && isset($extraInfo['APP13']))
				$info['iptc']	= iptcparse($extraInfo['APP13']);

		return $info;
	} // getImageInfo()

	/**
	 * Checks the image info and image's mime type to see if it is an image
	 *
	 * @since 2.0
	 * @return bool
	 */
	private function isImage()
	{
		if ($this->source !== FALSE
			|| substr($this->source['mime'], 0, 6) == 'image/')
			return TRUE;
		else
			return FALSE;
	} // isImage()

	/**
	 * Checks parameters against the image's attributes and determines whether
	 * anything needs to be changed or if we simply need to serve up the source
	 * image
	 *
	 * @since 2.0
	 * @return bool
	 * @todo Add check for JPEGs and progressiveness
	 */
	private function isSourceImageDesired()
	{
		if ($this->isWidthDifferent()
			|| $this->isHeightDifferent()
			|| $this->isBackgroundFillOn()
			|| $this->isQualityOn()
			|| $this->isCroppingNeeded()
			)
			return FALSE;
		else
			return TRUE;

	} // isSourceImageDesired()

	/**
	 * @since 2.0
	 * @return bool
	 */
	private function isWidthDifferent()
	{
		if ($this->maxWidth !== NULL
			&& $this->maxWidth < $this->source['width']
			)
			return TRUE;
		else
			return FALSE;
	} // isWidthDifferent()

	/**
	 * @since 2.0
	 * @return bool
	 */
	private function isHeightDifferent()
	{
		if ($this->maxHeight !== NULL
			&& $this->maxHeight < $this->source['height']
			)
			return TRUE;
		else
			return FALSE;
	} // isHeightDifferent()

	/**
	 * @since 2.0
	 * @return bool
	 */
	private function isBackgroundFillOn()
	{
		if ($this->backgroundFillColor !== NULL
			&& ($this->isSourceGIF() || $this->isSourcePNG())
			)
			return TRUE;
		else
			return FALSE;
	} // isBackgroundFillOn()

	/**
	 * @since 2.0
	 * @return boolean
	 */
	private function isQualityOn()
	{
		if ($this->quality !== NULL)
			return TRUE;
		else
			return FALSE;
	} // isQualityOn()

	/**
	 * @since 2.0
	 * @return boolean
	 */
	private function isCroppingNeeded()
	{
		if ($this->cropRatio['width'] !== NULL
			&& $this->cropRatio['height'] !== NULL
			&& $this->cropRatio['ratio'] != $this->source['ratio']
			)
			return TRUE;
		else
			return FALSE;
	} // isCroppingNeeded()

	/**
	 * @since 2.0
	 * @parram array $imageArray
	 * @param string $type Can be 'JPEG', 'GIF', or 'PNG'
	 * @return boolean
	 */
	private function isImageOfType($imageArray, $type = 'JPEG')
	{
		$method	= "is$type";
		if (method_exists($this, $method) && isset($imageArray['mime']))
			return $this->$method($imageArray['mime']);
	} // isImageOfType()

	/**
	 * @since 2.0
	 * @param string $mimeType
	 * @return boolean
	 */
	private function isJPEG($mimeType)
	{
		if ($mimeType == 'image/jpeg')
			return TRUE;
		else
			return FALSE;
	} // isJPEG()

	/**
	 * @since 2.0
	 * @param string $mimeType
	 * @return boolean
	 */
	private function isGIF($mimeType)
	{
		if ($mimeType == 'image/gif')
			return TRUE;
		else
			return FALSE;
	} // isGIF()

	/**
	 * @since 2.0
	 * @param string $mimeType
	 * @return boolean
	 */
	private function isPNG($mimeType)
	{
		if (in_array($mimeType, array('image/png', 'image/x-png')))
			return TRUE;
		else
			return FALSE;
	} // isPNG()

	/**
	 * @since 2.0
	 * @return boolean
	 */
	private function isSourceJPEG()
	{
		return $this->isImageOfType($this->source, 'JPEG');
	} // isSourceJPEG()

	/**
	 * @since 2.0
	 * @return boolean
	 */
	private function isRenderedJPEG()
	{
		return $this->isImageOfType($this->rendered, 'JPEG');
	} // isRenderedJPEG()

	/**
	 * @since 2.0
	 * @return boolean
	 */
	private function isSourceGIF()
	{
		return $this->isImageOfType($this->source, 'GIF');
	} // isSourceGIF()

	/**
	 * @since 2.0
	 * @return boolean
	 */
	private function isRenderedGIF()
	{
		return $this->isImageOfType($this->rendered, 'GIF');
	} // isRenderedGIF()

	/**
	 * @since 2.0
	 * @return boolean
	 */
	private function isSourcePNG()
	{
		return $this->isImageOfType($this->source, 'PNG');
	} // isSourcePNG()

	/**
	 * @since 2.0
	 * @return boolean
	 */
	private function isRenderedPNG()
	{
		return $this->isImageOfType($this->rendered, 'PNG');
	} // isRenderedPNG()

	/**
	 * Computes and sets properties of the rendered image, such as the actual
	 * width, height, and quality
	 *
	 * @since 2.0
	 */
	private function getRenderProperties()
	{
		// Set default properties of the rendered image
		$this->rendered	= array(
			'width'		=> $this->source['width'],
			'height'	=> $this->source['height'],
			'quality'	=> 0,
			'mime'		=> $this->source['mime'],
			'functions'	=> array(
				'create'	=> 'ImageCreateFromJpeg',
				'output'	=> 'ImageJpeg'
			),
			'sharpen'		=> TRUE,
			'progressive'	=> $this->progressiveJPEGs,
			'background'	=> $this->backgroundFillColor
		);
		
		// Set the ratios needed for resizing. We will compare these below to
		// determine how to resize the image (based on height or based on width)
		$ratios		= array(
			'width'		=> $this->maxWidth / $this->source['width'],
			'height'	=> $this->maxHeight / $this->source['height']
		);
		
		// Values used to make comparisons below
		$compare	= array(
			'heightRatio'	=> $ratios['height'],
			'widthRatio'	=> $ratios['width']
		);
		
		// Cropping
		/*
		To determine the width and height of the rendered image, the following
		should occur.
		
		If cropping an image is required, we need to:
		 1. Compute the dimensions of the source image after cropping before
			resizing.
		 2. Compute the dimensions of the resized image before cropping. One of 
			these dimensions may be greater than maxWidth or maxHeight because
			they are based on the dimensions of the final rendered image, which
			will be cropped to fit within the specified maximum dimensions.
		 3. Compute the dimensions of the resized image after cropping. These
			must both be less than or equal to maxWidth and maxHeight.
		 4. Then when rendering, the image needs to be resized, crop offsets
			need to be computed based on the desired method (smart or centered),
			and the image needs to be cropped to the specified dimensions.
		
		If cropping an image is not required, we need to compute the dimensions
		of the image without cropping. These must both be less than or equal to
		maxWidth and maxHeight.
		*/
		if ($this->isCroppingNeeded())
		{
			// Determine the dimensions of the source image after cropping and
			// before resizing
			
			if ($this->cropRatio['ratio'] > $this->source['ratio'])
			{
				// Image is too tall so we will crop the top and bottom
				$this->source['cropHeight']	= $this->source['width'] / $this->cropRatio['ratio'];
				$this->source['cropWidth']	= $this->source['width'];
			}
			else
			{
				// Image is too wide so we will crop off the left and right sides
				$this->source['cropWidth']	= $this->source['height'] * $this->cropRatio['ratio'];
				$this->source['cropHeight']	= $this->source['height'];
			} // if
			
			$ratios['cropWidth']		= $this->maxWidth / $this->source['cropWidth'];
			$ratios['cropHeight']		= $this->maxHeight / $this->source['cropHeight'];
			
			$compare['widthRatio']		= $ratios['cropWidth'];
			$compare['heightRatio']		= $ratios['cropHeight'];
		} // if

		if (floor($compare['widthRatio'] * $this->source['height']) <= $this->maxHeight)
		{
			// Resize the image based on width
			$this->rendered['height']	= ceil($compare['widthRatio'] * $this->source['height']);
			$this->rendered['width']	= ceil($compare['widthRatio'] * $this->source['width']);
			
			// Determine dimensions after cropping
			if (isset($this->source['cropWidth']))
			{
				$this->rendered['cropHeight']	= ceil($compare['widthRatio'] * $this->source['cropHeight']);
				$this->rendered['cropWidth']	= ceil($compare['widthRatio'] * $this->source['cropWidth']);
			} // if
		}
		else if (floor($compare['heightRatio'] * $this->source['width']) <= $this->maxWidth)
		{
			// Resize the image based on height
			$this->rendered['width']	= ceil($compare['heightRatio'] * $this->source['width']);
			$this->rendered['height']	= ceil($compare['heightRatio'] * $this->source['height']);
			
			// Determine dimensions after cropping
			if (isset($this->source['cropWidth']))
			{
				$this->rendered['cropHeight']	= ceil($compare['heightRatio'] * $this->source['cropHeight']);
				$this->rendered['cropWidth']	= ceil($compare['heightRatio'] * $this->source['cropWidth']);
			} // if
		}
		else if (isset($this->source['cropWidth'])) // No resizing is needed but we still need to crop
		{
			$ratio	= ($ratios['width'] > $ratios['height']) ? $ratios['width'] : $ratios['height'];
			$this->rendered['width']		= ceil($ratio * $this->source['width']);
			$this->rendered['height']		= ceil($ratio * $this->source['height']);
			
			$this->rendered['cropWidth']	= ceil($ratio * $this->source['cropWidth']);
			$this->rendered['cropHeight']	= ceil($ratio * $this->source['cropHeight']);
		} // if

		// Determine the quality of the output image
		$this->rendered['quality']		= ($this->quality !== NULL) ? $this->quality : SLIR_DEFAULT_QUALITY;

		// Set up the appropriate image handling functions based on the original
		// image's mime type
		switch ($this->source['mime'])
		{
			case 'image/gif':
				// We will be converting GIFs to PNGs to avoid transparency
				// issues when resizing GIFs
				// This is maybe not the ideal solution, but IE6 can suck it
				$this->rendered['functions']['create']	= 'ImageCreateFromGif';
				$this->rendered['functions']['output']	= 'ImagePng';
				// We need to convert GIFs to PNGs
				$this->rendered['mime']					= 'image/png';
				$this->rendered['sharpen']				= FALSE;
				$this->rendered['progressive']			= FALSE;

				// We are converting the GIF to a PNG, and PNG needs a
				// compression level of 0 (no compression) through 9
				$this->rendered['quality']				= round(10 - ($this->rendered['quality'] / 10));
			break;

			case 'image/x-png':
			case 'image/png':
				$this->rendered['functions']['create']	= 'ImageCreateFromPng';
				$this->rendered['functions']['output']	= 'ImagePng';
				$this->rendered['mime']					= $this->source['mime'];
				$this->rendered['sharpen']				= FALSE;
				$this->rendered['progressive']			= FALSE;

				// PNG needs a compression level of 0 (no compression) through 9
				$this->rendered['quality']				= round(10 - ($this->rendered['quality'] / 10));
			break;

			default:
				$this->rendered['functions']['create']	= 'ImageCreateFromJpeg';
				$this->rendered['functions']['output']	= 'ImageJpeg';
				$this->rendered['mime']					= $this->source['mime'];
				$this->rendered['progressive']			= ($this->progressiveJPEGs !== NULL) ? $this->progressiveJPEGs : SLIR_DEFAULT_QUALITY;
				$this->rendered['background']			= NULL;
			break;
		} // switch

	} // getRenderProperties()
	
	/**
	 * Determines the optimal number of rows in from the top or left to crop
	 * the source image
	 * 
	 * @since 2.0
	 * @param boolean $fromLeft If TRUE, will calculate from the left edge. If
	 * FALSE, will calculate from the top edge
	 * @return integer|boolean
	 */
	private function offset($fromLeft = TRUE)
	{
		if ($fromLeft)
		{
			$length				= $this->rendered['cropWidth'];
			$lengthB			= $this->rendered['cropHeight'];
			$originalLength		= $this->rendered['width'];
		}
		else
		{
			$length				= $this->rendered['cropHeight'];
			$lengthB			= $this->rendered['cropWidth'];
			$originalLength		= $this->rendered['height'];
		} // if
		
		// To smart crop an image, we need to calculate the difference between
		// each pixel in each row and its adjacent pixels. Add these up to
		// determine how interesting each row is. Based on how interesting each
		// row is, we can determine whether or not to discard it. We start with
		// the closest row and the farthest row and then move on from there.
		
		// All colors in the image will be stored in the global colors array.
		// This array will also include information about each pixel's
		// interestingness.
		// 
		// For example (rough representation):
		// 
		// $colors = array(
		//   x1	=> array(
		//   	x1y1	=> array(
		//			'lab'	=> array(l, a, b),
		//			'dE'	=> array(TL, TC, TR, LC, LR, BL, BC, BR),
		//			'i'		=> computedInterestingness
		//   	),
		//		x1y2	=> array( ... ),
		//		...
		//   ),
		//   x2	=> array( ... ),
		//   ...
		// );
		global $colors;
		$colors	= array();
		
		// Offset will remember how far in from each side we are in the
		// cropping game
		$offset	= array(
			'near'	=> 0,
			'far'	=> 0
		);
		
		$rowsToCrop	= $originalLength - $length;
		
		// $pixelStep will sacrifice accuracy for memory and speed. Essentially
		// it acts as a spot-checker and scales with the size of the cropped area
		$pixelStep	= round( sqrt($rowsToCrop * $lengthB) / 10);
		
		// We won't save much speed if the pixelStep is between 4 and 1 because
		// we still need to sample adjacent pixels
		if ($pixelStep < 4)
			$pixelStep = 1;
		
		$tolerance	= 0.5;
		$upperTol	= 1 + $tolerance;
		$lowerTol	= 1 / $upperTol;
		
		// Fight the near and far rows. The stronger will remain standing.
		$returningChampion	= NULL;
		$ratio				= 1;
		for($rowsCropped = 0; $rowsCropped < $rowsToCrop; ++$rowsCropped)
		{
			$a	= $this->rowInterestingness($offset['near'], $fromLeft, $pixelStep);
			$b	= $this->rowInterestingness($originalLength - $offset['far'] - 1, $fromLeft, $pixelStep);
			
			if ($a == 0 && $b == 0)
				$ratio = 1;
			else if ($b == 0)
				$ratio = 1 + $a;
			else
				$ratio	= $a / $b;
			
			if ($ratio > $upperTol)
			{
				++$offset['far'];
				
				// Fightback. Winning side gets to go backwards through fallen rows
				// to see if they are stronger
				if ($returningChampion == 'near')
					$offset['near']	-= ($offset['near'] > 0) ? 1 : 0;
				else
					$returningChampion	= 'near';
			}
			else if ($ratio < $lowerTol)
			{
				++$offset['near'];
				
				if ($returningChampion == 'far')
					$offset['far']	-= ($offset['far'] > 0) ? 1 : 0;
				else
					$returningChampion	= 'far';
			}
			else
			{
				// There is no strong winner, so discard rows from the side that
				// has lost the fewest so far. Essentially this is a draw.
				if ($offset['near'] > $offset['far'])
					++$offset['far'];
				else // Discard near
					++$offset['near'];
					
				// No fightback for draws
				$returningChampion	= NULL;
			} // if
			
		} // for
		
		// Bounceback for potentially important details on the edge.
		// This may possibly be better if the winning side fights a hard final
		// push multiple-rows-at-stake battle where it stands the chance to gain
		// ground.
		if ($ratio > (1 + ($tolerance * 1.25)))
			$offset['near'] -= round($length * .03);
		else if ($ratio < (1 / (1 + ($tolerance * 1.25))))
			$offset['near']	+= round($length * .03);
			
		return min($rowsToCrop, max(0, $offset['near']));
	} // offset()
	
	private function rowInterestingness($row, $fromLeft, $pixelStep)
	{
		$interestingness	= 0;
		$max				= 0;
		
		if ($fromLeft)
		{
			for($totalPixels = 0; $totalPixels < $this->rendered['height']; $totalPixels += $pixelStep)
			{
				$i					= $this->pixelInterestingness($row, $totalPixels);
				$max				= max($i, $max);
				$interestingness	+= $i;
			}
		}
		else
		{
			for($totalPixels = 0; $totalPixels < $this->rendered['width']; $totalPixels += $pixelStep)
			{
				$i					= $this->pixelInterestingness($totalPixels, $row);
				$max				= max($i, $max);
				$interestingness	+= $i;
			}
		}
		
		return $interestingness + (($max - ($interestingness / ($totalPixels / $pixelStep))) * ($totalPixels / $pixelStep));
	} // rowInterestingness()
	
	private function pixelInterestingness($x, $y)
	{
		global $colors;
		
		if (!isset($colors[$x][$y]['i']))
		{
			// Ensure this pixel's color information has already been loaded
			$this->loadPixelInfo($x, $y);
			
			// Calculate each neighboring pixel's Delta E in relation to this
			// pixel
			$this->calculateDeltas($x, $y);
			
			// Calculate the interestingness of this pixel based on neighboring
			// pixels' Delta E in relation to this pixel
			$this->calculateInterestingness($x, $y);
		} // if
		
		return $colors[$x][$y]['i'];
	} // pixelInterestingness()
	
	private function loadPixelInfo($x, $y)
	{
		if ($x < 0 || $x >= $this->rendered['width']
			|| $y < 0 || $y >= $this->rendered['height'])
				return FALSE;
				
		global $colors;
		
		if (!isset($colors[$x]))
			$colors[$x]	= array();
			
		if (!isset($colors[$x][$y]))
			$colors[$x][$y]	= array();
		
		if (!isset($colors[$x][$y]['i']) && !isset($colors[$x][$y]['lab']))
			$colors[$x][$y]['lab']	= $this->evaluateColor(imagecolorat($this->rendered['image'], $x, $y));
			
		return TRUE;
	} // loadPixelInfo()
	
	private function calculateDeltas($x, $y)
	{
		// Calculate each adjacent pixel's Delta E in relation to the current
		// pixel (top left, top center, top right, center left, center right,
		// bottom left, bottom center, and bottom right)
		
		global $colors;
		
		if (!isset($colors[$x][$y]['dE']['d-1-1']))
			$this->calculateDelta($x, $y, -1, -1);
		if (!isset($colors[$x][$y]['dE']['d0-1']))
			$this->calculateDelta($x, $y, 0, -1);
		if (!isset($colors[$x][$y]['dE']['d1-1']))
			$this->calculateDelta($x, $y, 1, -1);
		if (!isset($colors[$x][$y]['dE']['d-10']))
			$this->calculateDelta($x, $y, -1, 0);
		if (!isset($colors[$x][$y]['dE']['d10']))
			$this->calculateDelta($x, $y, 1, 0);
		if (!isset($colors[$x][$y]['dE']['d-11']))
			$this->calculateDelta($x, $y, -1, 1);
		if (!isset($colors[$x][$y]['dE']['d01']))
			$this->calculateDelta($x, $y, 0, 1);
		if (!isset($colors[$x][$y]['dE']['d11']))
			$this->calculateDelta($x, $y, 1, 1);
		
		return TRUE;
	} // calculateDeltas()
	
	private function calculateDelta($x1, $y1, $xMove, $yMove)
	{
		$x2	= $x1 + $xMove;
		$y2 = $y1 + $yMove;
		
		// Pixel is outside of the image, so we cant't calculate the Delta E
		if ($x2 < 0 || $x2 >= $this->rendered['width']
			|| $y2 < 0 || $y2 >= $this->rendered['height'])
				return NULL;
		
		global $colors;
		
		if (!isset($colors[$x1][$y1]['lab']))
			$this->loadPixelInfo($x1, $y1);
		if (!isset($colors[$x2][$y2]['lab']))
			$this->loadPixelInfo($x2, $y2);
		
		$delta	= $this->deltaE($colors[$x1][$y1]['lab'], $colors[$x2][$y2]['lab']);
		
		$colors[$x1][$y1]['dE']["d$xMove$yMove"]	= $delta;
		
		$x2Move	= $xMove * -1;
		$y2Move	= $yMove * -1;
		$colors[$x2][$y2]['dE']["d$x2Move$y2Move"]	=& $colors[$x1][$y1]['dE']["d$xMove$yMove"];
		
		return TRUE;
	} // calculateDelta()
	
	private function calculateInterestingness($x, $y)
	{
		global $colors;
		
		// The interestingness is the average of the pixel's Delta E values
		$colors[$x][$y]['i']	= array_sum($colors[$x][$y]['dE'])
			/ count(array_filter($colors[$x][$y]['dE'], 'is_numeric'));
		
		return TRUE;
	} // calculateInterestingness()
	
	/**
	 * @since 2.0
	 * @param integer $int
	 * @return array
	 */
	private function evaluateColor($int)
	{
		$rgb	= $this->colorIndexToRGB($int);
		$xyz	= $this->RGBtoXYZ($rgb);
		$lab	= $this->XYZtoHunterLab($xyz);
		
		return $lab;
	} // evaluateColor()
	
	/**
	 * @since 2.0
	 * @param integer $int
	 * @return array
	 */
	private function colorIndexToRGB($int)
	{
		$a	= (255 - (($int >> 24) & 0xFF)) / 255;
		$r	= (($int >> 16) & 0xFF) * $a;
		$g	= (($int >> 8) & 0xFF) * $a;
		$b	= ($int & 0xFF) * $a;
		return array('r' => $r, 'g' => $g, 'b' => $b);
	} // colorIndexToRGB()
	
	/**
	 * @since 2.0
	 * @param array $rgb
	 * @return array XYZ
	 * @link http://easyrgb.com/index.php?X=MATH&H=02#text2
	 */
	private function RGBtoXYZ($rgb)
	{
		$r	= $rgb['r'] / 255;
		$g	= $rgb['g'] / 255;
		$b	= $rgb['b'] / 255;
		
		if ($r > 0.04045)
			$r	= pow((($r + 0.055) / 1.055), 2.4);
		else
			$r	= $r / 12.92;
		
		if ($g > 0.04045)
			$g	= pow((($g + 0.055) / 1.055), 2.4);
		else
			$g	= $g / 12.92;
		
		if ($b > 0.04045)
			$b	= pow((($b + 0.055) / 1.055), 2.4);
		else
			$b	= $b / 12.92;
			
		$r	*= 100;
		$g	*= 100;
		$b	*= 100;

		//Observer. = 2°, Illuminant = D65
		$x = $r * 0.4124 + $g * 0.3576 + $b * 0.1805;
		$y = $r * 0.2126 + $g * 0.7152 + $b * 0.0722;
		$z = $r * 0.0193 + $g * 0.1192 + $b * 0.9505;
		
		return array('x' => $x, 'y' => $y, 'z' => $z);
	} // RGBtoXYZ()
	
	/**
	 * @link http://www.easyrgb.com/index.php?X=MATH&H=05#text5
	 */ 
	private function XYZtoHunterLab($xyz)
	{
		if ($xyz['y'] == 0)
			return array('l' => 0, 'a' => 0, 'b' => 0);
		
		$l	= 10 * sqrt($xyz['y']);
		$a	= 17.5 * ( ( ( 1.02 * $xyz['x'] ) - $xyz['y']) / sqrt( $xyz['y'] ) );
		$b	= 7 * ( ( $xyz['y'] - ( 0.847 * $xyz['z'] ) ) / sqrt( $xyz['y'] ) );
		
		return array('l' => $l, 'a' => $a, 'b' => $b);
	} // XYZtoHunterLab()
	
	/**
	 * Converts a color from RGB colorspace to CIE-L*ab colorspace
	 * @since 2.0
	 * @param array $xyz
	 * @return array LAB
	 * @link http://www.easyrgb.com/index.php?X=MATH&H=05#text5
	 */
	private function XYZtoCIELAB($xyz)
	{
		$refX	= 100;
		$refY	= 100;
		$refZ	= 100;
		
		$X = $xyz['x'] / $refX;
		$Y = $xyz['y'] / $refY;
		$Z = $xyz['z'] / $refZ;
		
		if ( $X > 0.008856 )
			$X = pow($X, 1/3);
		else
			$X = ( 7.787 * $X ) + ( 16 / 116 );
			
		if ( $Y > 0.008856 ) 
			$Y = pow($Y, 1/3);
		else
			$Y = ( 7.787 * $Y ) + ( 16 / 116 );
			
		if ( $Z > 0.008856 )
			$Z = pow($Z, 1/3);
		else
			$Z = ( 7.787 * $Z ) + ( 16 / 116 );

		$l = ( 116 * $Y ) - 16;
		$a = 500 * ( $X - $Y );
		$b = 200 * ( $Y - $Z );
		
		return array('l' => $l, 'a' => $a, 'b' => $b);
	} // XYZtoCIELAB()
	
	private function deltaE($lab1, $lab2)
	{
		return sqrt( ( pow( $lab1['l'] - $lab2['l'], 2 ) )
               + ( pow( $lab1['a'] - $lab2['a'], 2 ) )
               + ( pow( $lab1['b'] - $lab2['b'], 2 ) ) );
	} // deltaE()
	
	/**
	 * Compute the Delta E 2000 value of two colors in the LAB colorspace
	 * 
	 * @link http://en.wikipedia.org/wiki/Color_difference#CIEDE2000
	 * @link http://easyrgb.com/index.php?X=DELT&H=05#text5
	 * @since 2.0
	 * @param array $lab1 LAB color array
	 * @param array $lab2 LAB color array
	 * @return float
	 */
	private function deltaE2000($lab1, $lab2)
	{
		$weightL	= 1; // Lightness
		$weightC	= 1; // Chroma
		$weightH	= 1; // Hue
		
		$xC1 = sqrt( $lab1['a'] * $lab1['a'] + $lab1['b'] * $lab1['b'] );
		$xC2 = sqrt( $lab2['a'] * $lab2['a'] + $lab2['b'] * $lab2['b'] );
		$xCX = ( $xC1 + $xC2 ) / 2;
		$xGX = 0.5 * ( 1 - sqrt( ( pow($xCX, 7) ) / ( ( pow($xCX, 7) ) + ( pow(25, 7) ) ) ) );
		$xNN = ( 1 + $xGX ) * $lab1['a'];
		$xC1 = sqrt( $xNN * $xNN + $lab1['b'] * $lab1['b'] );
		$xH1 = $this->LABtoHue( $xNN, $lab1['b'] );
		$xNN = ( 1 + $xGX ) * $lab2['a'];
		$xC2 = sqrt( $xNN * $xNN + $lab2['b'] * $lab2['b'] );
		$xH2 = $this->LABtoHue( $xNN, $lab2['b'] );
		$xDL = $lab2['l'] - $lab1['l'];
		$xDC = $xC2 - $xC1;
		
		if ( ( $xC1 * $xC2 ) == 0 )
		{
		   $xDH = 0;
		}
		else
		{
			$xNN = round( $xH2 - $xH1, 12 );
			if ( abs( $xNN ) <= 180 )
			{
				$xDH = $xH2 - $xH1;
			}
			else
			{
				if ( $xNN > 180 )
					$xDH = $xH2 - $xH1 - 360;
				else
					$xDH = $xH2 - $xH1 + 360;
			} // if
		} // if

		$xDH = 2 * sqrt( $xC1 * $xC2 ) * sin( rad2deg( $xDH / 2 ) );
		$xLX = ( $lab1['l'] + $lab2['l'] ) / 2;
		$xCY = ( $xC1 + $xC2 ) / 2;

		if ( ( $xC1 *  $xC2 ) == 0 )
		{
			$xHX = $xH1 + $xH2;
		}
		else
		{
			$xNN = abs( round( $xH1 - $xH2, 12 ) );
			if ( $xNN >  180 )
			{
				if ( ( $xH2 + $xH1 ) <  360 )
					$xHX = $xH1 + $xH2 + 360;
				else
					$xHX = $xH1 + $xH2 - 360;
			}
			else
			{
				$xHX = $xH1 + $xH2;
			} // if
			$xHX /= 2;
		} // if

		$xTX = 1 - 0.17 * cos( rad2deg( $xHX - 30 ) )
			+ 0.24 * cos( rad2deg( 2 * $xHX ) )
			+ 0.32 * cos( rad2deg( 3 * $xHX + 6 ) )
			- 0.20 * cos( rad2deg( 4 * $xHX - 63 ) );
					   
		$xPH = 30 * exp( - ( ( $xHX  - 275 ) / 25 ) * ( ( $xHX  - 275 ) / 25 ) );
		$xRC = 2 * sqrt( ( pow($xCY, 7) ) / ( ( pow($xCY, 7) ) + ( pow(25, 7) ) ) );
		$xSL = 1 + ( ( 0.015 * ( ( $xLX - 50 ) * ( $xLX - 50 ) ) )
			/ sqrt( 20 + ( ( $xLX - 50 ) * ( $xLX - 50 ) ) ) );
		$xSC = 1 + 0.045 * $xCY;
		$xSH = 1 + 0.015 * $xCY * $xTX;
		$xRT = - sin( rad2deg( 2 * $xPH ) ) * $xRC;
		$xDL = $xDL / $weightL * $xSL;
		$xDC = $xDC / $weightC * $xSC;
		$xDH = $xDH / $weightH * $xSH;

		$delta	= sqrt( pow($xDL, 2) + pow($xDC, 2) + pow($xDH, 2) + $xRT * $xDC * $xDH );
		return (is_nan($delta)) ? 1 : $delta / 100;
	} // deltaE2000()
	
	/**
	 * Compute the Delta CMC value of two colors in the LAB colorspace
	 * 
	 * @since 2.0
	 * @param array $lab1 LAB color array
	 * @param array $lab2 LAB color array
	 * @return float
	 * @link http://easyrgb.com/index.php?X=DELT&H=06#text6
	 */
	private function deltaCMC($lab1, $lab2)
	{
		// if $weightL is 2 and $weightC is 1, it means that the lightness
		// will contribute half as much importance to the delta as the chroma
		$weightL	= 2; // Lightness
		$weightC	= 1; // Chroma
		
		$xC1	= sqrt( ( pow($lab1['a'], 2) ) + ( pow($lab1['b'], 2) ) );
		$xC2	= sqrt( ( pow($lab2['a'], 2) ) + ( pow($lab2['b'], 2) ) );
		$xff	= sqrt( ( pow($xC1, 4) ) / ( ( pow($xC1, 4) ) + 1900 ) );
		$xH1	= $this->LABtoHue( $lab1['a'], $lab1['b'] );
		
		if ( $xH1 < 164 || $xH1 > 345 )
			$xTT	= 0.36 + abs( 0.4 * cos( deg2rad(  35 + $xH1 ) ) );
		else
			$xTT	= 0.56 + abs( 0.2 * cos( deg2rad( 168 + $xH1 ) ) );
		
		if ( $lab1['l'] < 16 )
			$xSL	= 0.511;
		else
			$xSL	= ( 0.040975 * $lab1['l'] ) / ( 1 + ( 0.01765 * $lab1['l'] ) );
			
		$xSC = ( ( 0.0638 * $xC1 ) / ( 1 + ( 0.0131 * $xC1 ) ) ) + 0.638;
		$xSH = ( ( $xff * $xTT ) + 1 - $xff ) * $xSC;
		$xDH = sqrt( pow( $lab2['a'] - $lab1['a'], 2 ) + pow( $lab2['b'] - $lab1['b'], 2 ) - pow( $xC2 - $xC1, 2 ) );
		$xSL = ( $lab2['l'] - $lab1['l'] ) / $weightL * $xSL;
		$xSC = ( $xC2 - $xC1 ) / $weightC * $xSC;
		$xSH = $xDH / $xSH;
		
		$delta = sqrt( pow($xSL, 2) + pow($xSC, 2) + pow($xSH, 2) );
		return (is_nan($delta)) ? 1 : $delta;
	} // deltaCMC()
	
	/**
	 * @since 2.0
	 * @param integer $a
	 * @param integer $b
	 * @return CIE-H° value
	 */
	private function LABtoHue($a, $b)
	{
		$bias	= 0;
		
		if ($a >= 0 && $b == 0) return 0;
		if ($a <  0 && $b == 0) return 180;
		if ($a == 0 && $b >  0) return 90;
		if ($a == 0 && $b <  0) return 270;
		if ($a >  0 && $b >  0) $bias = 0;
		if ($a <  0           ) $bias = 180;
		if ($a >  0 && $b <  0) $bias = 360;
		
		return (rad2deg(atan($b / $a)) + $bias);
	} // LABtoHue()
	
	/**
	 * @since 2.0
	 * @return boolean
	 */
	private function isRenderedCached()
	{
		return $this->isCached($this->renderedCacheFilePath());
	} // isRenderedCached()

	/**
	 * @since 2.0
	 * @return boolean
	 */
	private function isRequestCached()
	{
		return $this->isCached($this->requestCacheFilePath());
	} // isRequestCached()

	/**
	 * @since 2.0
	 * @param string $cacheFilePath
	 * @return boolean
	 */
	private function isCached($cacheFilePath)
	{
		if (!file_exists($cacheFilePath))
			return FALSE;

		$cacheModified	= filemtime($cacheFilePath);

		if (!$cacheModified)
			return FALSE;

		$imageModified	= filemtime(SLIR_DOCUMENT_ROOT . $this->imagePath);

		if ($imageModified >= $cacheModified)
			return FALSE;
		else
			return TRUE;
	} // isCached()

	/**
	 * @since 2.0
	 * @return string
	 */
	private function renderedCacheFilename()
	{
		$cacheParams	= $this->rendered;
		if (isset($cacheParams['image']))
			unset($cacheParams['image']);

		return '/' . md5($this->imagePath . serialize($cacheParams));
	} // renderedCacheFilename()

	/**
	 * @since 2.0
	 * @return string
	 */
	private function renderedCacheFilePath()
	{
		return SLIR_CACHE_DIR . '/rendered' . $this->renderedCacheFilename();
	} // renderedCacheFilePath()

	/**
	 * @since 2.0
	 * @return string
	 */
	private function requestCacheFilename()
	{
		return '/' . md5($_SERVER['HTTP_HOST'] . '/' . $_SERVER['REQUEST_URI']);
	} // requestCacheFilename()

	/**
	 * @since 2.0
	 * @return string
	 */
	private function requestCacheFilePath()
	{
		return SLIR_CACHE_DIR . '/request' . $this->requestCacheFilename();
	} // requestCacheFilePath()

	/**
	 * Write an image to the cache
	 *
	 * @since 2.0
	 * @param string $imageData
	 * @return boolean
	 */
	private function cache($imageData)
	{
		$imageData	= $this->cacheRendered($imageData);
		
		if (SLIR_USE_REQUEST_CACHE)
			return $this->cacheRequest($imageData, FALSE);
		else
			return $imageData;
	} // cache()

	/**
	 * Write an image to the cache based on the properties of the rendered image
	 *
	 * @since 2.0
	 * @param string $imageData
	 * @param boolean $copyEXIF
	 * @return string
	 */
	private function cacheRendered($imageData, $copyEXIF = TRUE)
	{
		return $this->cacheFile($this->renderedCacheFilePath(), $imageData, $copyEXIF);
	} // cacheRendered()

	/**
	 * Write an image to the cache based on the request URI
	 *
	 * @since 2.0
	 * @param string $imageData
	 * @param boolean $copyEXIF
	 * @return string
	 */
	private function cacheRequest($imageData, $copyEXIF = TRUE)
	{
		return $this->cacheFile($this->requestCacheFilePath(), $imageData, $copyEXIF, $this->renderedCacheFilePath());
	} // cacheRequest()

	/**
	 * Write an image to the cache based on the properties of the rendered image
	 *
	 * @since 2.0
	 * @param string $cacheFilePath
	 * @param string $imageData
	 * @param boolean $copyEXIF
	 * @param string $symlinkToPath
	 * @return string
	 */
	private function cacheFile($cacheFilePath, $imageData, $copyEXIF = TRUE, $symlinkToPath = NULL)
	{
		$this->initializeCache();
		
		// Try to create just a symlink to minimize disk space
		if ($symlinkToPath && @symlink($symlinkToPath, $cacheFilePath))
			return $imageData;

		// Create the file
		file_put_contents($cacheFilePath, $imageData);

		if (SLIR_COPY_EXIF && $copyEXIF && $this->isSourceJPEG())
		{
			// Copy IPTC data
			if (isset($this->source['iptc']))
			{
				$imageData	= $this->copyIPTC($cacheFilePath);
				file_put_contents($cacheFilePath, $imageData);
			} // if

			// Copy EXIF data
			$this->copyEXIF($cacheFilePath);
		} // if

		return $imageData;
	} // cacheFile()

	/**
	 * Copy the source image's EXIF information to the new file in the cache
	 *
	 * @since 2.0
	 * @uses PEL
	 * @param string $cacheFilePath
	 */
	private function copyEXIF($cacheFilePath)
	{
		require_once('./pel-0.9.1/PelJpeg.php');
		$jpeg	= new PelJpeg(SLIR_DOCUMENT_ROOT . $this->imagePath);
		$exif	= $jpeg->getExif();
		if ($exif)
		{
			$jpeg	= new PelJpeg($cacheFilePath);
			$jpeg->setExif($exif);
			file_put_contents($cacheFilePath, $jpeg->getBytes());
		} // if
	} // copyEXIF()

	/**
	 * Makes sure the cache directory exists, is readable, and is writable
	 *
	 * @since 2.0
	 * @return boolean
	 */
	private function initializeCache()
	{
		if ($this->isCacheInitialized)
			return TRUE;

		$this->initializeDirectory(SLIR_CACHE_DIR);
		$this->initializeDirectory(SLIR_CACHE_DIR . '/rendered', FALSE);
		$this->initializeDirectory(SLIR_CACHE_DIR . '/request', FALSE);

		$this->isCacheInitialized	= TRUE;
		return TRUE;
	} // initializeCache()

	/**
	 * @since 2.0
	 * @param string $path Directory to initialize
	 * @param boolean $verifyReadWriteability
	 * @return boolean
	 */
	private function initializeDirectory($path, $verifyReadWriteability = TRUE, $test = FALSE)
	{
		if (!file_exists($path))
			mkdir($path, 0755, TRUE);

		if (!$verifyReadWriteability)
			return TRUE;

		// Make sure we can read and write the cache directory
		if (!is_readable($path))
		{
			header('HTTP/1.1 500 Internal Server Error');
			throw new SLIRException("Directory ($path) is not readable");
		}
		else if (!is_writable($path))
		{
			header('HTTP/1.1 500 Internal Server Error');
			throw new SLIRException("Directory ($path) is not writable");
		}

		return TRUE;
	} // initializeDirectory()

	/**
	 * Serves the unmodified source image
	 *
	 * @since 2.0
	 */
	private function serveSourceImage()
	{
		$this->serveFile(
			SLIR_DOCUMENT_ROOT . $this->imagePath,
			NULL,
			NULL,
			NULL,
			$this->source['mime'],
			'source'
		);
		
		exit();
	} // serveSourceImage()

	/**
	 * Serves the image from the cache based on the properties of the rendered
	 * image
	 *
	 * @since 2.0
	 */
	private function serveRenderedCachedImage()
	{
		return $this->serveCachedImage($this->renderedCacheFilePath(), 'rendered');
	} // serveRenderedCachedImage()

	/**
	 * Serves the image from the cache based on the request URI
	 *
	 * @since 2.0
	 */
	private function serveRequestCachedImage()
	{
		return $this->serveCachedImage($this->requestCacheFilePath(), 'request');
	} // serveRequestCachedImage()

	/**
	 * Serves the image from the cache
	 *
	 * @since 2.0
	 * @param string $cacheFilePath
	 * @param string $cacheType Can be 'request' or 'image'
	 */
	private function serveCachedImage($cacheFilePath, $cacheType)
	{
		// Serve the image
		$data = $this->serveFile(
			$cacheFilePath,
			NULL,
			NULL,
			NULL,
			$this->rendered['mime'],
			"$cacheType cache"
		);
		
		// If we are serving from the rendered cache, create a symlink in the
		// request cache to the rendered file
		if ($cacheType != 'request')
			$this->cacheRequest($data, FALSE);
		
		exit();
	} // serveCachedImage()

	/**
	 * Serves the rendered image
	 *
	 * @since 2.0
	 */
	private function serveRenderedImage()
	{
		// Put the data of the resized image into a variable
		ob_start();
		$this->rendered['functions']['output'](
			$this->rendered['image'],
			NULL,
			$this->rendered['quality']
		);
		$imageData	= ob_get_contents();
		ob_end_clean();

		// Cache the image
		$imageData	= $this->cache($imageData);

		// Serve the file
		$this->serveFile(
			NULL,
			$imageData,
			gmdate('U'),
			strlen($imageData),
			$this->rendered['mime'],
			'rendered'
		);

		// Clean up memory
		ImageDestroy($this->source['image']);
		ImageDestroy($this->rendered['image']);

		exit();
	} // serveRenderedImage()

	/**
	 * Serves a file
	 *
	 * @since 2.0
	 * @param string $imagePath Path to file to serve
	 * @param string $data Data of file to serve
	 * @param integer $lastModified Timestamp of when the file was last modified
	 * @param string $mimeType
	 * @param string $SLIRheader
	 * @return string Image data
	 */
	private function serveFile($imagePath, $data, $lastModified, $length, $mimeType, $SLIRHeader)
	{
		if ($imagePath != NULL)
		{
			if ($lastModified == NULL)
				$lastModified	= filemtime($imagePath);
			if ($length == NULL)
				$length			= filesize($imagePath);
		}
		else if ($length == NULL)
		{
			$length		= strlen($data);
		} // if
		
		// Serve the headers
		$this->serveHeaders(
			$this->lastModified($lastModified),
			$mimeType,
			$length,
			$SLIRHeader
		);
		
		// Read the image data into memory if we need to
		if ($data == NULL)
			$data	= file_get_contents($imagePath);

		// Send the image to the browser in bite-sized chunks
		$chunkSize	= 1024 * 8;
		$fp			= fopen('php://memory', 'r+b');
		fwrite($fp, $data);
		rewind($fp);
		while (!feof($fp))
		{
			echo fread($fp, $chunkSize);
			flush();
		} // while
		fclose($fp);
		
		return $data;
	} // serveFile()

	/**
	 * Serves headers for file for optimal browser caching
	 *
	 * @since 2.0
	 * @param string $lastModified Time when file was last modified in 'D, d M Y H:i:s' format
	 * @param string $mimeType
	 * @param integer $fileSize
	 * @param string $SLIRHeader
	 */
	private function serveHeaders($lastModified, $mimeType, $fileSize, $SLIRHeader)
	{
		header("Last-Modified: $lastModified");
		header("Content-Type: $mimeType");
		header("Content-Length: $fileSize");

		// Lets us easily know whether the image was rendered from scratch,
		// from the cache, or served directly from the source image
		header("Content-SLIR: $SLIRHeader");

		// Keep in browser cache how long?
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + SLIR_BROWSER_CACHE_EXPIRES_AFTER_SECONDS) . ' GMT');

		// Public in the Cache-Control lets proxies know that it is okay to
		// cache this content. If this is being served over HTTPS, there may be
		// sensitive content and therefore should probably not be cached by
		// proxy servers.
		header('Cache-Control: max-age=' . SLIR_BROWSER_CACHE_EXPIRES_AFTER_SECONDS . ', public');

		$this->doConditionalGet($lastModified);

		// The "Connection: close" header allows us to serve the file and let
		// the browser finish processing the script so we can do extra work
		// without making the user wait. This header must come last or the file
		// size will not properly work for images in the browser's cache
		header('Connection: close');
	} // serveHeaders()

	/**
	 * Converts a UNIX timestamp into the format needed for the Last-Modified
	 * header
	 *
	 * @since 2.0
	 * @param integer $timestamp
	 * @return string
	 */
	private function lastModified($timestamp)
	{
		return gmdate('D, d M Y H:i:s', $timestamp) . ' GMT';
	} // lastModified()

	/**
	 * Checks the to see if the file is different than the browser's cache
	 *
	 * @since 2.0
	 * @param string $lastModified
	 */
	private function doConditionalGet($lastModified)
	{
		$ifModifiedSince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) ?
			stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) :
			FALSE;

		if (!$ifModifiedSince || $ifModifiedSince != $lastModified)
			return;

		// Nothing has changed since their last request - serve a 304 and exit
		header('HTTP/1.1 304 Not Modified');

		// Serve a "Connection: close" header here in case there are any
		// shutdown functions that have been registered with
		// register_shutdown_function()
		header('Connection: close');

		exit();
	} // doConditionalGet()

} // class SLIR

// old pond
// a frog jumps
// the sound of water

// —Matsuo Basho
?>