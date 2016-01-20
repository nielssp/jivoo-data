<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Core\Module;
use Jivoo\Core\App;
use Jivoo\Models\DataType;

/**
 * A database driver that can be loaded by the {@see Databases} module.
 */
abstract class LoadableDatabase extends Module implements MigratableDatabase {
  /**
   * @var DatabaseSchema Schema.
   */
  private $schema;
  
  /**
   * @var string[] List of table names.
   */
  private $tableNames;
  
  /**
   * @var MigrationTypeAdapter Migration adapter.
   */
  private $migrationAdapter;
  
  /**
   * @var Table[] Tables.
   */
  private $tables;
  
  /**
   * Construct database.
   * @param DatabaseSchema $schema Database schema.
   * @param array $options Associative array of options for driver.
   */
  public final function __construct(DatabaseSchema $schema, $options = array()) {
    parent::__construct();
    $this->schema = $schema;
    $this->init($options);
    $this->migrationAdapter = $this->getMigrationAdapter();
    $this->tableNames = $this->getTables();
    foreach ($this->tableNames as $table) {
      $this->tables[$table] = $this->getTable($table);
    }
  }

  /**
   * Get table.
   * @param string $table Table name.
   * @return Table Table.
   */
  public function __get($table) {
    if (!isset($this->tables[$table])) {
      throw new InvalidTableException(
        tr('Table not found: "%1"', $table)
      );
    }
    return $this->tables[$table];
  }
  
  /**
   * Whether or not table exists.
   * @param string $table Table name.
   * @return bool True if table exists.
   */
  public function __isset($table) {
    return isset($this->tables[$table]);
  }
  
  /**
   * Database driver initialization.
   * @param array $options Associative array of options for driver.
   * @throws ConnectionException If connection fails.
   */
  protected abstract function init($options);
  
  /**
   * Get migration and type adapter.
   * @return MigrationTypeAdapter Migration and type adapter.
   */
  protected abstract function getMigrationAdapter();
  
  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return $this->schema;
  }

  /**
   * {@inheritdoc}
   */
  public function setSchema(DatabaseSchema $schema) {
    $this->schema = $schema;
    foreach ($schema->getTables() as $table) {
      $tableSchema = $schema->getSchema($table);
      if (!in_array($table, $this->tableNames)) {
        $this->tableNames[] = $table;
        $this->tables[$table] = $this->getTable($table);
      }
      $this->$table->setSchema($tableSchema);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function refreshSchema() {
    $tables = array_intersect($this->schema->getTables(), $this->tableNames);
    $this->schema = new DatabaseSchemaBuilder();
    foreach ($tables as $table) {
      $schema = $this->getTableSchema($table);
      $this->schema->addSchema($schema);
      $this->$table->setSchema($schema);
    }
  }

  /**
   * Get tables.
   * @return string[] List of table names.
   */
  public function getTables() {
    return $this->migrationAdapter->getTables();
  }

  /**
   * Get table schema.
   * @param string $table Table name.
   * @return Schema Schema.
   */
  public function getTableSchema($table) {
    return $this->migrationAdapter->getTableSchema($table);
  }

  /**
   * {@inheritdoc}
   */
  public function createTable(SchemaBuilder $schema) {
    $this->migrationAdapter->createTable($schema);
    $this->schema->addSchema($schema);
    $table = $schema->getName();
    $this->tableNames[] = $table; 
    $this->tables[$table] = $this->getTable($table);
  }

  /**
   * {@inheritdoc}
   */
  public function renameTable($table, $newName) {
    $this->migrationAdapter->renametable($table, $newName);
  }

  /**
   * {@inheritdoc}
   */
  public function dropTable($table) {
    $this->migrationAdapter->dropTable($table);
  }

  /**
   * {@inheritdoc}
   */
  public function addColumn($table, $column, DataType $type) {
    $this->migrationAdapter->addColumn($table, $column, $type);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteColumn($table, $column) {
    $this->migrationAdapter->deleteColumn($table, $column);
  }

  /**
   * {@inheritdoc}
   */
  public function alterColumn($table, $column, DataType $type) {
    $this->migrationAdapter->alterColumn($table, $column, $type);
  }

  /**
   * {@inheritdoc}
   */
  public function renameColumn($table, $column, $newName) {
    $this->migrationAdapter->renameColumn($table, $column, $newName);
  }

  /**
   * {@inheritdoc}
   */
  public function createIndex($table, $index, $options = array()) {
    $this->migrationAdapter->createIndex($table, $index, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteIndex($table, $index) {
    $this->migrationAdapter->deleteIndex($table, $index);
  }

  /**
   * {@inheritdoc}
   */
  public function alterIndex($table, $index, $options = array()) {
    $this->migrationAdapter->alterIndex($table, $index, $options);
  }
} 
