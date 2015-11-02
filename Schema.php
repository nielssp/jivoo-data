<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

/**
 * Model schema.
 */
interface Schema {
  /**
   * Get name of schema.
   * @return string Name.
   */
  public function getName();
  
  /** 
   * Get list of fields.
   * @return string[] List of field names.
   */
  public function getFields();
  
  /**
   * Get type of field
   * @param string $field Field name.
   * @return DataType Type of field.
   */
  public function getType($field);
  
  /**
   * Get fields of primary key.
   * @return string[] List of field names or empty array if no primary key
   */
  public function getPrimaryKey();

  /**
   * Get names of indexes/keys. 
   * @return string[] Names of keys.
   */
  public function getKeys();
  
  /**
   * Get fields of key.
   * @param string $key Key name.
   * @return string[] List of field names.
   */
  public function getKey($key);
  
  /**
   * Whether key is unique.
   * @param string $key Key name.
   * @return bool True if unique, false otherwise.
   */
  public function isUnique($key);
  
}
