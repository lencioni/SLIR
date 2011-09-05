<?php
/**
 * Interface for SLIR Image Library
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
 * SLIR Image Library interface
 * @package SLIR
 * @since 2.0
 */
interface SLIRImageLibrary
{
  /**
   * Resamples the image into the destination image
   * @param SLIRImageLibrary $destination
   * @param integer $width
   * @param integer $height
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function resample(SLIRImageLibrary $destination);

  /**
   * Copies the image into the destination image without reszing
   * @param SLIRImageLibrary $destination
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function copy(SLIRImageLibrary $destination);

  /**
   * Gets a hash that represents the properties of the image.
   * 
   * Used for caching.
   * 
   * @param array $infosToInclude
   * @return string
   * @since 2.0
   */
  public function getHash(array $infosToInclude = array());

  /**
   * Sets the path of the file
   * @param string $path
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function setPath($path);

  /**
   * Gets the path of the file
   * @return string
   * @since 2.0
   */
  public function getPath();

  /**
   * Sets the path of the original file
   * @param string $path
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function setOriginalPath($path);

  /**
   * Gets the path of the original file
   * @return string
   * @since 2.0
   */
  public function getOriginalPath();
  
  /**
   * Gets the width of the image
   * @return integer
   * @since 2.0
   */
  public function getWidth();

  /**
   * Gets the height of the image
   * @return integer
   * @since 2.0
   */
  public function getHeight();

  /**
   * Sets the width of the image
   * @param integer $width
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function setWidth($width);

  /**
   * Sets the height of the image
   * @param integer $height
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function setHeight($height);

  /**
   * Gets the width of the cropped image
   * @return integer
   * @since 2.0
   */
  public function getCropWidth();

  /**
   * Gets the height of the cropped image
   * @return integer
   * @since 2.0
   */
  public function getCropHeight();

  /**
   * Sets the width of the cropped image
   * @param integer $width
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function setCropWidth($width);

  /**
   * Sets the height of the cropped image
   * @param integer $height
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function setCropHeight($height);

  /**
   * Gets cropper to be used
   * @return string
   * @since 2.0
   */
  public function getCropper();

  /**
   * Sets the cropper to be used
   * @param string $cropper
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function setCropper($cropper);

  /**
   * @return integer
   * @since 2.0
   */
  public function getArea();

  /**
   * Gets info about the image
   * @param string $info
   * @return mixed
   * @since 2.0
   */
  public function getInfo($info = null);

  /**
   * Gets the width / height
   * @return float
   * @since 2.0
   */
  public function getRatio();

  /**
   * Gets the cropWidth / cropHeight
   * @return float
   * @since 2.0
   */
  public function getCropRatio();

  /**
   * Gets the MIME type of the image
   * @return string
   * @since 2.0
   */
  public function getMimeType();

  /**
   * @return string raw image data
   * @since 2.0
   */
  public function getData();

  /**
   * @return integer size of image data
   */
  public function getDatasize();

  /**
   * Creates a new, blank image
   * @param integer $width
   * @param integer $height
   * @return SLIRImageLibrary
   */
  public function create();

  /**
   * @return integer
   * @since 2.0
   */
  public function getQuality();

  /**
   * @param integer $quality
   * @return SLIRImageLibrary
   */
  public function setQuality($quality);

  /**
   * @return string
   * @since 2.0
   */
  public function getBackground();

  /**
   * @param string $color in hex
   * @return SLIRImageLibrary
   */
  public function setBackground($color);

  /**
   * Turns on transparency for image if no background fill color is
   * specified, otherwise, fills background
   *
   * @since 2.0
   * @return SLIRImageLibrary
   */
  public function background();

  /**
   * Turns on the alpha channel to enable transparency in the image
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function enableTransparency();

  /**
   * Fills the image with the set background color
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function fill();

  /**
   * @return boolean
   * @since 2.0
   */
  public function getProgressive();

  /**
   * @param boolean $progressive
   * @return SLIRImageLibrary
   */
  public function setProgressive($progressive);

  /**
   * Turns interlacing on or off
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function interlace();

  /**
   * Performs the actual cropping of the image
   *
   * @param integer $cropWidth
   * @param integer $cropHeight
   * @param string $fill color in hex
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function crop();

  /**
   * @return float
   * @since 2.0
   */
  public function getSharpeningFactor();

  /**
   * @param float $sharpeningFactor
   * @return SLIRImageLibrary
   */
  public function setSharpeningFactor($sharpeningFactor);

  /**
   * Sharpens the image
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function sharpen();

  /**
   * Outputs the image to the client
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function output();

  /**
   * Saves the image to disk
   * @param string $path
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function save();

  /**
   * Destroys the image resource
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function destroy();

  /**
   * Resizes, crops, sharpens, fills background, etc.
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function applyTransformations();
}
