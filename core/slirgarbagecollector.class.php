<?php
/**
 * Class definition file for SLIRGarbageCollector
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
 * SLIR garbage collector class
 *
 * @since 2.0
 * @author Joe Lencioni <joe@shiftingpixel.com>
 * @package SLIR
 */
class SLIRGarbageCollector
{

  /**
   * Setting for the garbage collector to sleep for a second after looking at this many files
   *
   * @since 2.0
   * @var integer
   */
  const BREATHE_EVERY = 5000;

  /**
   * Garbage collector
   *
   * Clears out old files from the cache
   *
   * @since 2.0
   * @param array $directories
   * @return void
   */
  public function __construct(array $directories)
  {
    // This code needs to be in a try/catch block to prevent the epically unhelpful
    // "PHP Fatal error:  Exception thrown without a stack frame in Unknown on line
    // 0" from showing up in the error log.
    try {
      if ($this->isRunning()) {
        return;
      }

      $this->start();
      foreach ($directories as $directory => $useAccessedTime) {
        $this->deleteStaleFilesFromDirectory($directory, $useAccessedTime);
      }
      $this->finish();
    } catch (Exception $e) {
      error_log(sprintf("\n[%s] %s thrown within the SLIR garbage collector. Message: %s in %s on line %d", @gmdate('D M d H:i:s Y'), get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()), 3, SLIRConfig::$pathToErrorLog);
      error_log("\nException trace stack: " . print_r($e->getTrace(), true), 3, SLIRConfig::$pathToErrorLog);
      $this->finish(false);
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
      if ($file->key() % self::BREATHE_EVERY == 0) {
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

    unset($dir);
  }

  /**
   * Checks to see if the garbage collector is currently running.
   *
   * @since 2.0
   * @return boolean
   */
  private function isRunning()
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
  private function start()
  {
    error_log(sprintf("\n[%s] Garbage collection started", @gmdate('D M d H:i:s Y')), 3, SLIRConfig::$pathToErrorLog);

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
  private function finish($successful = true)
  {
    // Delete the file that tells SLIR that the garbage collector is running
    unlink(SLIRConfig::$pathToCacheDir . '/garbageCollector.tmp');

    if ($successful) {
      error_log(sprintf("\n[%s] Garbage collection completed", @gmdate('D M d H:i:s Y')), 3, SLIRConfig::$pathToErrorLog);
    }
  }
}
