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
  public function resample(SLIRImageLibrary $destination, $width, $height);

  /**
   * Copies the image into the destination image without reszing
   * @param SLIRImageLibrary $destination
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function copy(SLIRImageLibrary $destination);

  /**
   * Gets width, height, and iptc information from the image
   * @return array with keys of width, height, and iptc
   * @since 2.0
   */
  protected function getInfo();

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
   * @return integer
   * @since 2.0
   */
  public function getArea();

  /**
   * Gets the MIME type of the image
   * @return string
   * @since 2.0
   */
  public function getMimeType();

  /**
   * Creates a new, blank image
   * @param integer $width
   * @param integer $height
   * @return SLIRImageLibrary
   */
  public function create($width, $height);

  /**
   * Turns on transparency for image if no background fill color is
   * specified, otherwise, fills background with specified color
   *
   * @param string $color in hex format
   * @since 2.0
   * @return SLIRImageLibrary
   */
  public function background($color = null);

  /**
   * Turns on the alpha channel to enable transparency in the image
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function enableTransparency();

  /**
   * Fills the image with the given color
   * @param string $color in hex format
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function fill($color);

  /**
   * Turns interlacing on or off
   * @param boolean $interlace
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function interlace($interlace);

  /**
   * Performs the actual cropping of the image
   *
   * @param integer $cropWidth
   * @param integer $cropHeight
   * @param string $fill color in hex
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function crop($cropWidth, $cropHeight, $fill = null);

  /**
   * Sharpens the image
   * @param integer $sharpness
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function sharpen($sharpness);

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
  public function save($path);

  /**
   * Destroys the image resource
   * @return SLIRImageLibrary
   * @since 2.0
   */
  public function destroy();
}
