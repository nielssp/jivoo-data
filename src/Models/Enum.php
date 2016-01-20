<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models;

use Jivoo\Core\Utilities;

/**
 * Used for creating enum types.
 * 
 * Example:
 * <code>
 * class DayOfWeek extends Enum {
 *   const monday = 1;
 *   const tuesday = 2;
 *   const wednesday = 3;
 *   const thursday = 4;
 *   const friday = 5;
 *   const saturday = 6;
 *   const sunday = 7;
 * }
 * </code>
 * 
 */
abstract class Enum {
  /**
   * @var array Values of enums.
   */
  private static $values = array();

  /**
   * @var string[].
   */
  private static $classes = array();
  
  /**
   * @var string[]
   */
  private static $searchPrefixes = array();

  private final function __construct() { }

  /**
   * Whether an enum class exists.
   * @param string $class Class name.
   * @return bool True if class exists.
   */
  public static function classExists($class) {
    if (isset(self::$values[$class]))
      return true;
    if (isset(self::$classes[$class]))
      return true;
    if (class_exists($class)) {
      self::$classes[$class] = $class;
      return true;
    }
    foreach (self::$searchPrefixes as $prefix) {
      if (class_exists($prefix . $class)) {
        self::$classes[$class] = $prefix . $class;
        return true;
      }
    }
    return false;
  } 
  
  /**
   * Get values of an enum class.
   * @param string $class Class name.
   * @throws InvalidEnumException If the class invalid or  does not contain
   * constants.
   * @return string[] Enum values.
   */
  public static function getValues($class = null) {
    if (!isset($class))
      $class = get_called_class();
    if (!isset(self::$values[$class])) {
      if (!self::classExists($class))
        throw new InvalidEnumException(tr('Enum class not found: %1', $class));
      $class = self::$classes[$class];
      Utilities::assumeSubclassOf($class, 'Jivoo\Models\Enum');
      $ref = new \ReflectionClass($class);
      self::$values[$class] = array_flip($ref->getConstants());
      if (count(self::$values[$class]) < 1)
        throw new InvalidEnumException(tr('Enum type "%1" must contain at least one constant', $class));
    }
    return self::$values[$class];
  }
  
  /**
   * Add namespace to search for enum classes under.
   * @param string $prefix Prefix, e.g. 'App\Enums\\'
   */
  public static function addSearchPrefix($prefix) {
    self::$searchPrefixes[] = $prefix;
  }

  /**
   * Get index of an enum value.
   * @param string $str Enum value.
   * @param string $class Class name.
   * @return int Index.
   */
  public static function getValue($str, $class = null) {
    if (!isset($class))
      $class = get_called_class();
    if (!isset(self::$values[$class]))
      self::getValues($class);
    return array_search($str, self::$values[$class]);
  }
}
