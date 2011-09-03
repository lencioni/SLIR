<?php
/**
 * Class for working with the GD image library
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

 require_once '../slirimagelib.interface.php';
 require_once '../slirimagelib.class.php';

/**
 * Class for working with the GD image library
 * @package SLIR
 * @since 2.0
 */
class SLIRGDImage extends SLIRImageLibrary implements SLIRImageLibrary
{

  /**
   * @var string Path to image
   */
  private $path;

  /**
   * @var resource GD image resource
   */
  private $image;

  /**
   * @var array Information about the image (width, height, iptc)
   */
  private $info;

  /**
   * @param string $path
   * @return void
   * @since 2.0
   */
  public function __construct($path = null)
  {
    // Allows some funky JPEGs to work instead of breaking everything
    ini_set('gd.jpeg_ignore_warning', '1');

    if ($path !== null) {
      $this->path = $path;
    }
    return parent::__construct($path);
  }

  /**
   * @return void
   * @since 2.0
   */
  public function __destruct()
  {
    $this->destroy();
    unset(
      $this->path,
      $this->image,
      $this->info
    );
    return parent::__destruct();
  }

  /**
   * @param integer $fallbackWidth
   * @param integer $fallbackHeight
   * @return resource
   * @since 2.0
   */
  public function getImage($fallbackWidth = null, $fallbackHeight = null)
  {
    if ($this->image === null) {
      if ($this->getPath() === null) {
        $this->create($fallbackWidth, $fallbackHeight);
      } else {
        try {
          if ($this->isJPEG()) {
            $this->image  = imagecreatefromjpeg($this->getPath());
          } else if ($this->isGIF()) {
            $this->image  = imagecreatefromgif($this->getPath());
          } else if ($this->isPNG()) {
            $this->image  = imagecreatefrompng($this->getPath());
          } else if ($this->isBMP()) {
            $this->image  = $this->imagecreatefrombmp($this->getPath());
          }
        } catch (Exception $e) {
          // Try an alternate catch-all method
          $this->image  = imagecreatefromstring(file_get_contents($this->getPath()));
        }

        $this->info = null;
      }
    }

    return $this->image;
  }

  /**
   * @since 2.0
   * @param string $path path to BMP file
   * @return resource
   * @link http://us.php.net/manual/en/function.imagecreatefromwbmp.php#86214
   */
  public function imagecreatefrombmp($path)
  {
    // Load the image into a string
    $read = file_get_contents($path);

    $temp = unpack('H*', $read);
    $hex  = $temp[1];
    $header = substr($hex, 0, 108);

    // Process the header
    // Structure: http://www.fastgraph.com/help/bmp_header_format.html
    if (substr($header, 0, 4) == '424d') {
      // Get the width 4 bytes
      $width  = hexdec($header[38] . $header[39] . $header[36] . $header[37]);

      // Get the height 4 bytes
      $height = hexdec($header[46] . $header[47] . $header[44] . $header[45]);
    }

    // Define starting X and Y
    $x  = 0;
    $y  = 1;

    // Create newimage
    $image  = imagecreatetruecolor($width, $height);

    // Grab the body from the image
    $body = substr($hex, 108);

    // Calculate if padding at the end-line is needed
    // Divided by two to keep overview.
    // 1 byte = 2 HEX-chars
    $bodySize    = (strlen($body) / 2);
    $headerSize  = ($width * $height);

    // Use end-line padding? Only when needed
    $usePadding = ($bodySize > ($headerSize * 3) + 4);

    // Using a for-loop with index-calculation instaid of str_split to avoid large memory consumption
    // Calculate the next DWORD-position in the body
    for ($i = 0; $i < $bodySize; $i += 3) {
        // Calculate line-ending and padding
        if ($x >= $width) {
          // If padding needed, ignore image-padding
          // Shift i to the ending of the current 32-bit-block
          if ($usePadding) {
            $i += $width % 4;
          }

          // Reset horizontal position
          $x  = 0;

          // Raise the height-position (bottom-up)
          ++$y;

          // Reached the image-height? Break the for-loop
          if ($y > $height) {
            break;
          }
        }

      // Calculation of the RGB-pixel (defined as BGR in image-data)
      // Define $iPos as absolute position in the body
      $iPos = $i * 2;
      $r    = hexdec($body[$iPos + 4] . $body[$iPos + 5]);
      $g    = hexdec($body[$iPos + 2] . $body[$iPos + 3]);
      $b    = hexdec($body[$iPos] . $body[$iPos + 1]);

      // Calculate and draw the pixel
      $color  = imagecolorallocate($image, $r, $g, $b);
      imagesetpixel($image, $x, $height - $y, $color);

      // Raise the horizontal position
      ++$x;
    }

    // Unset the body / free the memory
    unset($body);

    // Return image-object
    return $image;
  }

  /**
   * @return string
   * @since 2.0
   */
  public function getPath()
  {
    return $this->path;
  }

  /**
   * Resamples the image into the destination image
   * @param SLIRGDImage $destination
   * @param integer $width
   * @param integer $height
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function resample(SLIRGDImage $destination, $width, $height)
  {
    imagecopyresampled(
        $destination->getImage(),
        $this->getImage(),
        0,
        0,
        0,
        0,
        $destination->getWidth(),
        $destination->getHeight(),
        $this->getWidth(),
        $this->getHeight()
    );

    return $this;
  }

  /**
   * Copies the image into the destination image without reszing
   * @param SLIRGDImage $destination
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function copy(SLIRGDImage $destination)
  {
    imagecopy(
        $destination->getImage(),
        $this->getImage(),
        0,
        0,
        0,
        0,
        $this->getWidth(),
        $this->getHeight()
    );

    return $this;
  }

  /**
   * Gets width, height, and iptc information from the image
   * @return array with keys of width, height, and iptc
   * @since 2.0
   */
  private function getInfo()
  {
    if ($this->info === null) {
      if ($this->getPath() === null) {
        // If there is no path, get the info from the image resource
        if ($this->getImage() === null) {
          // There is nothing to get
        } else {
          $this->info['width']  = imagesx($this->getImage());
          $this->info['width']  = imagesy($this->getImage());
          // @todo mime
        }
      } else {
        // There is a path, so get the info from the file
        $this->info = getimagesize($this->getPath(), $extraInfo);

        if ($this->info === false) {
          header('HTTP/1.1 400 Bad Request');
          throw new RuntimeException('getimagesize failed (source file may not be an image): ' . $this->fullPath());
        }

        $this->info['width']  =& $this->info[0];
        $this->info['height'] =& $this->info[1];

        // IPTC
        if (is_array($extraInfo) && isset($extraInfo['APP13'])) {
          $this->info['iptc'] = iptcparse($extraInfo['APP13']);
        }
      }
    }

    return $this->info;
  }

  /**
   * Gets the width of the image
   * @return integer
   * @since 2.0
   */
  public function getWidth()
  {
    $info = $this->getInfo();
    return (integer) $info['width'];
  }

  /**
   * Gets the height of the image
   * @return integer
   * @since 2.0
   */
  public function getHeight()
  {
    $info = $this->getInfo();
    return (integer) $info['height'];
  }

  /**
   * Gets the MIME type of the image
   * @return string
   * @since 2.0
   */
  public function getMimeType()
  {
    $info = $this->getInfo();
    return $info['mime'];
  }

  /**
   * Creates a new, blank image
   * @param integer $width
   * @param integer $height
   * @return SLIRImageLibrary
   */
  public function create($width, $height)
  {
    $this->image  = imagecreatetruecolor($width, $height);
    $this->info   = array(
      'width'   => $width,
      'height'  => $height,
    );
    return $this;
  }

  /**
   * Turns on the alpha channel to enable transparency in the image
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function enableTransparency()
  {
    imagealphablending($this->getImage(), false);
    imagesavealpha($this->getImage(), true);
    return $this;
  }

  /**
   * Fills the image with the given color
   * @param string $color in hex format
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function fill($color)
  {
    $background = imagecolorallocate(
        $image,
        hexdec($color[0].$color[1]),
        hexdec($color[2].$color[3]),
        hexdec($color[4].$color[5])
    );

    imagefilledrectangle($this->getImage(), 0, 0, $this->getWidth(), $this->getHeight(), $background);

    return $this;
  }

  /**
   * Turns interlacing on or off
   * @param boolean $interlace
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function interlace($interlace)
  {
    imageinterlace($this->getImage, true);
    return $this;
  }

  /**
   * Gets the name of the class that will be used to determine the crop offset for the image
   *
   * @since 2.0
   * @param string $className Name of the cropper class name to get
   * @return string
   */
  private function getCropperClassName($className = null)
  {
    if ($className !== null) {
      return $className;
    } else if ($this->cropper !== null) {
      return $this->cropper;
    } else {
      return SLIRConfig::$defaultCropper;
    }
  }

  /**
   * Gets the class that will be used to determine the crop offset for the image
   *
   * @since 2.0
   * @param string $className Name of the cropper class to get
   * @return SLIRCropper
   */
  final public function getCropperClass($className = null)
  {
    $cropClass  = strtolower($this->getCropperClassName($className));
    $fileName   = SLIRConfig::$pathToSLIR . "/core/libs/gd/croppers/$cropClass.class.php";
    $class      = 'SLIRCropper' . ucfirst($cropClass);

    if (!file_exists($fileName)) {
      throw new RuntimeException('The requested cropper could not be found: ' . $fileName);
    }

    require_once $fileName;

    return new $class();
  }

  /**
   * Performs the actual cropping of the image
   *
   * @param integer $cropWidth
   * @param integer $cropHeight
   * @param string $fill color in hex
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function crop($cropWidth, $cropHeight, $fill = null)
  {
    if ($this->croppingIsNeeded($cropWidth, $cropHeight)) {
      $cropper  = $this->getCropperClass();
      $offset   = $cropper->getCrop($this);
      $this->cropImage($cropWidth, $cropHeight, $offset['x'], $offset['y'], $fill);
    }

    return $this;
  }

  /**
   * Performs the actual cropping of the image
   *
   * @since 2.0
   * @param integer $width Width of cropped image
   * @param integer $height Height of cropped image
   * @param integer $leftOffset Number of pixels from the left side of the image to crop in
   * @param integer $topOffset Number of pixels from the top side of the image to crop in
   * @param string $fill color in hex
   * @return boolean
   */
  private function cropImage($width, $height, $leftOffset, $topOffset, $fill = null)
  {
    $cropped = new get_class();

    $cropped->background($fill);

    // Copy rendered image to cropped image
    imagecopy(
        $cropped->getImage($width, $height),
        $this->getImage(),
        0,
        0,
        $leftOffset,
        $topOffset,
        $cropped->getWidth(),
        $cropped->getHeight()
    );

    // Replace pre-cropped image with cropped image
    $this->destroy();
    $this->image          = $cropped->getImage();

    // Update width and height
    $this->info['width']  = $cropped->getWidth();
    $this->info['height'] = $cropped->getHeight();

    // Clean up memory
    $cropped->destroy(); // This might destroy both images
    unset($cropped);

    return $this;
  }

  /**
   * Sharpens the image
   * @param integer $sharpness
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function sharpen($sharpness)
  {
    if ($this->isSharpeningDesired()) {
      imageconvolution(
          $this->getImage(),
          $this->sharpenMatrix($sharpness),
          $sharpness,
          0
      );
    }

    return $this;
  }

  /**
   * @param integer $sharpness
   * @return array
   * @since 2.0
   */
  private function sharpenMatrix($sharpness)
  {
    return array(
      array(-1, -2, -1),
      array(-2, $sharpness + 12, -2),
      array(-1, -2, -1)
    );
  }

  /**
   * Outputs the image
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function output()
  {
    return $this->save(null);
  }

  /**
   * Saves the image to disk
   * @param string $filename
   * @return boolean
   * @since 2.0
   */
  public function save($path)
  {
    if ($this->isJPEG()) {
      return imagejpeg($this->image, $path, $this->getQuality());
    } else if ($this->isPNG()) {
      return imagepng($this->image, $path, $this->getQuality());
    } else if ($this->isGIF()) {
      return imagegif($this->image, $path, $this->getQuality());
    } else {
      return false;
    }
  }

  /**
   * Destroys the image
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function destroy()
  {
    imagedestroy($this->getImage());
    return $this;
  }
}
