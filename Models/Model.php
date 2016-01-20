<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models;

/**
 * A more advanced extension of {@see BasicModel}.
 */
interface Model extends Selection\Selection, BasicModel {
  /**
   * Get shcmea of model.
   * @return Schema Schema for model.
   */
  public function getSchema();

  /**
   * Retrieve primary key if it is an auto incrementing integer
   * @return string|null Name of primary key if there is
   * only a single primary key and it is auto incrementing,
   * otherwise null.
   */
  public function getAiPrimaryKey();

  /**
   * Get validator for model.
   * @return Validator Validator for model.
   */
  public function getValidator();

  /**
   * Determine if the field is virtual. Virtual fields are not allowed in
   * selections.
   * @param string $field Field name.
   * @return bool True if virtual.
   */
  public function isVirtual($field);

  /**
   * Make a selection that selects a single record.
   * @param Record $record A record.
   * @return Selection A selection.
  */
  public function selectRecord(Record $record);

  /**
   * Make a selection that selects everything except for a single record.
   * @param Record $record A record.
   * @return Selection A selection.
   */
  public function selectNotRecord(Record $record);

  /**
   * Find a record by its primary key. If the primary key
   * consists of multiple fields, this function expects a
   * parameter for each field (in alphabetical order).
   * @param mixed $primary Value of primary key.
   * @param mixed ...$primary For multifield primary key.
   * @return Record|null A single matching record or null if it doesn't exist.
   * @throws InvalidSelectionException If number of parameters does not
   * match size of primary key.
   */
  public function find($primary);

  /**
   * Convert model to another type (for the purpose of joining).
   * @param string $class Name of model class to convert to.
   * @return Model|null New instance or null if not possible.
   */
  public function asInstanceOf($class);
  
  /**
   * Create a record.
   * @param array $data Associative array of record data.
   * @param string[]|null $allowedFields List of allowed fields (null for all
   * fields allowed), fields that are not allowed (or not in the model) will be
   * ignored.
   * @return Record A record.
   */
  public function create($data = array(), $allowedFields = null);

  /**
   * Insert data directly into model.
   * @param array $data Associative array of record data.
   * @param bool $replace Whether to replace rows on conflict.
   * @return int|null Last insert id if any.
   */
  public function insert($data, $replace = false);

  /**
   * Insert multiple data records directly into model. Each record-array MUST cotain the
   * same columns and order of columns.
   * @param array[] $records List of associative arrays of record data.
   * @param bool $replace Whether to replace rows on conflict.
   * @return int|null Last insert id if any.
   */
  public function insertMultiple($records, $replace = false);
}
