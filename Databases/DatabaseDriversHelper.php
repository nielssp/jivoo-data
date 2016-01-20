<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Helpers\Helper;
use Jivoo\Core\Json;
use Jivoo\Core\JsonException;

/**
 * Helper for listing database drivers.
 */
class DatabaseDriversHelper extends Helper {
  /**
   * Get information about a database driver.
   *
   * The returned information array is of the format:
   * <code>
   * array(
   *   'driver' => ..., // Driver name (string)
   *   'name' => ..., // Formal name, e.g. 'MySQL' instead of 'MySql' (string)
   *   'requiredOptions' => array(...), // List of required options (string[])
   *   'optionalOptions' => array(...), // List of optional options (string[])
   *   'isAvailable' => ..., // Whether or not driver is available (bool)
   *   'missingExtensions => array(...) // List of missing extensions (string[])
   * )
   * </code>
   * @param string $driver Driver name
   * @return array Driver information as an associative array.
   * @throws InvalidDriverException If driver is missing or invalid.
   */
  public function checkDriver($driver) {
    if (!file_exists($this->p('Jivoo/Databases/Drivers/' . $driver . '/' . $driver . 'Database.php'))) {
      throw new InvalidDriverException(tr('Driver class not found: %1', $driver));
    }
    if (!file_exists($this->p('Jivoo/Databases/Drivers/' . $driver . '/driver.json'))) {
      throw new InvalidDriverException(tr('Driver manifest not found: %1', $driver));
    }
    try {
      $info = Json::decodeFile($this->p('Jivoo/Databases/Drivers/' . $driver . '/driver.json'));
    }
    catch (JsonException $e) {
      throw new InvalidDriverException(tr('Invalid driver manifest: %1 (%2)', $driver, $e->getMessage()), 0, $e);
    }
    if (!isset($info['required']))
      $info['required'] = array();
    if (!isset($info['optional']))
      $info['optional'] = array();
    if (!isset($info['phpExtensions']))
      $info['phpExtensions'] = array();
    $missing = array();
    foreach ($info['phpExtensions'] as $dependency) {
      if (!extension_loaded($dependency)) {
        $missing[] = $dependency;
      }
    }
    return array(
      'driver' => $driver,
      'name' => $info['name'],
      'requiredOptions' => $info['required'],
      'optionalOptions' => $info['optional'],
      'isAvailable' => count($missing) < 1,
      'missingExtensions' => $missing
    );
  }
  
  /**
   * Get an array of all drivers and their information.
   * @return array An associative array of driver names and driver information
   * as returned by {@see Database::checkDriver()}.
   */
  public function listDrivers() {
    $drivers = array();
    $files = scandir($this->p('Jivoo/Databases/Drivers'));
    if ($files !== false) {
      foreach ($files as $driver) {
        if (is_dir($this->p('Jivoo/Databases/Drivers/' . $driver))) {
          try {
            $drivers[$driver] = $this->checkDriver($driver);
          }
          catch (InvalidDriverException $e) {
            $this->logger->warning($e->getMessage(), array('exception' => $e));
          }
        }
      }
    }
    return $drivers;
  }
}