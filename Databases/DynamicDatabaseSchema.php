<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Models\Schema;

/**
 * A dynamic database schema (does not require prior knowledge of the database
 * structure). Table schemas are automatically created as instances of
 * {@see DynamicSchema}.
 */
class DynamicDatabaseSchema implements DatabaseSchema {
  /**
   * @var Schema[] Associative array of names and schema.
   */
  private $schemas = array();
  
  /**
   * @var string[] List of table names.
   */
  private $tables = array();

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
    if (!isset($this->schemas[$table]))
      $this->schemas[$table] = new DynamicSchema($table);
    return $this->schemas[$table];
  }

  /**
   * {@inheritdoc}
   */
  public function addSchema(Schema $schema) {
    $name = $schema->getName();
    if (!in_array($name, $this->tables))
      $this->tables[] = $name;
    $this->schemas[$name] = $schema;
  }
}