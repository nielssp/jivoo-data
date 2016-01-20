<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Migrations;

use Jivoo\Databases\MigratableDatabase;
use Jivoo\Databases\SchemaBuilder;
use Jivoo\Models\DataType;

/**
 * Base class for migrations.
 */
abstract class Migration {
  /**
   * @var MigratableDatabase Database.
   */
  private $db = null;
  
  /**
   * @var MigrationSchema Schema.
   */
  private $schema = null;

  /**
   * @var bool Whether to ignore exceptions.
   */
  private $ignoreExceptions = false;
  
  /**
   * Construct migration.
   * @param MigratableDatabase $db Database to run migration on.
   * @param MigrationSchema $schema A migration schema.
   */
  public final function __construct(MigratableDatabase $db, MigrationSchema $schema) {
    $this->db = $db;
    $this->schema = $schema;
  }
  
  /**
   * Get a table.
   * @param string $table Table name.
   * @return Table Table.
   */
  public function __get($table) {
    return $this->db->$table;
  }

  /**
   * Whether or not the table exists.
   * @param string $table Table name.
   * @return bool True if table exists.
   */
  public function __isset($table) {
    return isset($this->db->table);
  }
  
  /**
   * Create a table.
   * @param SchemaBuilder $schema Schema for table.
   */
  protected function createTable(SchemaBuilder $schema) {
    try {
      $this->db->createTable($schema);
      $this->schema->createTable($schema);
    }
    catch (\Exception $e) {
      if (!$this->ignoreExceptions) throw $e;
    }
  }

  /**
   * Delete a table.
   * @param string $table Table name.
   */
  protected function dropTable($table) {
    try {
      $this->db->dropTable($table); 
      $this->schema->dropTable($table);
    }
    catch (\Exception $e) {
      if (!$this->ignoreExceptions) throw $e;
    }
  }
  
  /**
   * Add a column to a table.
   * @param string $table Table name.
   * @param string $column Column name.
   * @param DataType $type Column type.
   */
  protected function addColumn($table, $column, DataType $type) {
    try {
      $this->db->addColumn($table, $column, $type);
      $this->schema->addColumn($table, $column, $type); 
    }
    catch (\Exception $e) {
      if (!$this->ignoreExceptions) throw $e;
    }
  }

  /**
   * Delete a column from a table.
   * @param string $table Table name.
   * @param string $column Column nane.
   */
  protected function deleteColumn($table, $column) {
    try {
      $this->db->deleteColumn($table, $column);
      $this->schema->deleteColumn($table, $column);
    }
    catch (\Exception $e) {
      if (!$this->ignoreExceptions) throw $e;
    }
  }

  /**
   * Modify type of a column.
   * @param string $table Table name.
   * @param string $column Column name.
   * @param DataType $type Column type.
   */
  protected function alterColumn($table, $column, DataType $type) {
    try {
      $this->db->alterColumn($table, $column, $type);
      $this->schema->alterColumn($table, $column, $type); 
    }
    catch (\Exception $e) {
      if (!$this->ignoreExceptions) throw $e;
    }
  }

  /**
   * Rename a column.
   * @param string $table Table name.
   * @param string  $column Current column name.
   * @param string $newName New column name.
   */
  protected function renameColumn($table, $column, $newName) {
    try {
      $this->db->renameColumn($table, $column, $newName);
      $this->schema->renameColumn($table, $column, $newName);
    }
    catch (\Exception $e) {
      if (!$this->ignoreExceptions) throw $e;
    }
  }

  /**
   * Create an index.
   * @param string $table Table name.
   * @param string $index Index name.
   * @param array $options Associative array of index options, with keys
   * 'unique' and 'columns'.
   */
  protected function createIndex($table, $index, $options = array()) {
    try {
      $this->db->createIndex($table, $index, $options);
      $this->schema->createIndex($table, $index, $options);
    }
    catch (\Exception $e) {
      if (!$this->ignoreExceptions) throw $e;
    }
  }

  /**
   * Delete an index.
   * @param string $table Table name.
   * @param string $index Index name.
   */
  protected function deleteIndex($table, $index) {
    try {
      $this->db->deleteIndex($table, $index);
      $this->schema->deleteIndex($table, $index); 
    }
    catch (\Exception $e) {
      if (!$this->ignoreExceptions) throw $e;
    }
  }

  /**
   * Alter an index.
   * @param string $table Table name.
   * @param string $index Index name.
   * @param array $options Associative array of index options, with keys
   * 'unique' and 'columns'.
   * @throws \Exception
   */
  protected function alterIndex($table, $index, $options = array()) {
    try {
      $this->alterIndex($table, $index, $options); 
      $this->schema->alterIndex($table, $index, $options);
    }
    catch (\Exception $e) {
      if (!$this->ignoreExceptions) throw $e;
    }
  }
  
  /**
   * Revert changes made by this migration.
   */
  public final function revert() {
    $this->ignoreExceptions = true;
    $this->down();
    $this->ignoreExceptions = false;
  }

  /**
   * Perform database changes.
   */
  public abstract function up();

  /**
   * Undo database changes made by {@see up()}.
   */
  public abstract function down();
  
  //public function up() {
    //$operations = $this->change();
    //foreach ($operations as $operation) {
      //$this->do($operation);
    //}
  //}
  
  //public function down() {
    //$operations = array_reverse($this->change());
    //foreach ($operations as $operation) {
      //$this->undo($operation);
    //}
  //}
  
  /**
   * List of changes. Not implemented.
   * @return array
   */
  protected function change() {
    return array();
  }
}
