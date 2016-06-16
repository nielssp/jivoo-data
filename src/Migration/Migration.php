<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Migration;

use Jivoo\Data\Database\MigratableDatabase;
use Jivoo\Data\DefinitionBuilder;
use Jivoo\Data\DataType;

/**
 * Base class for migrations.
 */
abstract class Migration
{

    /**
     * @var MigratableDatabase Database.
     */
    private $db = null;

    /**
     * @var MigrationDefinition Schema.
     */
    private $schema = null;

    /**
     * @var bool Whether to ignore exceptions.
     */
    private $ignoreExceptions = false;

    /**
     * Construct migration.
     *
     * @param MigratableDatabase $db
     *            Database to run migration on.
     * @param MigrationDefinition $schema
     *            A migration schema.
     */
    final public function __construct(MigratableDatabase $db, MigrationDefinition $schema)
    {
        $this->db = $db;
        $this->schema = $schema;
    }

    /**
     * Get a table.
     *
     * @param string $table
     *            Table name.
     * @return Table Table.
     */
    public function __get($table)
    {
        return $this->db->$table;
    }

    /**
     * Whether or not the table exists.
     *
     * @param string $table
     *            Table name.
     * @return bool True if table exists.
     */
    public function __isset($table)
    {
        return isset($this->db->table);
    }

    /**
     * Create a table.
     *
     * @param DefinitionBuilder $definition
     *            Schema for table.
     */
    protected function createTable(DefinitionBuilder $definition)
    {
        try {
            $this->db->createTable($definition);
            $this->schema->createTable($definition);
        } catch (\Exception $e) {
            if (! $this->ignoreExceptions) {
                throw $e;
            }
        }
    }

    /**
     * Delete a table.
     *
     * @param string $table
     *            Table name.
     */
    protected function dropTable($table)
    {
        try {
            $this->db->dropTable($table);
            $this->schema->dropTable($table);
        } catch (\Exception $e) {
            if (! $this->ignoreExceptions) {
                throw $e;
            }
        }
    }

    /**
     * Add a column to a table.
     *
     * @param string $table
     *            Table name.
     * @param string $column
     *            Column name.
     * @param DataType $type
     *            Column type.
     */
    protected function addColumn($table, $column, DataType $type)
    {
        try {
            $this->db->addColumn($table, $column, $type);
            $this->schema->addColumn($table, $column, $type);
        } catch (\Exception $e) {
            if (! $this->ignoreExceptions) {
                throw $e;
            }
        }
    }

    /**
     * Delete a column from a table.
     *
     * @param string $table
     *            Table name.
     * @param string $column
     *            Column nane.
     */
    protected function deleteColumn($table, $column)
    {
        try {
            $this->db->deleteColumn($table, $column);
            $this->schema->deleteColumn($table, $column);
        } catch (\Exception $e) {
            if (! $this->ignoreExceptions) {
                throw $e;
            }
        }
    }

    /**
     * Modify type of a column.
     *
     * @param string $table
     *            Table name.
     * @param string $column
     *            Column name.
     * @param DataType $type
     *            Column type.
     */
    protected function alterColumn($table, $column, DataType $type)
    {
        try {
            $this->db->alterColumn($table, $column, $type);
            $this->schema->alterColumn($table, $column, $type);
        } catch (\Exception $e) {
            if (! $this->ignoreExceptions) {
                throw $e;
            }
        }
    }

    /**
     * Rename a column.
     *
     * @param string $table
     *            Table name.
     * @param string $column
     *            Current column name.
     * @param string $newName
     *            New column name.
     */
    protected function renameColumn($table, $column, $newName)
    {
        try {
            $this->db->renameColumn($table, $column, $newName);
            $this->schema->renameColumn($table, $column, $newName);
        } catch (\Exception $e) {
            if (! $this->ignoreExceptions) {
                throw $e;
            }
        }
    }

    /**
     * Create a key.
     *
     * @param string $table
     *            Table name.
     * @param string $key
     *            Key name.
     * @param array $options
     *            Associative array of index options, with keys
     *            'unique' and 'columns'.
     */
    protected function createKey($table, $key, $options = array())
    {
        try {
            $this->db->createKey($table, $key, $options);
            $this->schema->createKey($table, $key, $options);
        } catch (\Exception $e) {
            if (! $this->ignoreExceptions) {
                throw $e;
            }
        }
    }

    /**
     * Delete a key.
     *
     * @param string $table
     *            Table name.
     * @param string $key
     *            Key name.
     */
    protected function deleteKey($table, $key)
    {
        try {
            $this->db->deleteKey($table, $key);
            $this->schema->deleteKey($table, $key);
        } catch (\Exception $e) {
            if (! $this->ignoreExceptions) {
                throw $e;
            }
        }
    }

    /**
     * Alter a key.
     *
     * @param string $table
     *            Table name.
     * @param string $key
     *            Ket name.
     * @param array $options
     *            Associative array of index options, with keys
     *            'unique' and 'columns'.
     * @throws \Exception
     */
    protected function alterKey($table, $key, $options = array())
    {
        try {
            $this->alterKey($table, $key, $options);
            $this->schema->alterKey($table, $key, $options);
        } catch (\Exception $e) {
            if (! $this->ignoreExceptions) {
                throw $e;
            }
        }
    }

    /**
     * Revert changes made by this migration.
     */
    final public function revert()
    {
        $this->ignoreExceptions = true;
        $this->down();
        $this->ignoreExceptions = false;
    }

    /**
     * Perform database changes.
     */
    abstract public function up();

    /**
     * Undo database changes made by {@see up()}.
     */
    abstract public function down();
    
    // public function up() {
    // $operations = $this->change();
    // foreach ($operations as $operation) {
    // $this->do($operation);
    // }
    // }
    
    // public function down() {
    // $operations = array_reverse($this->change());
    // foreach ($operations as $operation) {
    // $this->undo($operation);
    // }
    // }
    
    /**
     * List of changes.
     * Not implemented.
     *
     * @return array
     */
    protected function change()
    {
        return array();
    }
}
