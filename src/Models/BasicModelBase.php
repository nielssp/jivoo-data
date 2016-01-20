<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models;

/**
 * A basic model implementation.
 */
class BasicModelBase implements BasicModel {
  
  /**
   * @var string[] Associative array of fields and labels.
   */
  private $labels = array();
  
  /**
   * @var DataType[] Associative array of fields and types.
   */
  private $types = array();
  
  /**
   * @var bool[] Associative array of fields and whether or not they are
   * required.
   */
  private $required = array();
  
  /**
   * @var string Model name.
   */
  private $name;
  
  /**
   * Construct basic model.
   * @param string $name Model name.
   */
  public function __construct($name) {
    $this->name = $name;
  }

  /**
   * Add field to model.
   * @param string $field Field name.
   * @param string $label Field label.
   * @param DataType $type Field type.
   */
  protected function addField($field, $label, DataType $type) {
    $this->labels[$field] = $label;
    $this->types[$field] = $type;
    if (!$type->null)
      $this->required[$field] = true;
  }
  
  /**
   * {@inherit}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inherit}
   */
  public function getFields() {
    return array_keys($this->labels);
  }

  /**
   * {@inherit}
   */
  public function getLabel($field) {
    if (isset($this->labels[$field]))
      return $this->labels[$field];
    return null;
  }

  /**
   * {@inherit}
   */
  public function getType($field) {
    if (isset($this->types[$field]))
      return $this->types[$field];
    return null;
  }

  /**
   * {@inherit}
   */
  public function hasField($field) {
    return isset($this->labels[$field]);
  }

  /**
   * {@inherit}
   */
  public function isRequired($field) {
    return isset($this->required[$field]);
  }
  
  /**
   * Sort an array of records by a field.
   * @param string $field Field to sort by.
   * @param BasicRecord[] $selection Array of records.
   * @param bool Whether to sort in descending order.
   * @return BasicRecord[] Sorted array.
   */
  public function sortBy($field, $selection, $descending = false) {
    assume(is_array($selection));
    usort($selection, function(BasicRecord $a, BasicRecord $b) use($field, $descending) {
      if ($a->$field == $b->$field)
        return 0;
      if ($descending) {
        if (is_numeric($a->$field))
          return $b->$field - $a->$field;
        return strcmp($b->$field, $a->$field);
      }
      else {
        if (is_numeric($a->$field))
          return $a->$field - $b->$field;
        return strcmp($a->$field, $b->$field);
      }
    });
    return $selection;
  }

  /**
   * Sort an array of records by a field in descending order.
   * @param string $field Field to sort by.
   * @param BasicRecord[] $selection Array of records.
   * @return BasicRecord[] Sorted array.
   */
  public function sortByDescending($field, $selection) {
    return $this->sortBy($field, $selection, true);
  }
}