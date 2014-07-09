<?php
/**
 * Class definition file for the top/centered SLIR cropper
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
 * Top/centered SLIR cropper
 *
 * Calculates the crop offset anchored in the top of the image if the top and bottom are being cropped, or the center of the image if the left and right are being cropped
 *
 * @since 2.0
 * @author Joe Lencioni <joe@shiftingpixel.com>
 * @package SLIR
 * @subpackage Croppers
 */
class SLIRCropperTopcentered extends SLIRCropperCentered
{
  /**
   * @since 2.0
   * @param SLIRImage $image
   * @return integer
   */
  public function getCropY(SLIRImage $image)
  {
    return 0;
  }
}
