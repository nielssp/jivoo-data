<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Migrations;

use Jivoo\Databases\DatabaseSchema;
use Jivoo\Databases\Migratable;
use Jivoo\Databases\MigratableDatabase;
use Jivoo\Databases\SchemaBuilder;
use Jivoo\Models\DataType;
use Jivoo\Models\Schema;

/**
 * A modifiable database schema for use with migrations.
 */
class MigrationSchema implements DatabaseSchema, Migratable {
  /**
   * @var DatabaseSchema Target schema.
   */
  private $targetSchema;
  
  /**
   * @var MigratableDatabase Database.
   */
  private $db;
  
  /**
   * @var Schema[] List of table schemas
   */
  private $schemas = array();
  
  /**
   * @var string[] List of table names.
   */
  private $tables = array();
  /**
   * Construct migration schema.
   * @param MigratableDatabase The database to migrate.
   */
  public function __construct(MigratableDatabase $db) {
    $this->db = $db;
    $this->targetSchema = $db->getSchema();
    $db->refreshSchema();
    $current = $db->getSchema();
    foreach ($current->getTables() as $table) {
      $this->tables[] = $table;
      $this->schemas[$table] = $current->getSchema($table);
    }
    $db->setSchema($this);
  }
  
  /**
   * Finalize migration, sets target schema on database.
   */
  public function finalize() {
    $this->db->setSchema($this->targetSchema);
  }
  
  /**
   * Updates the schema of the associated database to match this migration
   * schema.
   */
  private function reload() {
    $this->db->setSchema($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getTables() {
    return $this->tables;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema($table) {
    if (isset($this->schemas[$table]))
      return $this->schemas[$table];
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function addSchema(Schema $schema) {
    $this->schemas[$schema->getName()] = $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function createTable(SchemaBuilder $schema) {
    $table = $schema->getName();
    $this->tables[] = $table;
    $this->schemas[$table] = $schema;
    $this->reload();
  }

  /**
   * {@inheritdoc}
   */
  public function renameTable($table, $newName) {
    $this->tables = array_diff($this->tables, array($table));
    $schema = $this->schemas[$table];
    // TODO: Change name somehow...
    unset($this->schemas[$table]);
    $this->schemas[$newName] = $schema;
    $this->reload();
  }

  /**
   * {@inheritdoc}
   */
  public function dropTable($table) {
    $this->tables = array_diff($this->tables, array($table));
    unset($this->schemas[$table]);
    $this->reload();
  }

  /**
   * {@inheritdoc}
   */
  public function addColumn($table, $column, DataType $type) {
    $this->schemas[$table]->$column = $type;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteColumn($table, $column) {
    unset($this->schemas[$table]->$column);
  }

  /**
   * {@inheritdoc}
   */
  public function alterColumn($table, $column, DataType $type) {
    $this->schemas[$table]->$column = $type;
  }
  
  public function renameColumn($table, $column, $newName) {
    $type = $this->schemas[$table]->$column;
    unset($this->schemas[$table]->$column);
    $this->schemas[$table]->$newName = $type;
  }

  /**
   * {@inheritdoc}
   */
  public function createIndex($table, $index, $options = array()) {
    if ($options['unique'])
      $this->schemas[$table]->addUnique($index, $options['columns']);
    else
      $this->schemas[$table]->addIndex($index, $options['columns']);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteIndex($table, $index) {
    $this->schemas[$table]->removeIndex($index);
  }

  /**
   * {@inheritdoc}
   */
  public function alterIndex($table, $index, $options = array()) {
    $this->delteIndex($table, $index);
    $this->createIndex($table, $index, $options);
  }
}