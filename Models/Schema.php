<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models;

/**
 * Model schema.
 */
interface Schema {
  /**
   * Get type of field.
   * @param string $field Field name.
   * @return DataType Type of field.
   */
  public function __get($field);

  /**
   * Whether or not a field exists in schema.
   * @param string $field Field name.
   * @return bool True if it does, false otherwise.
   */
  public function __isset($field);
  
  /**
   * Make a copy of this schema with a new name.
   * @param string $newName New name.
   * @return Schema New schema.
   */
  public function copy($newName);

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
   * Process data before creating record object.
   * @param array $data Record data.
   * @return array Record data.
   */
  public function filter($data);

  /**
   * Get fields of primary key.
   * @return string[] List of field names or empty array if no primary key
   */
  public function getPrimaryKey();

  /**
   * Get indexes. The 'PRIMARY'-index is the primary key.
   * 
   * The returned array is of the following format:
   * <code>
   * array(
   *   'indexname' => array(
   *     'fields' => array('fieldname1', 'fieldname'),
   *     'unique' => true
   *   )
   * )
   * </code>
   * 
   * @return array Associative array of index names and info.
   */
  public function getIndexes();

  /**
   * Check whether or not an index exists.
   * @param string $name Index name.
   * @return bool True if index exists.
   */
  public function indexExists($name);
  
  /**
   * Get information about an index.
   * @param string $name Index name.
   * @return array Associative array with two keys: 'fields' is a list of
   * field names and 'unique' is a boolean.
   */
  public function getIndex($name);
  
}
