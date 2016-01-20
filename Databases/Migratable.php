<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Models\DataType;

/**
 * Interface containing methods for migrating databases.
 */
interface Migratable {
  /**
   * Create a table based on a schema.
   * @param SchemaBuilder $schema Schema.
   */
  public function createTable(SchemaBuilder $schema);
  
  /**
   * Rename a table.
   * @param string $table Table name.
   * @param string $newName New table name. 
   */
  public function renameTable($table, $newName);

  /**
   * Delete a table.
   * @param string $table Table name.
   */
  public function dropTable($table);

  /**
   * Add a column to a table.
   * @param string $table Table name.
   * @param string $column Column name.
   * @param DataType $type Type.
   */
  public function addColumn($table, $column, DataType $type);

  /**
   * Delete a column from a table.
   * @param string $table Table name.
   * @param string $column Column name.
   */
  public function deleteColumn($table, $column);

  /**
   * Alter a column in a table.
   * @param string $table Table name.
   * @param string $column Column name.
   * @param DataType $type Type.
   */
  public function alterColumn($table, $column, DataType $type);
  
  /**
   * Rename a column in a table.
   * @param string $table Table name.
   * @param string $column Column name.
   * @param string $newName New column name.
   */
  public function renameColumn($table, $column, $newName);

  /**
   * Create an index.
   * 
   * Format of options array:
   * <code>
   * array(
   *   'unique' => ..., // Whether or not index is unique (bool)
   *   'columns' => array(...) // List of column names (string[])
   * )
   * </code>
   * 
   * @param string $table Table name.
   * @param string $index Index name.
   * @param array $options Options.
   */
  public function createIndex($table, $index, $options = array());

  /**
   * Delete an index
   * @param string $table Table name.
   * @param string $index Index name.
   */
  public function deleteIndex($table, $index);

  /**
   * Alter an index.
   * 
   * Format of options array:
   * <code>
   * array(
   *   'unique' => ..., // Whether or not index is unique (bool)
   *   'columns' => array(...) // List of column names (string[])
   * )
   * </code>
   * 
   * @param string $table Table name.
   * @param string $index Index name.
   * @param array $options Options.
   */
  public function alterIndex($table, $index, $options = array());
}
