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
class DynamicSchema implements Schema {  
  /**
   * @var string Name of table.
   */
  private $name = 'undefined';
  
  private $text;
  
  private $fields = array();

  /**
   * Constructor
   * @param string $name Name of schema
  */
  public function __construct($name) {
    $this->name = $name;
    $this->text = DataType::text(true);
  }

  /**
   * {@inheritdoc}
   */
  public function __get($field) {
    return $this->text;
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($field) {
    return true;
  }
  
  /**
   * {@inheritdoc}
   */
  public function copy($newName) {
    return new self($newName);
  }
  
  /**
   * {@inheritdoc}
   */
  public function filter($data) {
    $this->fields = array_unique(array_merge(array_keys($data), $this->fields));
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    return $this->fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getPrimaryKey() {
    return array();
  }
  
  /**
   * {@inheritdoc}
   */
  public function getIndexes() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function indexExists($name) {
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function getIndex($name) {
    return null;
  }
}
