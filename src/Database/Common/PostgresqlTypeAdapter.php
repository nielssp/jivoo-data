<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Common;

use Jivoo\Databases\MigrationTypeAdapter;
use Jivoo\Models\DataType;
use Jivoo\Databases\SchemaBuilder;
use Jivoo\Core\Utilities;
use Jivoo\Core\Json;
use Jivoo\Databases\TypeException;

/**
 * Type adapter for PostgreSQL database drivers.
 */
class PostgresqlTypeAdapter implements MigrationTypeAdapter {
  /**
   * @var SqlDatabase Database
   */
  private $db;

  /**
   * Construct type adapter.
   * @param SqlDatabaseBase $db Database.
   */
  public function __construct(SqlDatabaseBase $db) {
    $this->db = $db;
  }

  /**
   * {@inheritdoc}
   */
  public function encode(DataType $type, $value) {
    $value = $type->convert($value);
    if (!isset($value)) {
      if ($type->isInteger() and $type->autoIncrement)
        return 'DEFAULT';
      return 'NULL';
    }
    switch ($type->type) {
      case DataType::INTEGER:
        return intval($value);
      case DataType::FLOAT:
        return floatval($value);
      case DataType::BOOLEAN:
        return $value ? 'TRUE' : 'FALSE';
      case DataType::DATE:
        return $this->db->quoteString(gmdate('Y-m-d', $value));
      case DataType::DATETIME:
        return $this->db->quoteString(gmdate('Y-m-d H:i:s', $value));
      case DataType::STRING:
      case DataType::TEXT:
      case DataType::BINARY:
      case DataType::ENUM:
        return $this->db->quoteString($value);
      case DataType::OBJECT:
        return $this->db->quoteString(Json::encode($value));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function decode(DataType $type, $value) {
    if (!isset($value))
      return null;
    switch ($type->type) {
      case DataType::BOOLEAN:
        return $value != 0;
      case DataType::DATE:
      case DataType::DATETIME:
        return strtotime($value . ' UTC');
      case DataType::INTEGER:
        return intval($value);
      case DataType::FLOAT:
        return floatval($value);
      case DataType::STRING:
      case DataType::TEXT:
      case DataType::BINARY:
      case DataType::ENUM:
        return strval($value);
      case DataType::OBJECT:
        return Json::decode($value);
    }
  }

  /**
   * Convert a schema type to a PostgreSQL type.
   * @param DataType $type Type.
   * @return string PostgreSQL type.
   */
  private function fromDataType(DataType $type) {
    $autoIncrement = '';
    switch ($type->type) {
      case DataType::INTEGER:
        $column = '';
        if ($type->size == DataType::BIG)
          $column = 'big';
        else if ($type->size == DataType::SMALL)
          $column = 'small';
        else if ($type->size == DataType::TINY)
          $column = 'small';
        if ($type->autoIncrement)
          $column .='serial';
        else
          $column .= 'int';
        break;
      case DataType::FLOAT:
        $column = 'float';
        break;
      case DataType::STRING:
        $column = 'varchar(' . $type->length . ')';
        break;
      case DataType::BOOLEAN:
        $column = 'boolean';
        break;
      case DataType::BINARY:
        // TODO: use bytea
        $column = 'text';
        break;
      case DataType::DATE:
        $column = 'date';
        break;
      case DataType::DATETIME:
        $column = 'timestamp';
        break;
      case DataType::ENUM:
        // TODO: add support for enums using CREATE TYPE
        $column = 'varchar(255)';
//         $column = "ENUM('" . implode("','", $type->values) . "')";
        break; 
      case DataType::TEXT:
      case DataType::OBJECT:
      default:
        $column = 'text';
        break;
    }
    if ($type->notNull)
      $column .= ' NOT NULL';
    if (isset($type->default))
      $column .= ' DEFAULT ' . $this->encode($type, $type->default);
    return $column . $autoIncrement;
  }
  
  /**
   * Convert output of SHOW COLUMN to DataType.
   * @param array $row Row result.
   * @throws TypeException If type unsupported.
   * @return DataType The type.
   */
  private function toDataType($row) {
    $null = ($row['is_nullable'] != 'NO');
    $default = null;
    if (isset($row['column_default']))
      $default = $row['column_default'];
    
    $type = $row['data_type'];
    if (strpos($type, 'int') !== false) {
      $intFlags = 0;
      if (preg_match('/^nextval\(/', $default) === 1) {
        $intFlags = DataType::AUTO_INCREMENT;
        $default = null;
      }
      else if (isset($default)) {
        $default = intval($default);
      }
      if (strpos($type, 'bigint') !== false)
        return DataType::integer($intFlags | DataType::BIG, $null, $default);
      if (strpos($type, 'smallint') !== false)
        return DataType::integer($intFlags | DataType::SMALL, $null, $default);
      return DataType::integer($intFlags, $null, $default);
    }
    if (strpos($type, 'double') !== false)
      return DataType::float($null, isset($default) ? floatval($default) : null);
    if (strpos($type, 'bool') !== false)
      return DataType::boolean($null, isset($default) ? boolval($default) : null);
    
    if (preg_match("/^'(.*)'::[a-z ]+$/", $default, $matches) === 1)
      $default = $matches[1];
    else
      $default = null;
    
    if (strpos($type, 'character') !== false) {
      $length = $row['character_maximum_length'];
      return DataType::string($length, $null, $default);
    }
    if (strpos($type, 'date') !== false)
      return DataType::date($null, isset($default) ? strtotime($default . ' UTC') : null);
    if (strpos($type, 'timestamp') !== false)
      return DataType::dateTime($null, isset($default) ? strtotime($default . ' UTC') : null);
    if (strpos($type, 'text') !== false)
      return DataType::text($null, $default);
    
    throw new TypeException(tr(
      'Unsupported PostgreSQL type "%1" for column: %2', $row['data_type'], $row['column_name']
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getTableSchema($table) {
    $result = $this->db->rawQuery("SELECT * FROM information_schema.columns WHERE table_name = '" . $this->db->tableName($table) . "'");
    $schema = new SchemaBuilder($table);
    while ($row = $result->fetchAssoc()) {
      $column = $row['column_name'];
      $schema->addField($column, $this->toDataType($row));
    }
    
    $sql = 'SELECT i.relname AS index_name, a.attname AS column_name, indisunique, indisprimary FROM';
    $sql .= ' pg_class t, pg_class i, pg_index ix, pg_attribute a WHERE';
    $sql .= ' t.oid = ix.indrelid AND i.oid = ix.indexrelid AND a.attrelid = t.oid';
    $sql .= " AND a.attnum = ANY(ix.indkey) AND t.relkind = 'r'";
    $sql .= " AND t.relname = '" . $this->db->tableName($table) . "'";
    $result = $this->db->rawQuery($sql);
    $indexes = array();
    while ($row = $result->fetchAssoc()) {
      $index = $row['index_name'];
      $column = $row['column_name'];
      $unique = $row['indisunique'] == 0 ? true : false;
      if (isset($indexes[$index]))
        $indexes[$index]['columns'][] = $column;
      else
        $indexes[$index] = array(
          'columns' => array($column),
          'unique' => $unique
        );
    }
    foreach ($indexes as $name => $index) {
      $name = preg_replace(
        '/^' . preg_quote($this->db->tableName($table) . '_', '/') . '/',
        '', $name, 1, $count
      );
      if ($count == 0)
        continue;
      if ($index['unique'])
        $schema->addUnique($name, $index['columns']);
      else
        $schema->addIndex($name, $index['columns']);
    }
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function tableExists($table) {
    $result = $this->db->rawQuery(
      // TODO: custom schemaname?
      "SELECT 1 FROM pg_catalog.pg_tables WHERE schemaname = 'public' AND tablename = '"
      . $this->db->tableName($table) . "'"
    );
    return $result->hasRows();
  }

  /**
   * {@inheritdoc}
   */
  public function getTables() {
    $prefix = $this->db->tableName('');
    $prefixLength = strlen($prefix);
    $result = $this->db->rawQuery("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'");
    $tables = array();
    while ($row = $result->fetchRow()) {
      $name = $row[0];
      if (substr($name, 0, $prefixLength) == $prefix) {
        $name = substr($name, $prefixLength);
        $tables[] = Utilities::underscoresToCamelCase($name);
      }
    }
    return $tables;
  }

  /**
   * {@inheritdoc}
   */
  public function createTable(SchemaBuilder $schema) {
    $table = $schema->getName();
    $sql = 'CREATE TABLE ' . $this->db->quoteModel($table) . ' (';
    $columns = $schema->getFields();
    $first = true;
    foreach ($columns as $column) {
      $type = $schema->$column;
      if (!$first)
        $sql .= ', ';
      else
        $first = false;
      $sql .= $this->db->quoteField($column);
      $sql .= ' ' . $this->fromDataType($type);
    }
    $pk = $schema->getPrimaryKey();
    if (count($pk) > 0) {
      $sql .= ', CONSTRAINT "' . $this->db->tableName($table) . '_PRIMARY" PRIMARY KEY (';
      $pk = array_map(array($this->db, 'quoteField'), $pk);
      $sql .= implode(', ', $pk) . ')';
    }
    $sql .= ')';
    $this->db->rawQuery($sql);
    foreach ($schema->getIndexes() as $index => $options) {
      if ($index == 'PRIMARY')
        continue;
      $sql = 'CREATE';
      if ($options['unique'])
        $sql .= ' UNIQUE';
      $sql .= ' INDEX "' . $this->db->tableName($table) . '_' . $index . '"';
      $sql .= ' ON ' . $this->db->quoteModel($table);
      $columns = array_map(array($this->db, 'quoteField'), $options['columns']);
      $sql .= ' (' . implode(', ', $columns) . ')';
      $this->db->rawQuery($sql);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function renameTable($table, $newName) {
    $sql = 'ALTER TABLE ' . $this->db->quoteModel($table) . ' RENAME TO ';
    $sql .= $this->db->quoteModel($newName);
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function dropTable($table) {
    $sql = 'DROP TABLE ' . $this->db->quoteModel($table);
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function addColumn($table, $column, DataType $type) {
    $sql = 'ALTER TABLE ' . $this->db->quoteModel($table);
    $sql .= ' ADD ' . $this->db->quoteField($column);
    $sql .= ' ' . $this->fromDataType($type);
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteColumn($table, $column) {
    $sql = 'ALTER TABLE ' . $this->db->quoteModel($table);
    $sql .= ' DROP ' . $this->db->quoteField($column);
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function alterColumn($table, $column, DataType $type) {
    // TODO: fix
    $sql = 'ALTER TABLE ' . $this->db->quoteModel($table);
    $sql .= ' ALTER ' . $this->db->quoteField($column);
    $sql .= ' TYPE ' . $this->fromDataType($type);
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function renameColumn($table, $column, $newName) {
    $sql = 'ALTER TABLE ' . $this->db->quoteModel($table);
    $sql .= ' RENAME ' . $this->db->quoteField($column);
    $sql .= ' TO ' . $this->db->quoteField($newName);
    $type = $this->db->$table->getSchema()->$column;
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function createIndex($table, $index, $options = array()) {
    $columns = array_map(array($this->db, 'quoteField'), $options['columns']);
    $columns = '(' . implode(', ', $columns) . ')';
    
    if ($index == 'PRIMARY') {
      $sql = 'ALTER TABLE ' . $this->db->quoteModel($table);
      $sql .= 'ADD CONSTRAINT "' . $this->db->tableName($table) . '_PRIMARY" PRIMARY KEY ' . $columns;
      $this->db->rawQuery($sql);
      return;
    }
    $sql = 'CREATE';
    if ($options['unique'])
      $sql .= ' UNIQUE';
    $sql .= ' INDEX "' . $this->db->tableName($table) . '_' . $index . '"';
    $sql .= ' ON ' . $this->db->quoteModel($schema->getName());
    $sql .= ' ' . $columns;
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteIndex($table, $index) {
    if ($index == 'PRIMARY') {
      $sql = 'ALTER TABLE ' . $this->db->quoteModel($table);
      $sql .= 'DROP CONSTRAINT "' . $this->db->tableName($table) . '_PRIMARY"';
      $this->db->rawQuery($sql);
      return;
    }

    $sql = 'DROP INDEX ';
    $sql .= '"' . $this->db->tableName($table) . '_' . $index . '"';
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function alterIndex($table, $index, $options = array()) {
    try {
      $this->db->beginTransaction();
      $this->deleteIndex($table, $index);
      $this->createIndex($table, $index, $options);
      $this->db->commit();
    }
    catch (\Exception $e) {
      $this->db->rollback();
      throw $e;
    }
  }
}
