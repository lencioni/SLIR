<?php
/**
 * Class definition file for the centered SLIR cropper
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
 * @subpackage Croppers
 */

namespace SLIR\Libs\GD\Croppers;


use \SLIR\Libs\SLIRImage;

/**
 * Centered SLIR cropper
 *
 * Calculates the crop offset anchored in the center of the image
 *
 * @since 2.0
 * @author Joe Lencioni <joe@shiftingpixel.com>
 * @package SLIR
 * @subpackage Croppers
 */
class SLIRCropperCentered implements SLIRCropper
{
  /**
   * Determines if the top and bottom need to be cropped
   *
   * @since 2.0
   * @param SLIRImage $image
   * @return boolean
   */
  private function shouldCropTopAndBottom(SLIRImage $image)
  {
    if ($image->getCropRatio() > $image->getRatio()) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * @since 2.0
   * @param SLIRImage $image
   * @return integer
   */
  public function getCropY(SLIRImage $image)
  {
    return round(($image->getHeight() - $image->getCropHeight()) / 2);
  }

  /**
   * @since 2.0
   * @param SLIRImage $image
   * @return integer
   */
  public function getCropX(SLIRImage $image)
  {
    return round(($image->getWidth() - $image->getCropWidth()) / 2);
  }

  /**
   * Calculates the crop offset anchored in the center of the image
   *
   * @since 2.0
   * @param SLIRImage $image
   * @return array Associative array with the keys of x and y that specify the top left corner of the box that should be cropped
   */
  public function getCrop(SLIRImage $image)
  {
    // Determine crop offset
    $crop = array(
      'x' => 0,
      'y' => 0,
    );

    if ($this->shouldCropTopAndBottom($image)) {
      // Image is too tall so we will crop the top and bottom
      $crop['y']  = $this->getCropY($image);
    } else {
      // Image is too wide so we will crop off the left and right sides
      $crop['x']  = $this->getCropX($image);
    }

    return $crop;
  }
}
