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
 * @copyright Copyright © 2011, Joe Lencioni
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 * @since 2.0
 * @package SLIR
 */

/**
 * SLIR (Smart Lencioni Image Resizer)
 * Resizes images, intelligently sharpens, crops based on width:height ratios,
 * color fills transparent GIFs and PNGs, and caches variations for optimal
 * performance.
 *
 * I love to hear when my work is being used, so if you decide to use this,
 * feel encouraged to send me an email. I would appreciate it if you would
 * include a link on your site back to Shifting Pixel (either the SLIR page or
 * shiftingpixel.com), but don?t worry about including a big link on each page
 * if you don?t want to?one will do just nicely. Feel free to contact me to
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
 * Note: filenames that include special characters must be URL-encoded (e.g.
 * plus sign, +, should be encoded as %2B) in order for SLIR to recognize them
 * properly. This can be accomplished by passing your filenames through PHP's
 * rawurlencode() or urlencode() function.
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
 * <code><img src="/slir/?w=100&amp;h=100&amp;c=1:1&amp;i=/path/to/image.jpg"
 * alt="Don't forget your alt text" /></code>
 *
 * @author Joe Lencioni <joe@shiftingpixel.com>
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
 * @todo Remote image fetching?
 * @todo Alternative support for ImageMagick?
 * @todo Prevent files in cache from being read directly?
 * @todo split directory initialization into a separate
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
  const VERSION = '2.0b4';

  /**
   * @since 2.0
   * @var string
   */
  const CROP_CLASS_CENTERED = 'centered';

  /**
   * @since 2.0
   * @var string
   */
  const CROP_CLASS_TOP_CENTERED = 'topcentered';

  /**
   * @since 2.0
   * @var string
   */
  const CROP_CLASS_SMART    = 'smart';

  /**
   * Setting for the garbage collector to sleep for a second after looking at this many files
   *
   * @since 2.0
   * @var integer
   */
  const GARBAGE_COLLECTOR_BREATHE_EVERY = 5000;

  /**
   * @var string
   * @since 2.0
   */
  const CONFIG_FILENAME     = 'slirconfig.class.php';

  /**
   * Request object
   *
   * @since 2.0
   * @uses SLIRRequest
   * @var object
   */
  private $request;

  /**
   * Source image object
   *
   * @since 2.0
   * @uses SLIRImage
   * @var object
   */
  private $source;

  /**
   * Rendered image object
   *
   * @since 2.0
   * @uses SLIRImage
   * @var object
   */
  private $rendered;

  /**
   * Whether or not the cache has already been initialized
   *
   * @since 2.0
   * @var boolean
   */
  private $isCacheInitialized = false;

  /**
   * The magic starts here
   *
   * @since 2.0
   */
  final public function __construct()
  {
  }

  /**
   * Destructor method. Try to clean up memory a little.
   *
   * @return void
   * @since 2.0
   */
  final public function __destruct()
  {
    unset(
        $this->request,
        $this->source,
        $this->rendered
    );
  }

  /**
   * Processes the SLIR request from the parameters passed through the URL
   *
   * @since 2.0
   */
  public function processRequestFromURL()
  {
    // This helps prevent unnecessary warnings (which messes up images)
    // on servers that are set to display E_STRICT errors.
    $this->disableStrictErrorReporting();

    // Prevents ob_start('ob_gzhandler') in auto_prepend files from messing
    // up SLIR's output.
    $this->escapeOutputBuffering();

    $this->getConfig();

    // Set up our exception and error handler after the request cache to help keep
    // everything humming along nicely
    require 'slirexceptionhandler.class.php';

    $this->initializeGarbageCollection();

    require 'slirrequest.class.php';
    $this->request  = new SLIRRequest();

    // Check the cache based on the request URI
    if ($this->shouldUseRequestCache() && $this->isRequestCached()) {
      $this->serveRequestCachedImage();
    }

    require 'slirimage.class.php';
    // Set all parameters for resizing
    $this->setParameters();

    // See if there is anything we actually need to do
    if ($this->isSourceImageDesired()) {
      $this->serveSourceImage();
    }

    // Determine rendered dimensions
    $this->setRenderedProperties();

    // Check the cache based on the properties of the rendered image
    if (!$this->isRenderedCached() || !$this->serveRenderedCachedImage()) {
      // Image is not cached in any way, so we need to render the image,
      // cache it, and serve it up to the client
      $this->render();
      $this->serveRenderedImage();
    } // if
  }

  /**
   * Checks to see if the request cache should be used
   *
   * @since 2.0
   * @return boolean
   */
  private function shouldUseRequestCache()
  {
    // The request cache can't be used if the request is falling back to the
    // default image path because it will prevent the actual image from being
    // shown if it eventually ends up on the server
    if (SLIRConfig::$enableRequestCache === true && !$this->request->isUsingDefaultImagePath()) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Disables E_STRICT and E_NOTICE error reporting
   *
   * @since 2.0
   * @return integer
   */
  private function disableStrictErrorReporting()
  {
    return error_reporting(error_reporting() & ~E_STRICT & ~E_NOTICE);
  }

  /**
   * Escapes from output buffering.
   *
   * @since 2.0
   * @return void
   */
  final public function escapeOutputBuffering()
  {
    while ($level = ob_get_level()) {
      ob_end_clean();

      if ($level == ob_get_level()) {
        // On some setups, ob_get_level() will return a 1 instead of a 0 when there are no more buffers
        return;
      }
    }
  }

  /**
   * Determines if the garbage collector should run for this request.
   *
   * @since 2.0
   * @return boolean
   */
  private function garbageCollectionShouldRun()
  {
    if (rand(1, SLIRConfig::$garbageCollectDivisor) <= SLIRConfig::$garbageCollectProbability) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Checks to see if the garbage collector should be initialized, and if it should, initializes it.
   *
   * @since 2.0
   * @return void
   */
  private function initializeGarbageCollection()
  {
    if ($this->garbageCollectionShouldRun()) {
      // Register this as a shutdown function so the additional processing time
      // will not affect the speed of the request
      register_shutdown_function(array($this, 'collectGarbage'));
    }
  }

  /**
   * Deletes stale files from a directory.
   *
   * Used by the garbage collector to keep the cache directories from overflowing.
   *
   * @param string $path Directory to delete stale files from
   */
  private function deleteStaleFilesFromDirectory($path, $useAccessedTime = true)
  {
    $now  = time();
    $dir  = new DirectoryIterator($path);

    if ($useAccessedTime === true) {
      $function = 'getATime';
    } else {
      $function = 'getCTime';
    }

    foreach ($dir as $file) {
      // Every x files, stop for a second to help let other things on the server happen
      if ($file->key() % self::GARBAGE_COLLECTOR_BREATHE_EVERY == 0) {
        sleep(1);
      }

      // If the file is a link and not readable, the file it was pointing at has probably
      // been deleted, so we need to delete the link.
      // Otherwise, if the file is older than the max lifetime specified in the config, it is
      // stale and should be deleted.
      if (!$file->isDot() && (($file->isLink() && !$file->isReadable()) || ($now - $file->$function()) > SLIRConfig::$garbageCollectFileCacheMaxLifetime)) {
        unlink($file->getPathName());
      }
    }
  }


  /**
   * Checks to see if the garbage collector is currently running.
   *
   * @since 2.0
   * @return boolean
   */
  private function garbageCollectorIsRunning()
  {
    if (file_exists(SLIRConfig::$pathToCacheDir . '/garbageCollector.tmp') && filemtime(SLIRConfig::$pathToCacheDir . '/garbageCollector.tmp') > time() - 86400) {
      // If the file is more than 1 day old, something probably went wrong and we should run again anyway
      return true;
    } else {
      return false;
    }
  }

  /**
   * Writes a file to the cache to use as a signal that the garbage collector is currently running.
   *
   * @since 2.0
   * @return void
   */
  private function startGarbageCollection()
  {
    error_log(sprintf("\n[%s] Garbage collection started", @gmdate('D M d H:i:s Y')), 3, SLIRConfig::$pathToErrorLog);

    // Shut down the connection so the user can go about his or her business
    header("Connection: close");
    ignore_user_abort(true);
    flush();

    // Create the file that tells SLIR that the garbage collector is currently running and doesn't need to run again right now.
    touch(SLIRConfig::$pathToCacheDir . '/garbageCollector.tmp');
  }

  /**
   * Removes the file that signifies that the garbage collector is currently running.
   *
   * @since 2.0
   * @param boolean $successful
   * @return void
   */
  private function finishGarbageCollection($successful = true)
  {
    // Delete the file that tells SLIR that the garbage collector is running
    unlink(SLIRConfig::$pathToCacheDir . '/garbageCollector.tmp');

    if ($successful) {
      error_log(sprintf("\n[%s] Garbage collection completed", @gmdate('D M d H:i:s Y')), 3, SLIRConfig::$pathToErrorLog);
    }
  }

  /**
   * Garbage collector
   *
   * Clears out old files from the cache
   *
   * @since 2.0
   * @return void
   */
  public function collectGarbage()
  {
    // This code needs to be in a try/catch block to prevent the epically unhelpful
    // "PHP Fatal error:  Exception thrown without a stack frame in Unknown on line
    // 0" from showing up in the error log.
    try {
      if ($this->garbageCollectorIsRunning()) {
        return;
      }

      $this->startGarbageCollection();
      $this->deleteStaleFilesFromDirectory($this->getRequestCacheDir(), false);
      $this->deleteStaleFilesFromDirectory($this->getRenderedCacheDir());
      $this->finishGarbageCollection();
    } catch (Exception $e) {
      error_log(sprintf("\n[%s] %s thrown within the SLIR garbage collector. Message: %s in %s on line %d", @gmdate('D M d H:i:s Y'), get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()), 3, SLIRConfig::$pathToErrorLog);
      error_log("\nException trace stack: " . print_r($e->getTrace(), true), 3, SLIRConfig::$pathToErrorLog);
      $this->finishGarbageCollection(false);
    }
  }

  /**
   * Includes the configuration file.
   *
   * If the configuration file cannot be included, this will throw an error that will hopefully explain what needs to be done.
   *
   * @since 2.0
   * @return void
   */
  final public function getConfig()
  {
    require_once $this->getConfigPath();
  }

  /**
   * @since 2.0
   * @return string
   */
  final public function getConfigPath()
  {
    if (defined('SLIR_CONFIG_FILENAME')) {
      return SLIR_CONFIG_FILENAME;
    } else {
      return $this->resolveRelativePath('../' . self::CONFIG_FILENAME);
    }
  }

  /**
   * @param string $path
   * @return string
   * @since 2.0
   */
  final public function resolveRelativePath($path)
  {
    $path = __DIR__ . '/' . $path;

    while (strstr($path, '../')) {
      $path = preg_replace('/\w+\/\.\.\//', '', $path);
    }

    return $path;
  }

  /**
   * Sets up parameters for image resizing
   *
   * @since 2.0
   * @return void
   */
  private function setParameters()
  {
    $this->source   = new SLIRImage();
    $this->source->path = $this->request->path;

    // If either a max width or max height are not specified or larger than
    // the source image we default to the dimension of the source image so
    // they do not become constraints on our resized image.
    if (!$this->request->width || $this->request->width > $this->source->width) {
      $this->request->width = $this->source->width;
    }

    if (!$this->request->height ||  $this->request->height > $this->source->height) {
      $this->request->height  = $this->source->height;
    }
  }

  /**
   * Allocates memory for the request.
   *
   * Tries to dynamically guess how much memory will be needed for the request based on the dimensions of the source image.
   *
   * @since 2.0
   * @return void
   */
  private function allocateMemory()
  {
    // Multiply width * height * 8 bytes
    $estimatedMemory = $this->source->width * $this->source->height * 8;

    // Convert memory to Megabytes and add 15 in order to allow some slack
    $estimatedMemory = round(($estimatedMemory / 1024) / 1024, 0) + 15;

    $v = ini_set('memory_limit', min($estimatedMemory, SLIRConfig::$maxMemoryToAllocate) . 'M');
  }

  /**
   * Renders requested changes to the image
   *
   * @since 2.0
   * @return void
   */
  private function render()
  {
    $this->allocateMemory();

    // Allows some funky JPEGs to work instead of breaking everything
    ini_set('gd.jpeg_ignore_warning', '1');

    $this->source->createImageFromFile();

    $this->rendered->createBlankImage();
    $this->rendered->background($this->isBackgroundFillOn());

    $this->copySourceToRendered();
    $this->rendered->setPath($this->source->path, false);
    $this->source->destroyImage();

    $this->rendered->crop($this->isBackgroundFillOn());
    $this->rendered->sharpen($this->calculateSharpnessFactor());
    $this->rendered->interlace();
    $this->rendered->optimize();
  }

  /**
   * Copies the source image to the rendered image, resizing (resampling) it if resizing is requested
   *
   * @since 2.0
   * @return void
   */
  private function copySourceToRendered()
  {
    // Resample the original image into the resized canvas we set up earlier
    if ($this->source->width != $this->rendered->width || $this->source->height != $this->rendered->height) {
      imagecopyresampled(
          $this->rendered->image,
          $this->source->image,
          0,
          0,
          0,
          0,
          $this->rendered->width,
          $this->rendered->height,
          $this->source->width,
          $this->source->height
      );
    } else {
      // No resizing is needed, so make a clean copy
      imagecopy(
          $this->rendered->image,
          $this->source->image,
          0,
          0,
          0,
          0,
          $this->source->width,
          $this->source->height
      );
    } // if
  }

  /**
   * Calculates how much to sharpen the image based on the difference in dimensions of the source image and the rendered image
   *
   * @since 2.0
   * @return integer Sharpness factor
   */
  private function calculateSharpnessFactor()
  {
    return $this->calculateASharpnessFactor($this->source->area(), $this->rendered->area());
  }

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
  private function calculateASharpnessFactor($sourceArea, $destinationArea)
  {
    $final  = sqrt($destinationArea) * (750.0 / sqrt($sourceArea));
    $a      = 52;
    $b      = -0.27810650887573124;
    $c      = .00047337278106508946;

    $result = $a + $b * $final + $c * $final * $final;

    return max(round($result), 0);
  }

  /**
   * Copies IPTC data from the source image to the cached file
   *
   * @since 2.0
   * @param string $cacheFilePath
   * @return boolean
   */
  private function copyIPTC($cacheFilePath)
  {
    $data = '';

    $iptc = $this->source->iptc;

    // Originating program
    $iptc['2#065']  = array('Smart Lencioni Image Resizer');

    // Program version
    $iptc['2#070']  = array(SLIR::VERSION);

    foreach ($iptc as $tag => $iptcData) {
      $tag  = substr($tag, 2);
      $data .= $this->makeIPTCTag(2, $tag, $iptcData[0]);
    }

    // Embed the IPTC data
    return iptcembed($data, $cacheFilePath);
  }

  /**
   * @since 2.0
   * @author Thies C. Arntzen
   */
  private function makeIPTCTag($rec, $data, $value)
  {
    $length = strlen($value);
    $retval = chr(0x1C) . chr($rec) . chr($data);

    if ($length < 0x8000) {
      $retval .= chr($length >> 8) .  chr($length & 0xFF);
    } else {
      $retval .= chr(0x80) .
       chr(0x04) .
       chr(($length >> 24) & 0xFF) .
       chr(($length >> 16) & 0xFF) .
       chr(($length >> 8) & 0xFF) .
       chr($length & 0xFF);
    }

    return $retval . $value;
  }

  /**
   * Checks parameters against the image's attributes and determines whether
   * anything needs to be changed or if we simply need to serve up the source
   * image
   *
   * @since 2.0
   * @return boolean
   * @todo Add check for JPEGs and progressiveness
   */
  private function isSourceImageDesired()
  {
    if ($this->isWidthDifferent() || $this->isHeightDifferent() || $this->isBackgroundFillOn() || $this->isQualityOn() || $this->isCroppingNeeded()) {
      return false;
    } else {
      return true;
    }
  }

  /**
   * Determines if the requested width is different than the width of the source image
   *
   * @since 2.0
   * @return boolean
   */
  private function isWidthDifferent()
  {
    if ($this->request->width !== null && $this->request->width < $this->source->width) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Determines if the requested height is different than the height of the source image
   *
   * @since 2.0
   * @return boolean
   */
  private function isHeightDifferent()
  {
    if ($this->request->height !== null && $this->request->height < $this->source->height) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Determines if a background fill has been requested and if the image is able to have transparency (not for JPEG files)
   *
   * @since 2.0
   * @return boolean
   */
  private function isBackgroundFillOn()
  {
    if ($this->request->isBackground() && $this->source->isAbleToHaveTransparency()) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Determines if the user included image quality in the request
   *
   * @since 2.0
   * @return boolean
   */
  private function isQualityOn()
  {
    return $this->request->isQuality();
  }

  /**
   * Determines if the image should be cropped based on the requested crop ratio and the width:height ratio of the source image
   *
   * @since 2.0
   * @return boolean
   */
  private function isCroppingNeeded()
  {
    if ($this->request->isCropping() && $this->request->cropRatio['ratio'] != $this->source->ratio()) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Computes and sets properties of the rendered image, such as the actual
   * width, height, and quality
   *
   * @since 2.0
   */
  private function setRenderedProperties()
  {
    $this->rendered = new SLIRImage();

    // Set default properties of the rendered image
    $this->rendered->path   = $this->source->path;
    $this->rendered->width  = $this->source->width;
    $this->rendered->height = $this->source->height;

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
    if ($this->isCroppingNeeded()) {
      // Determine the dimensions of the source image after cropping and
      // before resizing

      if ($this->request->cropRatio['ratio'] > $this->source->ratio()) {
        // Image is too tall so we will crop the top and bottom
        $this->source->cropHeight = $this->source->width / $this->request->cropRatio['ratio'];
        $this->source->cropWidth  = $this->source->width;
      } else {
        // Image is too wide so we will crop off the left and right sides
        $this->source->cropWidth  = $this->source->height * $this->request->cropRatio['ratio'];
        $this->source->cropHeight = $this->source->height;
      } // if

      $this->source->cropper    = $this->request->cropper;
      $this->rendered->cropper  = $this->source->cropper;
    } // if

    if ($this->shouldResizeBasedOnWidth()) {
      $resizeFactor = $this->resizeWidthFactor();
      $this->rendered->height = round($resizeFactor * $this->source->height);
      $this->rendered->width  = round($resizeFactor * $this->source->width);

      // Determine dimensions after cropping
      if ($this->isCroppingNeeded()) {
        $this->rendered->cropHeight = round($resizeFactor * $this->source->cropHeight);
        $this->rendered->cropWidth  = round($resizeFactor * $this->source->cropWidth);
      } // if
    } else if ($this->shouldResizeBasedOnHeight()) {
      $resizeFactor = $this->resizeHeightFactor();
      $this->rendered->width  = round($resizeFactor * $this->source->width);
      $this->rendered->height = round($resizeFactor * $this->source->height);

      // Determine dimensions after cropping
      if ($this->isCroppingNeeded()) {
        $this->rendered->cropHeight = round($resizeFactor * $this->source->cropHeight);
        $this->rendered->cropWidth  = round($resizeFactor * $this->source->cropWidth);
      } // if
    } else if ($this->isCroppingNeeded()) {
      // No resizing is needed but we still need to crop
      $ratio  = ($this->resizeUncroppedWidthFactor() > $this->resizeUncroppedHeightFactor())
        ? $this->resizeUncroppedWidthFactor() : $this->resizeUncroppedHeightFactor();

      $this->rendered->width    = round($ratio * $this->source->width);
      $this->rendered->height   = round($ratio * $this->source->height);

      $this->rendered->cropWidth  = round($ratio * $this->source->cropWidth);
      $this->rendered->cropHeight = round($ratio * $this->source->cropHeight);
    } // if

    // Determine the quality of the output image
    $this->rendered->quality    = ($this->request->quality !== null)
      ? $this->request->quality : SLIRConfig::$defaultQuality;

    // Set up the appropriate image handling parameters based on the original
    // image's mime type
    // @todo some of this code should be moved to the SLIRImage class
    $this->rendered->mime       = $this->source->mime;
    if ($this->source->isJPEG()) {
      $this->rendered->progressive  = ($this->request->progressive !== null)
        ? $this->request->progressive : SLIRConfig::$defaultProgressiveJPEG;
      $this->rendered->background   = null;
    } else if ($this->source->isPNG()) {
      $this->rendered->progressive  = false;
    } else if ($this->source->isGIF() || $this->source->isBMP()) {
      // We convert GIFs and BMPs to PNGs
      $this->rendered->mime     = 'image/png';
      $this->rendered->progressive  = false;
    } else {
      throw new RuntimeException("Unable to determine type of source image ({$this->source->mime})");
    } // if

    if ($this->isBackgroundFillOn()) {
      $this->rendered->background = $this->request->background;
    }
  }

  /**
   * Detemrines if the image should be resized based on its width (i.e. the width is the constraining dimension for this request)
   *
   * @since 2.0
   * @return boolean
   */
  private function shouldResizeBasedOnWidth()
  {
    if (floor($this->resizeWidthFactor() * $this->source->height) <= $this->request->height) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Detemrines if the image should be resized based on its height (i.e. the height is the constraining dimension for this request)
   * @since 2.0
   * @return boolean
   */
  private function shouldResizeBasedOnHeight()
  {
    if (floor($this->resizeHeightFactor() * $this->source->width) <= $this->request->width) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * @since 2.0
   * @return float
   */
  private function resizeWidthFactor()
  {
    if ($this->source->cropWidth !== null) {
      return $this->resizeCroppedWidthFactor();
    } else {
      return $this->resizeUncroppedWidthFactor();
    }
  }

  /**
   * @since 2.0
   * @return float
   */
  private function resizeUncroppedWidthFactor()
  {
    return $this->request->width / $this->source->width;
  }

  /**
   * @since 2.0
   * @return float
   */
  private function resizeCroppedWidthFactor()
  {
    return $this->request->width / $this->source->cropWidth;
  }

  /**
   * @since 2.0
   * @return float
   */
  private function resizeHeightFactor()
  {
    if ($this->source->cropHeight !== null) {
      return $this->resizeCroppedHeightFactor();
    } else {
      return $this->resizeUncroppedHeightFactor();
    }
  }

  /**
   * @since 2.0
   * @return float
   */
  private function resizeUncroppedHeightFactor()
  {
    return $this->request->height / $this->source->height;
  }

  /**
   * @since 2.0
   * @return float
   */
  private function resizeCroppedHeightFactor()
  {
    return $this->request->height / $this->source->cropHeight;
  }

  /**
   * Determines if the rendered file is in the rendered cache
   *
   * @since 2.0
   * @return boolean
   */
  private function isRenderedCached()
  {
    return $this->isCached($this->renderedCacheFilePath());
  }

  /**
   * Determines if the request is symlinked to the rendered file
   *
   * @since 2.0
   * @return boolean
   */
  private function isRequestCached()
  {
    return $this->isCached($this->requestCacheFilePath());
  }

  /**
   * Determines if a given file exists in the cache
   *
   * @since 2.0
   * @param string $cacheFilePath
   * @return boolean
   */
  private function isCached($cacheFilePath)
  {
    if (!file_exists($cacheFilePath)) {
      return false;
    }

    $cacheModified  = filemtime($cacheFilePath);

    if (!$cacheModified) {
      return false;
    }

    $imageModified  = filectime($this->request->fullPath());

    if ($imageModified >= $cacheModified) {
      return false;
    } else {
      return true;
    }
  }

  /**
   * @since 2.0
   * @return string
   */
  private function getRenderedCacheDir()
  {
    return SLIRConfig::$pathToCacheDir . '/rendered';
  }

  /**
   * @since 2.0
   * @return string
   */
  private function renderedCacheFilePath()
  {
    return $this->getRenderedCacheDir() . $this->renderedCacheFilename();
  }

  /**
   * @since 2.0
   * @return string
   */
  private function renderedCacheFilename()
  {
    return '/' . hash('md4', $this->request->fullPath() . serialize($this->rendered->cacheParameters()));
  }

  /**
   * @since 2.0
   * @return string
   */
  private function requestCacheFilename()
  {
    return '/' . hash('md4', $_SERVER['HTTP_HOST'] . '/' . $this->requestURI() . '/' . SLIRConfig::$defaultCropper);
  }

  /**
   * @since 2.0
   * @return string
   */
  private function requestURI()
  {
    if (SLIRConfig::$forceQueryString === true) {
      return $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($_GET);
    } else {
      return $_SERVER['REQUEST_URI'];
    }
  }

  /**
   * @since 2.0
   * @return string
   */
  private function getRequestCacheDir()
  {
    return SLIRConfig::$pathToCacheDir . '/request';
  }

  /**
   * @since 2.0
   * @return string
   */
  private function requestCacheFilePath()
  {
    return $this->getRequestCacheDir() . $this->requestCacheFilename();
  }

  /**
   * Write an image to the cache
   *
   * @since 2.0
   * @param string $imageData
   * @return boolean
   */
  private function cache()
  {
    $this->cacheRendered();

    if ($this->shouldUseRequestCache()) {
      return $this->cacheRequest($this->rendered->data, true);
    } else {
      return true;
    }
  }

  /**
   * Write an image to the cache based on the properties of the rendered image
   *
   * @since 2.0
   * @return boolean
   */
  private function cacheRendered()
  {
    $this->rendered->data = $this->cacheFile(
        $this->renderedCacheFilePath(),
        $this->rendered->data,
        true
    );

    return true;
  }

  /**
   * Write an image to the cache based on the request URI
   *
   * @since 2.0
   * @param string $imageData
   * @param boolean $copyEXIF
   * @return string
   */
  private function cacheRequest($imageData, $copyEXIF = true)
  {
    return $this->cacheFile(
        $this->requestCacheFilePath(),
        $imageData,
        $copyEXIF,
        $this->renderedCacheFilePath()
    );
  }

  /**
   * Write an image to the cache based on the properties of the rendered image
   *
   * @since 2.0
   * @param string $cacheFilePath
   * @param string $imageData
   * @param boolean $copyEXIF
   * @param string $symlinkToPath
   * @return string|boolean
   */
  private function cacheFile($cacheFilePath, $imageData, $copyEXIF = true, $symlinkToPath = null)
  {
    $this->initializeCache();

    // Try to create just a symlink to minimize disk space
    if ($symlinkToPath && function_exists('symlink') && (file_exists($cacheFilePath) || symlink($symlinkToPath, $cacheFilePath))) {
      return true;
    }

    // Create the file
    if (!file_put_contents($cacheFilePath, $imageData)) {
      return false;
    }

    if (SLIRConfig::$copyEXIF == true && $copyEXIF && $this->source->isJPEG()) {
      // Copy IPTC data
      if (isset($this->source->iptc) && !$this->copyIPTC($cacheFilePath)) {
        return false;
      }

      // Copy EXIF data
      $imageData  = $this->copyEXIF($cacheFilePath);
    } // if

    return $imageData;
  }

  /**
   * Copy the source image's EXIF information to the new file in the cache
   *
   * @since 2.0
   * @uses PEL
   * @param string $cacheFilePath
   * @return mixed string contents of image on success, false on failure
   */
  private function copyEXIF($cacheFilePath)
  {
    // Make sure to suppress strict warning thrown by PEL
    @require_once dirname(__FILE__) . '/pel-0.9.2/src/PelJpeg.php';

    $jpeg   = new PelJpeg($this->source->fullPath());
    $exif   = $jpeg->getExif();

    if ($exif) {
      $jpeg   = new PelJpeg($cacheFilePath);
      $jpeg->setExif($exif);
      $imageData  = $jpeg->getBytes();
      if (!file_put_contents($cacheFilePath, $imageData)) {
        return false;
      }

      return $imageData;
    } // if

    return file_get_contents($cacheFilePath);
  }

  /**
   * Makes sure the cache directory exists, is readable, and is writable
   *
   * @since 2.0
   * @return boolean
   */
  private function initializeCache()
  {
    if ($this->isCacheInitialized) {
      return true;
    }

    $this->initializeDirectory(SLIRConfig::$pathToCacheDir);
    $this->initializeDirectory(SLIRConfig::$pathToCacheDir . '/rendered', false);
    $this->initializeDirectory(SLIRConfig::$pathToCacheDir . '/request', false);

    $this->isCacheInitialized = true;
    return true;
  }

  /**
   * @since 2.0
   * @param string $path Directory to initialize
   * @param boolean $verifyReadWriteability
   * @return boolean
   */
  private function initializeDirectory($path, $verifyReadWriteability = true, $test = false)
  {
    if (!file_exists($path)) {
      if (!@mkdir($path, 0755, true)) {
        header('HTTP/1.1 500 Internal Server Error');
        throw new RuntimeException("Directory ($path) does not exist and was unable to be created. Please create the directory.");
      }
    }

    if (!$verifyReadWriteability) {
      return true;
    }

    // Make sure we can read and write the cache directory
    if (!is_readable($path)) {
      header('HTTP/1.1 500 Internal Server Error');
      throw new RuntimeException("Directory ($path) is not readable");
    } else if (!is_writable($path)) {
      header('HTTP/1.1 500 Internal Server Error');
      throw new RuntimeException("Directory ($path) is not writable");
    }

    return true;
  }

  /**
   * Serves the unmodified source image
   *
   * @since 2.0
   * @return void
   */
  private function serveSourceImage()
  {
    $this->serveFile(
        $this->source->fullPath(),
        null,
        null,
        null,
        $this->source->mime,
        'source'
    );

    exit();
  }

  /**
   * Serves the image from the cache based on the properties of the rendered
   * image
   *
   * @since 2.0
   * @return void
   */
  private function serveRenderedCachedImage()
  {
    return $this->serveCachedImage($this->renderedCacheFilePath(), 'rendered');
  }

  /**
   * Serves the image from the cache based on the request URI
   *
   * @since 2.0
   * @return void
   */
  private function serveRequestCachedImage()
  {
    return $this->serveCachedImage($this->requestCacheFilePath(), 'request');
  }

  /**
   * Serves the image from the cache
   *
   * @since 2.0
   * @param string $cacheFilePath
   * @param string $cacheType Can be 'request' or 'image'
   * @return void
   */
  private function serveCachedImage($cacheFilePath, $cacheType)
  {
    // Serve the image
    $this->serveFile(
        $cacheFilePath,
        null,
        null,
        null,
        null,
        "$cacheType cache"
    );

    // If we are serving from the rendered cache, create a symlink in the
    // request cache to the rendered file
    if ($cacheType != 'request') {
      $this->cacheRequest(file_get_contents($cacheFilePath), false);
    }

    exit();
  }

  /**
   * Determines the mime type of an image
   *
   * @since 2.0
   * @param string $path
   * @return string
   */
  private function mimeType($path)
  {
    $info = getimagesize($path);
    return $info['mime'];
  }

  /**
   * Serves the rendered image
   *
   * @since 2.0
   * @return void
   */
  private function serveRenderedImage()
  {
    // Cache the image
    $this->cache();

    // Serve the file
    $this->serveFile(
        null,
        $this->rendered->data,
        gmdate('U'),
        $this->rendered->fileSize(),
        $this->rendered->mime,
        'rendered'
    );

    // Clean up memory
    $this->rendered->destroyImage();

    exit();
  }

  /**
   * Serves a file
   *
   * @since 2.0
   * @param string $imagePath Path to file to serve
   * @param string $data Data of file to serve
   * @param integer $lastModified Timestamp of when the file was last modified
   * @param string $mimeType
   * @param string $slirHeader
   * @return void
   */
  private function serveFile($imagePath, $data, $lastModified, $length, $mimeType, $slirHeader)
  {
    if ($imagePath !== null) {
      if ($lastModified === null) {
        $lastModified = filemtime($imagePath);
      }
      if ($length === null) {
        $length     = filesize($imagePath);
      }
      if ($mimeType === null) {
        $mimeType   = $this->mimeType($imagePath);
      }
    } else if ($length === null) {
      $length   = strlen($data);
    } // if

    // Serve the headers
    $this->serveHeaders(
        $this->lastModified($lastModified),
        $mimeType,
        $length,
        $slirHeader
    );

    if ($data === null) {
      readfile($imagePath);
    } else {
      echo $data;
    }
  }

  /**
   * Serves headers for file for optimal browser caching
   *
   * @since 2.0
   * @param string $lastModified Time when file was last modified in 'D, d M Y H:i:s' format
   * @param string $mimeType
   * @param integer $fileSize
   * @param string $slirHeader
   * @return void
   */
  private function serveHeaders($lastModified, $mimeType, $fileSize, $slirHeader)
  {
    header("Last-Modified: $lastModified");
    header("Content-Type: $mimeType");
    header("Content-Length: $fileSize");

    // Lets us easily know whether the image was rendered from scratch,
    // from the cache, or served directly from the source image
    header("X-Content-SLIR: $slirHeader");

    // Keep in browser cache how long?
    header(sprintf('Expires: %s GMT', gmdate('D, d M Y H:i:s', time() + SLIRConfig::$browserCacheTTL)));

    // Public in the Cache-Control lets proxies know that it is okay to
    // cache this content. If this is being served over HTTPS, there may be
    // sensitive content and therefore should probably not be cached by
    // proxy servers.
    header(sprintf('Cache-Control: max-age=%d, public', SLIRConfig::$browserCacheTTL));

    $this->doConditionalGet($lastModified);

    // The "Connection: close" header allows us to serve the file and let
    // the browser finish processing the script so we can do extra work
    // without making the user wait. This header must come last or the file
    // size will not properly work for images in the browser's cache
    //header('Connection: close');
  }

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
  }

  /**
   * Checks the to see if the file is different than the browser's cache
   *
   * @since 2.0
   * @param string $lastModified
   * @return void
   */
  private function doConditionalGet($lastModified)
  {
    $ifModifiedSince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) ?
      stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) :
      false;

    if (!$ifModifiedSince || $ifModifiedSince != $lastModified) {
      return;
    }

    // Nothing has changed since their last request - serve a 304 and exit
    header('HTTP/1.1 304 Not Modified');

    exit();
  }

} // class SLIR

// old pond
// a frog jumps
// the sound of water

// —Matsuo Basho