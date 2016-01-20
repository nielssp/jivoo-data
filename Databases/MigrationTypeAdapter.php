<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

/**
 * A type and migration adapter.
 */
interface MigrationTypeAdapter extends Migratable, TypeAdapter {
  /**
   * Whether or not a table exists.
   * @param string $table Table name.
   * @return bool True if table exists, false otherwise.
   */
  public function tableExists($table);
  
  /**
   * Get tables.
   * @return string[] List of table names.
   */
  public function getTables();
  
  /**
   * Get table schema.
   * @param string $table Table name.
   * @return Schema Schema.
   */
  public function getTableSchema($table);
}
