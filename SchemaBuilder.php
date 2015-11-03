<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

use Jivoo\Core\Utilities;
use Jivoo\Models\Validation\ValidatorBuilder;
use Jivoo\InvalidPropertyException;

/**
 * Represents a database table schema.
 */
class SchemaBuilder implements Schema {
  
  /**
   * @var DataType[] List of column names.
   */
  private $fields = array();
  
  /**
   * @var string Name of table.
   */
  private $name = 'undefined';

  /**
   * @var array List of keys.
   */
  private $keys = array();
  
  /**
   * Constructor
   * @param string $name Name of schema
  */
  public function __construct($name) {
    $this->name = $name;
  }

  /**
   * Set type of field.
   * @param string $field Field name.
   * @param DataType $type Type.
   */
  public function __set($field, DataType $type) {
    $this->fields[$field] = $type;
  }
  
  /**
   * Delete field.
   * @param string $field Field name.
   */
  public function __unset($field) {
    unset($this->fields[$field]);
  }
  
  /**
   * {@inheritdoc}
   */
  public function copy($newName) {
    $new = clone $this;
    $new->name = $newName;
    return $new;
  }

  /**
   * Add an unsigned auto increment integer.
   * @param string $id Field name.
   */
  public function addAutoIncrementId($id = 'id') {
    $this->$id = DataType::integer(DataType::AUTO_INCREMENT | DataType::UNSIGNED);
    $this->setPrimaryKey($id);
  }

  /**
   * Add created and updated timestamps to schema.
   * @param string $created Created field name.
   * @param string $updated Updated field name.
   */
  public function addTimestamps($created = 'created', $updated = 'updated') {
    $this->$created = DataType::dateTime();
    $this->$updated = DataType::dateTime();
  }

  /**
   * Create validation rules based on types.
   * @param ValidatorBuilder $validator Validator to create rules on.
   */
  public function createValidationRules(ValidatorBuilder $validator) {
    foreach ($this->fields as $field => $type) {
      $type->createValidationRules($validator->$field);
    }
    foreach ($this->keys as $index) {
      if ($index['unique'] and count($index['columns']) == 1) {
        $field = $index['columns'][0];
        $validator->$field->unique = true;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    return array_keys($this->fields);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->_name;
  }

  
  /**
   * Set primary key.
   * @param string|string[] $columns An array of column names or a single column
   * name.
   * @param string $columns,... Additional column names (if $columns is a single
   * column name).
   */
  public function setPrimaryKey($columns) {
    if (!is_array($columns)) {
      $params = func_get_args();
      if (count($params) > 1) {
        $columns = $params;
      }
      else {
        $columns = array($columns);
      }
    }
    $this->keys['PRIMARY'] = array(
      'columns' => $columns,
      'unique' => true
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getPrimaryKey() {
    if (!isset($this->keys['PRIMARY'])) {
      return array();
    }
    return $this->keys['PRIMARY']['columns'];
  }
  
  /**
   * Check if the column is part of the primary key.
   * @param string $column Column name.
   * @return boolean True if part of primary key, false otherwise.
   */
  public function isPrimaryKey($column) {
    if (!isset($this->keys['PRIMARY'])) {
      return false;
    }
    return in_array($column, $this->keys['PRIMARY']['columns']);
  }

  /**
   * Add a unique index to schema.
   * @param string $index Index name.
   * @param string|string[] $columns An array of column names or a single column
   * name.
   * @param string $columns,... Additional column names (if $columns is a single
   * column name).
   */
  public function addUnique($name, $columns) {
    if (!is_array($columns)) {
      $params = func_get_args();
      if (count($params) > 2) {
        array_shift($params);
        $columns = $params;
      }
      else {
        $columns = array($columns);
      }
    }
    if (isset($this->keys[$name])) {
      $this->keys[$name]['columns'] = array_merge($this->keys[$name]['columns'], $columns);
    }
    else {
      $this->keys[$name] = array(
        'columns' => $columns,
        'unique' => true
      );
    }
  }

  /**
   * Add an index to schema.
   * @param string $index Index name.
   * @param string|string[] $columns An array of column names or a single column
   * name.
   * @param string $columns,... Additional column names (if $columns is a single
   * column name).
   */
  public function addIndex($name, $columns) {
    if (!is_array($columns)) {
      $params = func_get_args();
      if (count($params) > 2) {
        array_shift($params);
        $columns = $params;
      }
      else {
        $columns = array($columns);
      }
    }
    if (isset($this->keys[$name])) {
      $columns = array_merge($this->keys[$name]['columns'], $columns);
    }
    if (isset($this->keys[$name])) {
      $this->keys[$name]['columns'] = array_merge($this->keys[$name]['columns'], $columns);
    }
    else {
      $this->keys[$name] = array(
        'columns' => $columns,
        'unique' => false
      );
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function getKeys() {
    return $this->keys;
  }

  /**
   * {@inheritdoc}
   */
  public function indexExists($name) {
    return isset($this->keys[$name]);
  }
  
  /**
   * Remove an index.
   * @param string $name Index name.
   */
  public function removeIndex($name) {
    unset($this->keys[$name]);
  }

  /**
   * {@inheritdoc}
   */
  public function getIndex($name) {
    return $this->keys[$name];
  }
}
