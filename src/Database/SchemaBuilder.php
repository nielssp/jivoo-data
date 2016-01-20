<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Models\Schema;
use Jivoo\Models\DataType;
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
  private $_fields = array();
  
  /**
   * @var bool Whether or not schema is read only.
   */
  private $_readOnly = false;
  
  /**
   * @var string Name of table.
   */
  private $_name = 'undefined';

  /**
   * @var array List of indexes.
   */
  private $_indexes = array();
  
  private $_revision = 0;

  /**
   * Constructor
   * @param string $name Name of schema
  */
  public function __construct($name = null) {
    $className = get_class($this);
    if ($className != __CLASS__) {
      if (!isset($name)) {
        $name = preg_replace('/Schema$/', '', Utilities::getClassName($className));
      }
      if (defined($className . '::REVISION'))
        $this->_revision = constant($className . '::REVISION');
      $this->createSchema();
      $this->_readOnly = true;
    }
    if (isset($name)) {
      $this->_name = $name;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __get($field) {
    if (isset($this->_fields[$field])) {
      return $this->_fields[$field];
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $field));
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($field) {
    return isset($this->_fields[$field]);
  }

  /**
   * Set type of field.
   * @param string $field Field name.
   * @param DataType $type Type.
   */
  public function __set($field, DataType $type) {
    if (!$this->_readOnly) {
      $this->_fields[$field] = $type;
    }
  }
  
  /**
   * Delete field.
   * @param string $field Field name.
   */
  public function __unset($field) {
    if (!$this->_readOnly) {
      unset($this->_fields[$field]);
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function copy($newName) {
    $new = clone $this;
    $new->_name = $newName;
    return $new;
  }
  
  /**
   * {@inheritdoc}
   */
  public function filter($data) {
    return $data;
  }

  /**
   * Add an unsigned auto increment integer.
   * @param string $id Field name.
   */
  public function addAutoIncrementId($id = 'id') {
    if (!$this->_readOnly) {
      $this->$id = DataType::integer(DataType::AUTO_INCREMENT | DataType::UNSIGNED);
      $this->setPrimaryKey($id);
    }
  }

  /**
   * Add created and updated timestamps to schema.
   * @param string $created Created field name.
   * @param string $updated Updated field name.
   */
  public function addTimestamps($created = 'created', $updated = 'updated') {
    if (!$this->_readOnly) {
      $this->$created = DataType::dateTime();
      $this->$updated = DataType::dateTime();
    }
  }

  /**
   * Create validation rules based on types.
   * @param ValidatorBuilder $validator Validator to create rules on.
   */
  public function createValidationRules(ValidatorBuilder $validator) {
    foreach ($this->_fields as $field => $type) {
      $type->createValidationRules($validator->$field);
    }
    foreach ($this->_indexes as $index) {
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
    return array_keys($this->_fields);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->_name;
  }

  /**
   * Create schema.
   */
  protected function createSchema() { }

  /**
   * Add a field to schema.
   * @param string $column Column name.
   * @param array $info Column information.
   */
  public function addField($name, DataType $type) {
    if (!$this->_readOnly) {
      $this->_fields[$name] = $type;
    }
  }
  
  /**
   * Remove a field.
   * @param string $name Field name.
   */
  public function removeField($name) {
    if (!$this->_readOnly) {
      unset($this->_fields[$name]);
    }
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
    $this->_indexes['PRIMARY'] = array(
      'columns' => $columns,
      'unique' => true
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getPrimaryKey() {
    if (!isset($this->_indexes['PRIMARY'])) {
      return array();
    }
    return $this->_indexes['PRIMARY']['columns'];
  }
  
  /**
   * Check if the column is part of the primary key.
   * @param string $column Column name.
   * @return boolean True if part of primary key, false otherwise.
   */
  public function isPrimaryKey($column) {
    if (!isset($this->_indexes['PRIMARY'])) {
      return false;
    }
    return in_array($column, $this->_indexes['PRIMARY']['columns']);
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
    if (isset($this->_indexes[$name])) {
      $this->_indexes[$name]['columns'] = array_merge($this->_indexes[$name]['columns'], $columns);
    }
    else {
      $this->_indexes[$name] = array(
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
    if (isset($this->_indexes[$name])) {
      $columns = array_merge($this->_indexes[$name]['columns'], $columns);
    }
    if (isset($this->_indexes[$name])) {
      $this->_indexes[$name]['columns'] = array_merge($this->_indexes[$name]['columns'], $columns);
    }
    else {
      $this->_indexes[$name] = array(
        'columns' => $columns,
        'unique' => false
      );
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function getIndexes() {
    return $this->_indexes;
  }

  /**
   * {@inheritdoc}
   */
  public function indexExists($name) {
    return isset($this->_indexes[$name]);
  }
  
  /**
   * Remove an index.
   * @param string $name Index name.
   */
  public function removeIndex($name) {
    unset($this->_indexes[$name]);
  }

  /**
   * {@inheritdoc}
   */
  public function getIndex($name) {
    return $this->_indexes[$name];
  }
}
