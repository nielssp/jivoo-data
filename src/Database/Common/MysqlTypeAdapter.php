<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database\Common;

use Jivoo\Data\Database\MigrationTypeAdapter;
use Jivoo\Data\DataType;
use Jivoo\Data\DefinitionBuilder;
use Jivoo\Utilities;
use Jivoo\Json;
use Jivoo\Data\Database\TypeException;

/**
 * Type adapter for MySQL database drivers.
 */
class MysqlTypeAdapter implements MigrationTypeAdapter
{

    /**
     * @var SqlDatabase Database
     */
    private $db;

    /**
     * Construct type adapter.
     *
     * @param SqlDatabase $db
     *            Database.
     */
    public function __construct(SqlDatabase $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(DataType $type, $value)
    {
        $value = $type->convert($value);
        if (! isset($value)) {
            return 'NULL';
        }
        switch ($type->type) {
            case DataType::INTEGER:
                return intval($value);
            case DataType::FLOAT:
                return floatval($value);
            case DataType::BOOLEAN:
                return $value ? 1 : 0;
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
    public function decode(DataType $type, $value)
    {
        if (! isset($value)) {
            return null;
        }
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
     * Convert a schema type to a MySQL type.
     *
     * @param DataType $type
     *            Type.
     * @return string MySQL type.
     */
    private function fromDataType(DataType $type)
    {
        $autoIncrement = '';
        switch ($type->type) {
            case DataType::INTEGER:
                if ($type->size == DataType::BIG) {
                    $column = 'BIGINT';
                } elseif ($type->size == DataType::SMALL) {
                        $column = 'SMALLINT';
                } elseif ($type->size == DataType::TINY) {
                        $column = 'TINYINT';
                } else {
                    $column = 'INT';
                }
                if ($type->unsigned) {
                    $column .= ' UNSIGNED';
                }
                if ($type->serial) {
                    $autoIncrement = ' AUTO_INCREMENT';
                }
                break;
            case DataType::FLOAT:
                $column = 'DOUBLE';
                break;
            case DataType::STRING:
                $column = 'VARCHAR(' . $type->length . ')';
                break;
            case DataType::BOOLEAN:
                $column = 'TINYINT';
                break;
            case DataType::BINARY:
                $column = 'BLOB';
                break;
            case DataType::DATE:
                $column = 'DATE';
                break;
            case DataType::DATETIME:
                $column = 'DATETIME';
                break;
            case DataType::ENUM:
                $column = "ENUM('" . implode("','", $type->values) . "')";
                break;
            case DataType::TEXT:
            case DataType::OBJECT:
            default:
                $column = 'TEXT';
                break;
        }
        if ($type->notNull) {
            $column .= ' NOT';
        }
        $column .= ' NULL';
        if (isset($type->default)) {
            $column .= ' DEFAULT ' . $this->encode($type, $type->default);
        }
        return $column . $autoIncrement;
    }

    /**
     * Convert output of SHOW COLUMN to DataType.
     *
     * @param array $row
     *            Row result.
     * @throws TypeException If type unsupported.
     * @return DataType The type.
     */
    private function toDataType($row)
    {
        $null = (isset($row['Null']) and $row['Null'] != 'NO');
        $default = null;
        if (isset($row['Default'])) {
            $default = $row['Default'];
        }
        
        if (preg_match('/enum\((.+)\)/i', $row['Type'], $matches) === 1) {
            preg_match_all('/\'([^\']+)\'/', $matches[1], $matches);
            $values = $matches[1];
            return DataType::enum($values, $null, $default);
        }
        preg_match('/ *([^ (]+) *(\(([0-9]+)\))? *(unsigned)? *?/i', $row['Type'], $matches);
        $actualType = strtolower($matches[1]);
        $length = isset($matches[3]) ? intval($matches[3]) : 0;
        $intFlags = 0;
        if (isset($matches[4])) {
            $intFlags |= DataType::UNSIGNED;
        }
        if (strpos($row['Extra'], 'auto_increment') !== false) {
            $intFlags |= DataType::SERIAL;
        }
        switch ($actualType) {
            case 'bigint':
                $intFlags |= DataType::BIG;
                return DataType::integer($intFlags, $null, isset($default) ? intval($default) : null);
            case 'smallint':
                $intFlags |= DataType::SMALL;
                return DataType::integer($intFlags, $null, isset($default) ? intval($default) : null);
            case 'tinyint':
                $intFlags |= DataType::TINY;
                return DataType::integer($intFlags, $null, isset($default) ? intval($default) : null);
            case 'int':
                return DataType::integer($intFlags, $null, isset($default) ? intval($default) : null);
            case 'double':
                return DataType::float($null, isset($default) ? floatval($default) : null);
            case 'varchar':
                return DataType::string($length, $null, $default);
            case 'blob':
                return DataType::binary($null, $default);
            case 'date':
                return DataType::date($null, isset($default) ? strtotime($default . ' UTC') : null);
            case 'datetime':
                return DataType::dateTime($null, isset($default) ? strtotime($default . ' UTC') : null);
            case 'text':
                return DataType::text($null, $default);
        }
        throw new TypeException(tr('Unsupported MySQL type for column: %1', $row['Field']));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($table)
    {
        $result = $this->db->query('SHOW COLUMNS FROM `' . $this->db->tableName($table) . '`');
        $schema = new DefinitionBuilder($table);
        while ($row = $result->fetchAssoc()) {
            $column = $row['Field'];
            $schema->$column = $this->toDataType($row);
        }
        $result = $this->db->query('SHOW INDEX FROM `' . $this->db->tableName($table) . '`');
        $indexes = array();
        while ($row = $result->fetchAssoc()) {
            $index = $row['Key_name'];
            $column = $row['Column_name'];
            $unique = $row['Non_unique'] == 0 ? true : false;
            if (isset($indexes[$index])) {
                $indexes[$index]['columns'][] = $column;
            } else {
                $indexes[$index] = array(
                    'columns' => array(
                        $column
                    ),
                    'unique' => $unique
                );
            }
        }
        foreach ($indexes as $name => $index) {
            if ($index['unique']) {
                $schema->addUnique($index['columns'], $name);
            } else {
                $schema->addKey($index['columns'], $name);
            }
        }
        return $schema;
    }

    /**
     * {@inheritdoc}
     */
    public function tableExists($table)
    {
        $result = $this->db->query('SHOW TABLES LIKE "' . $this->db->tableName($table) . '"');
        return $result->hasRows();
    }

    /**
     * {@inheritdoc}
     */
    public function getTables()
    {
        $prefix = $this->db->tableName('');
        $prefixLength = strlen($prefix);
        $result = $this->db->query('SHOW TABLES');
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
    public function createTable($table, \Jivoo\Data\Definition $definition)
    {
        $sql = 'CREATE TABLE `' . $this->db->tableName($table) . '` (';
        $columns = $definition->getFields();
        $first = true;
        foreach ($columns as $column) {
            $type = $definition->getType($column);
            if (! $first) {
                $sql .= ', ';
            } else {
                $first = false;
            }
            $sql .= $column;
            $sql .= ' ' . $this->fromDataType($type);
        }
        foreach ($definition->getKeys() as $key) {
            $columns = $definition->getKey($key);
            $sql .= ', ';
            if ($key == 'PRIMARY') {
                $sql .= 'PRIMARY KEY (';
            } elseif ($definition->isUnique($key)) {
                $sql .= 'UNIQUE (';
            } else {
                $sql .= 'INDEX (';
            }
            $sql .= implode(', ', $columns) . ')';
        }
        $sql .= ') CHARACTER SET utf8';
        $this->db->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function renameTable($table, $newName)
    {
        $sql = 'RENAME TABLE `' . $this->db->tableName($table) . '` TO `';
        $sql .= $this->db->tableName($newName) . '`';
        $this->db->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function dropTable($table)
    {
        $sql = 'DROP TABLE `' . $this->db->tableName($table) . '`';
        $this->db->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function addColumn($table, $column, DataType $type)
    {
        $sql = 'ALTER TABLE `' . $this->db->tableName($table) . '` ADD ' . $column;
        $sql .= ' ' . $this->fromDataType($type);
        $this->db->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteColumn($table, $column)
    {
        $sql = 'ALTER TABLE `' . $this->db->tableName($table) . '` DROP ' . $column;
        $this->db->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function alterColumn($table, $column, DataType $type)
    {
        $sql = 'ALTER TABLE `' . $this->db->tableName($table) . '` CHANGE ' . $column . ' ' . $column;
        $sql .= ' ' . $this->fromDataType($type);
        $this->db->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function renameColumn($table, $column, $newName)
    {
        $type = $this->db->getDefinition()->getDefinition($table)->getType($column);
        $sql = 'ALTER TABLE `' . $this->db->tableName($table) . '` CHANGE ' . $column . ' ' . $newName;
        $sql .= ' ' . $this->fromDataType($type);
        $this->db->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function createIndex($table, $index, array $columns, $unique = true)
    {
        $sql = 'ALTER TABLE `' . $this->db->tableName($table) . '`';
        if ($index == 'PRIMARY') {
            $sql .= ' ADD PRIMARY KEY';
        } elseif ($unique) {
            $sql .= ' ADD UNIQUE ' . $index;
        } else {
            $sql .= ' ADD INDEX ' . $index;
        }
        $sql .= ' (';
        $sql .= implode(', ', $columns);
        $sql .= ')';
        $this->db->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex($table, $index)
    {
        $sql = 'ALTER TABLE `' . $this->db->tableName($table) . '`';
        if ($index == 'PRIMARY') {
            $sql .= ' DROP PRIMARY KEY';
        } else {
            $sql .= ' DROP INDEX ' . $index;
        }
        $this->db->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function alterIndex($table, $index, array $columns, $unique = true)
    {
        $sql = 'ALTER TABLE `' . $this->db->tableName($table) . '`';
        if ($index == 'PRIMARY') {
            $sql .= ' DROP PRIMARY KEY';
        } else {
            $sql .= ' DROP INDEX ' . $index;
        }
        $sql .= ', ';
        if ($index == 'PRIMARY') {
            $sql .= 'ADD PRIMARY KEY';
        } elseif ($unique) {
            $sql .= 'ADD UNIQUE ' . $index;
        } else {
            $sql .= 'ADD INDEX ' . $index;
        }
        $sql .= ' (';
        $sql .= implode(', ', $columns);
        $sql .= ')';
        $this->db->execute($sql);
    }
}
