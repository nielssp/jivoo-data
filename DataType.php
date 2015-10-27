<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

use Jivoo\Models\Validation\ValidatorField;
use Jivoo\InvalidPropertyException;

/**
 * Model field data type.
 * @property-read int $type Type (see type constants).
 * @property-read bool $null Whether or not type is nullable.
 * @property-read bool $notNull Opposite of null.
 * @property-read mixed $default Default value.
 * @property-read int $length Length (strings only).
 * @property-read int $size Size: BIG, SMALL, TINY or 0 (integers only).
 * @property-read bool $signed Signed (integers only).
 * @property-read bool $unsigned Opposite of signed (integers only).
 * @property-read bool $autoIncrement Auto increment (integers only).
 * @property-read string $placeholder Placeholder of data type.
 */
class DataType {
  /** @var int Type: Integer. */
  const INTEGER = 1;
  /** @var int Type: String (length <= 255). */
  const STRING = 2;
  /** @var int Type: Text. */
  const TEXT = 3;
  /** @var int Type: Boolean. */
  const BOOLEAN = 4;
  /** @var int Type: Float. */
  const FLOAT = 5;
  /** @var int Type: Date. */
  const DATE = 6;
  /** @var int Type: Date/time. */
  const DATETIME = 7;
  /** @var int Type: Binary object. */
  const BINARY = 8;
  /** @var int Type: Generic array/object, can be encoded as JSON. */
  const OBJECT = 9;
  /** @var int Type: Enumerated type. */
  const ENUM = 10;

  /** @var int Flag: Unsigned (integers only). */
  const UNSIGNED = 0x02;
  /** @var int Flag: Auto increment (integers only). */
  const AUTO_INCREMENT = 0x04;
  /** @var int Flag: Tiny integer (8 bit) (integers only). */
  const TINY = 0x10;
  /** @var int Flag: Small integer (16 bit) (integers only). */
  const SMALL = 0x20;
  /** @var int Flag: Big integer (64 bit) (integers only). */
  const BIG = 0x30;
  
  /** @var int Type. */
  private $type;
  /** @var bool Null. */
  private $null = false;
  /** @var int String length. */
  private $length = null;
  /** @var bool Signed. */
  private $signed = true;
  /** @var bool Auto increment. */
  private $autoIncrement = false;
  /** @var mixed Default value. */
  private $default = null;
  /** @var int Integer size. */
  private $size = null;
  
  /**
   * Construct data type.
   * @param int $type Type.
   * @param bool $null Null.
   * @param mixed $default Default value.
   * @param int $flags Integer flags.
   * @param int|null $length String length.
   * @throws InvalidDataTypeException When type is invalid.
   */
  protected function __construct($type, $null = false, $default = null, $flags = 0, $length = null) {
    if ($type < 1 or $type > 10)
      throw new InvalidDataTypeException(tr('%1 is not a valid type', $type));
    $this->type = $type;
    $this->length = $length;
    $this->default = $default;
    $this->null = $null;
    if ($type == self::INTEGER) {
      $this->signed = ($flags & self::UNSIGNED) == 0;
      $this->autoIncrement = ($flags & self::AUTO_INCREMENT) != 0;
      $this->size = $flags & 0x30;
    }
  }
  
  /**
   * Get property value
   * @param string $property Property name
   * @return mixed Property value
   * @throws InvalidPropertyException If property undefined.
   */
  public function __get($property) {
    switch ($property) {
      case 'type':
      case 'null':
      case 'default':
        return $this->$property;
      case 'notNull':
        return !$this->null;
      case 'placeholder':
        switch ($this->type) {
          case self::INTEGER: return '%i';
          case self::STRING: return '%s';
          case self::TEXT: return '%t';
          case self::BOOLEAN: return '%b';
          case self::FLOAT: return '%f';
          case self::DATE: return '%date';
          case self::DATETIME: return '%d';
          case self::OBJECT: return '%o';
        }
    }
    if ($this->type == self::STRING) {
      switch ($property) {
        case 'length':
          return $this->$property;
      }
    }
    if ($this->type == self::INTEGER) {
      switch ($property) {
        case 'size':
        case 'signed':
        case 'autoIncrement':
          return $this->$property;
        case 'unsigned':
          return !$this->signed;
      }
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }

  /**
   * Whether or not a property is set.
   * @param string $property Property name.
   * @return bool True if set.
   */
  public function __isset($property) {
    return $this->$property !== null;
  }

  /**
   * Create string representation of data type.
   * @return string String.
   */
  public function __toString() {
    switch ($this->type) {
      case self::INTEGER:
        $s = '';
        if ($this->autoIncrement)
          $s .= 'Auto ';
        if ($this->unsigned)
          $s .= 'Unsigned ';
        else
          $s .= 'Signed ';
        switch ($this->size) {
          case self::BIG:
            $s .= 'Big ';
            break;
          case self::SMALL:
            $s .= 'Small ';
            break;
          case self::TINY:
            $s .= 'Tiny ';
            break;
        }
        return $s . 'Integer';
      case self::STRING:
        return 'String(' . $this->length . ')';
      case self::TEXT: return 'Text';
      case self::BOOLEAN: return 'Boolean';
      case self::FLOAT: return 'Float';
      case self::DATE: return 'Date';
      case self::DATETIME: return 'Date/Time';
      case self::OBJECT: return 'Object';
    }
  }

  /** @return bool Whether or not the type is integer */
  public function isInteger() {
    return $this->type == self::INTEGER;
  }

  /** @return bool Whether or not the type is string */
  public function isString() {
    return $this->type == self::STRING;
  }

  /** @return bool Whether or not the type is text */
  public function isText() {
    return $this->type == self::TEXT;
  }

  /** @return bool Whether or not the type is boolean */
  public function isBoolean() {
    return $this->type == self::BOOLEAN;
  }

  /** @return bool Whether or not the type is float */
  public function isFloat() {
    return $this->type == self::FLOAT;
  }

  /** @return bool Whether or not the type is date */
  public function isDate() {
    return $this->type == self::DATE;
  }

  /** @return bool Whether or not the type is date/time */
  public function isDateTime() {
    return $this->type == self::DATETIME;
  }

  /** @return bool Whether or not the type is binary */
  public function isBinary() {
    return $this->type == self::BINARY;
  }

  /** @return bool Whether or not the type is array/object. */
  public function isObject() {
    return $this->type == self::OBJECT;
  }

  /** @return bool Whether or not the type is enum */
  public function isEnum() {
    return $this->type == self::ENUM;
  }

  /**
   * Create validation rules for data type.
   * @param ValidatorField $validator A validator field.
   */
  public function createValidationRules(ValidatorField $validator) {
    $validator = $validator->ruleDataType;
    if (!$this->null and $this->type != self::INTEGER and !$this->autoIncrement)
      $validator->null = false;
    switch ($this->type) {
      case self::INTEGER:
        $validator->integer = true;
        if (!$this->null and !$this->autoIncrement)
          $validator->presence = true;
        if ($this->signed) {
          switch ($this->size) {
            case self::BIG:
              return;
            case self::SMALL:
              $validator->minValue = -32768;
              $validator->maxValue = 32767;
              return;
            case self::TINY:
              $validator->minValue = -128;
              $validator->maxValue = 127;
              return;
            default:
              $validator->minValue = -2147483648;
              $validator->maxValue = 2147483647;
              return;
          }
        }
        else {
          $validator->minValue = 0;
          switch ($this->size) {
            case self::BIG:
              return;
            case self::SMALL:
              $valudator->maxValue = 65535;
              return;
            case self::TINY:
              $validator->maxValue = 255;
              return;
            default:
              $validator->maxValue = 4294967295;
              return;
          }
        }
        return;
      case self::STRING:
        $validator->maxLength = $this->length;
        return;
      case self::BOOLEAN:
        $validator->boolean = true;
        return;
      case self::FLOAT:
        if (!$this->null)
          $validator->presence = true;
        $validator->float = true;
        return;
      case self::DATE:
      case self::DATETIME:
        if (!$this->null)
          $validator->presence = true;
        $validator->date = true;
        return;
      case self::TEXT:
      case self::BINARY:
      case self::OBJECT:
        return;
    }
  }

  /**
   * Check if value is of this type.
   * @param mixed $value Value to test.
   * @return bool True if it is, false otherwise.
   */
  public function isValid($value) {
    if ($this->null and $value == null)
      return true;
    switch ($this->type) {
      case self::INTEGER:
        if (!is_int($value))
          return false;
        if ($this->signed) {
          switch ($this->size) {
            case self::BIG:
              return true;
            case self::SMALL:
              return $value >= -32768 and $value <= 32767;
            case self::TINY:
              return $value >= -128 and $value <= 127;
            default:
              return $value >= -2147483648 and $value <= 2147483647;
          }
        }
        else {
          if ($value < 0)
            return false;
          switch ($this->size) {
            case self::BIG:
              return true;
            case self::SMALL:
              return $value <= 65535;
            case self::TINY:
              return $value <= 255;
            default:
              return $value <= 4294967295;
          }
        }
      case self::STRING:
        return is_string($value) and strlen($value) <= $this->length;
      case self::BOOLEAN:
        return is_bool($value);
      case self::FLOAT:
        return is_float($value);
      case self::DATE:
      case self::DATETIME:
        return is_int($value);
      case self::TEXT:
      case self::BINARY:
        return is_string($value);
      case self::OBJECT:
        return is_array($value);
    }
    return false;
  }
  
  /**
   * Convert a value such that it is compatible with this data type. The
   * resulting value is of the correct type but may still not be valid (see
   * {@see isValid()}), e.g. it may be null even if the data type isn't
   * nullable, or out of range (string too long or integer too big or small).
   * @param mixed $value Value.
   * @param bool $strict If true, the function will only use strict conversion
   * for integers and floats and return null if the value is invalid. If false,
   * it will use PHP's built-in conversions, e.g. any value can be converted to
   * an integer etc. and a string like "5notanumber" will be converted to 5.
   * @return mixed A value depending on this data type.
   */
  public function convert($value, $strict = true) {
    //if ($this->null and $value == null)
    if ($value === null)
      return null;
    switch ($this->type) {
      case self::INTEGER:
        if ($strict) {
          if (is_int($value))
            return $value;
          if (!is_string($value) or preg_match('/^-?\d+$/', $value) !== 1)
            return null;
        }
        return intval($value);
      case self::STRING:
        return strval($value);
      case self::BOOLEAN:
        if (is_bool($value))
          return $value;
        if (preg_match('/^yes|1|true$/i', $value) === 1)
          return true;
        if (preg_match('/^no|0|false/i', $value) === 1)
          return false;
        return (bool) $value;
      case self::FLOAT:
        if ($strict) {
          if (is_float($value))
            return $value;
          if (!is_numeric($value))
            return null;
        }
        return floatval($value);
      case self::DATE:
      case self::DATETIME:
        if (is_int($value))
          return $value;
        return strtotime($value);
      case self::TEXT:
      case self::BINARY:
      case self::ENUM:
        return strval($value);
      case self::OBJECT:
        if (is_array($value))
          return $value;
        if (is_object($value))
          return (array) $value;
        return array($value);
    }
    return null;
  }
  
  /**
   * Create integer type.
   * @param int $flags Combination of: UNSIGNED, AUTO_INCREMENT, BIG, SMALL, TINY.
   * @param bool $null Whether or not type is nullable.
   * @param int|null $default Default value.
   * @return DataType Type object.
   */
  public static function integer($flags = 0, $null = false, $default = null) {
    return new self(self::INTEGER, $null, $default, $flags);
  }
  
  /**
   * Create string type.
   * @param int $length String maximum length (0 to 255).
   * @param bool $null Whether or not type is nullable.
   * @param string $default Default value.
   * @return DataType Type object.
   */
  public static function string($length = 255, $null = false, $default = null) {
    return new self(self::STRING, $null, $default, 0, $length);
  }
  
  /**
   * Create text type.
   * @param bool $null Whether or not type is nullable.
   * @param string $default Default value.
   * @return DataType Type object.
   */
  public static function text($null = false, $default = null) {
    return new self(self::TEXT, $null, $default);
  }
  
  /**
   * Create boolean type.
   * @param bool $null Whether or not type is nullable.
   * @param bool $default Default value.
   * @return DataType Type object.
   */
  public static function boolean($null = false, $default = null) {
    return new self(self::BOOLEAN, $null, $default);
  }
  
  /**
   * Create float type.
   * @param bool $null Whether or not type is nullable.
   * @param float $default Default value.
   * @return DataType Type object.
   */
  public static function float($null = false, $default = null) {
    return new self(self::FLOAT, $null, $default);
  }

  /**
   * Create date type.
   * @param bool $null Whether or not type is nullable.
   * @param int $default Default value (unix timestamp).
   * @return DataType Type object.
   */
  public static function date($null = false, $default = null) {
    return new self(self::DATE, $null, $default);
  }
  
  /**
   * Create date/time type.
   * @param bool $null Whether or not type is nullable.
   * @param int $default Default value (unix timestamp).
   * @return DataType Type object.
   */
  public static function dateTime($null = false, $default = null) {
    return new self(self::DATETIME, $null, $default);
  }
  
  /**
   * Create binary object type.
   * @param bool $null Whether or not type is nullable.
   * @param string $default Default value.
   * @return DataType Type object.
   */
  public static function binary($null = false, $default = null) {
    return new self(self::BINARY, $null, $default);
  }
  
  /**
   * Create generic array/object type.
   * @param bool $null Whether or not type is nullable.
   * @param string $default Default value.
   * @return DataType Type object.
   */
  public static function object($null = false, $default = null) {
    return new self(self::OBJECT, $null, $default);
  }
  
  /**
   * Create enumerated data type.
   * @param string[]|string $valuesOrClass Enum values as strings. Keys must be integers.
   * Or the name of a class extending {@see Enum}.
   * @param bool $null Whether or not type is nullable.
   * @param int $default Default value.
   * @return DataType Type object.
   */
  public static function enum($valuesOrClass, $null = false, $default = null) {
    return new EnumDataType($valuesOrClass, $null, $default);
  }
  
  /**
   * Return data type corresponding to value.
   * @param mixed $value Value.
   * @return DataType Type of value.
   */
  public static function detectType($value) {
    if (is_bool($value))
      return self::boolean();
    if (is_int($value))
      return self::integer();
    if (is_float($value))
      return self::float();
    if (is_array($value) or is_object($value))
      return self::object();
    return self::text();
  }
  
  /**
   * Create data type from a placeholder.
   * @param string $placeholder Placeholder string.
   * @return DataType Type object.
   */
  public static function fromPlaceholder($placeholder) {
    switch (strtolower($placeholder)) {
      case 'i':
      case 'int':
      case 'integer':
        return self::integer(self::BIG);
      case 'f':
      case 'float':
        return self::float();
      case 's':
      case 'str':
      case 'string':
        return self::string(255);
      case 't':
      case 'text':
        return self::text();
      case 'b':
      case 'bool':
      case 'boolean':
        return self::boolean();
      case 'date':
        return self::date();
      case 'd':
      case 'datetime':
        return self::dateTime();
      case 'n':
      case 'bin':
      case 'binary':
        return self::binary();
      case 'a':
      case 'o':
      case 'array':
      case 'object':
        return self::binary();
    }
    if (Enum::classExists($placeholder))
      return self::enum($placeholder);
    throw new InvalidDataTypeException(tr('Invalid data type placeholder: %1', '%' . $placeholder));
  }
}
