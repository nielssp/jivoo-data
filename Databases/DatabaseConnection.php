<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Models\Model;

/**
 * A wrapper for another database driver.
 */
class DatabaseConnection implements Database {
  /**
   * @var Table[] Tabbles.
   */
  private $tables = array();
  
  /**
   * @var Database Database.
   */
  private $connection;
  
  /**
   * @var DatabaseSchema Database schema.
   */
  private $schema;

  /**
   * Construct database connection.
   * @param Database $database Database.
   */
  public function __construct(Database $database) {
    $this->connection = $database;
    $this->schema = $database->getSchema();
  }

  /**
   * Get table.
   * @param string $table Table name.
   * @return Table Table.
   */
  public function __get($table) {
    if (isset($this->tables[$table]))
      return $this->tables[$table];
    if (isset($this->connection->$table)) {
      $this->tables[$table] = $this->connection->$table;
      return $this->tables[$table];
    }
    return null;
  }

  /**
   * Whether or not table exists.
   * @param string $table Table name.
   * @return bool True if table exists.
   */
  public function __isset($table) {
    if (isset($this->tables[$table]))
      return true;
    if (isset($this->connection->$table)) {
      $this->tables[$table] = $this->connection->$table;
      return true;
    }
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function __set($table, Model $model) {
    $this->tables[$table] = $model;
  }

  /**
   * {@inheritdoc}
   */
  public function __unset($table) {
    unset($this->tables[$table]);
  }

  /**
   * Get wrapped database.
   * @return Database Database.
   */
  public function getConnection() {
    return $this->connection;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return $this->schema;
  }
  
  /**
   * Refresh schema.
   */
  public function refreshSchema() {
    $this->connection->refreshSchema();
    $this->schema = $this->connection->getSchema();
  }

  /**
   * {@inheritdoc}
   */
  public function close() {
    $this->connection->close();
  }

  /**
   * {@inheritdoc}
   */
  public function beginTransaction() {
    $this->connection->beginTransaction();
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    $this->connection->commit();
  }

  /**
   * {@inheritdoc}
   */
  public function rollback() {
    $this->connection->rollback();
  }
}
