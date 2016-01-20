<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models;

use Jivoo\Models\Validation\ValidatorField;
use Jivoo\InvalidPropertyException;

/**
 * The data type of {@see Enum}s.
 * @property-read string[] $values Enum values.
 */
class EnumDataType extends DataType {
  
  /**
   * @var string Enum class if any.
   */
  private $class = null;
  
  /**
   * @var string[] Enum values.
   */
  private $values;
  
  /**
   * Construct enum data type.
   * @param string|string[] $valuesOrClass Either an enum class or a list of
   * enum values.
   * @param string $null Is nullable.
   * @param string $default Default value.
   * @throws InvalidEnumException If default values is not part of enum.
   */
  protected function __construct($valuesOrClass, $null = false, $default = null) {
    parent::__construct(DataType::ENUM, $null, $default);
    if (is_array($valuesOrClass)) {
      $this->values = $valuesOrClass;
    }
    else { 
      $this->class = $valuesOrClass;
      $this->values = Enum::getValues($this->class);
    }
    if (isset($default) and !in_array($default, $this->values)) {
      throw new InvalidEnumException(tr(
        'Default value must be part of enum'
      ));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __get($property) {
    if ($property === 'values')
      return $this->values;
    if ($property === 'placeholder') {
      if (!isset($this->class))
        throw new InvalidPropertyException(tr('Invalid use of anonymous enum type'));
      return '%' . $this->class;
    }
    return parent::__get($property);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    if (!isset($this->class))
      return 'Enum(' . implode(', ', $this->values) . ')';
    return $this->class;
  }

  /**
   * {@inheritdoc}
   */
  public function createValidationRules(ValidatorField $validator) {
    $validator = $validator->ruleDataType;
    if (!$this->null)
      $validator->null = false;
    $validator->in = array_values($this->values);
  }

  /**
   * {@inheritdoc}
   */  
  public function isValid($value) {
    if ($this->null and $value == null)
      return true;
    return in_array($value, $this->values);
  }
}
