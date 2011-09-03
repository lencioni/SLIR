<?php
abstract class SLIRImageLibrary
{
  /**
   * Mime types
   * @var array
   * @since 2.0
   */
  private $mimeTypes  = array(
    'JPEG'  => array(
      'image/jpeg'  => 1,
    ),
    'GIF' => array(
      'image/gif'   => 1,
    ),
    'PNG' => array(
      'image/png'   => 1,
      'image/x-png' => 1,
    ),
    'BMP' => array(
      'image/bmp'       => 1,
      'image/x-ms-bmp'  => 1,
    ),
  );

  /**
   * Checks the mime type to see if it is an image
   *
   * @since 2.0
   * @return boolean
   */
  final public function isImage()
  {
    if (substr($this->getMimeType(), 0, 6) == 'image/') {
      return true;
    } else {
      return false;
    }
  }

  /**
   * @since 2.0
   * @param string $type Can be 'JPEG', 'GIF', 'PNG', or 'BMP'
   * @return boolean
   */
  final public function isOfType($type = 'JPEG')
  {
    if (isset($this->mimeTypes[$type][$this->getMimeType()])) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * @since 2.0
   * @return boolean
   */
  final public function isJPEG()
  {
    return $this->isOfType('JPEG');
  }

  /**
   * @since 2.0
   * @return boolean
   */
  final public function isGIF()
  {
    return $this->isOfType('GIF');
  }

  /**
   * @since 2.0
   * @return boolean
   */
  final public function isBMP()
  {
    return $this->isOfType('BMP');
  }

  /**
   * @since 2.0
   * @return boolean
   */
  final public function isPNG()
  {
    return $this->isOfType('PNG');
  }

  /**
   * @since 2.0
   * @return boolean
   */
  final public function isAbleToHaveTransparency()
  {
    if ($this->isPNG() || $this->isGIF()) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * @since 2.0
   * @return boolean
   */
  final protected function isSharpeningDesired()
  {
    if ($this->isJPEG()) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * @since 2.0
   * @return integer
   */
  final public function getArea()
  {
    return $this->getWidth() * $this->getHeight();
  }

  /**
   * Turns on transparency for image if no background fill color is
   * specified, otherwise, fills background with specified color
   *
   * @param string $color in hex format
   * @since 2.0
   * @return SLIRImageLibrary
   */
  final public function background($color = null)
  {
    if ($this->isAbleToHaveTransparency()) {
      if (empty($color)) {
        // If this is a GIF or a PNG, we need to set up transparency
        $this->enableTransparency();
      } else {
        // Fill the background with the specified color for matting purposes
        $this->fill($color);
      }
    }

    return $this;
  }

  /**
   * @since 2.0
   * @param integer $cropWidth
   * @param integer $cropHeight
   * @return boolean
   */
  private function croppingIsNeeded($cropWidth, $cropHeight)
  {
    if ($cropWidth < $this->getWidth() || $cropHeight < $this->getHeight()) {
      return true;
    } else {
      return false;
    }
  }
}
