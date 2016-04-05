<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database;

use Jivoo\Data\DataType;

/**
 * A database driver that can be loaded by the {@see Loader}.
 */
abstract class LoadableDatabase implements MigratableDatabase
{

    /**
     * @var DatabaseDefinition Schema.
     */
    private $definition;

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
     *
     * @param DatabaseDefinition $definition
     *            Database definition.
     * @param array $options
     *            Associative array of options for driver.
     */
    final public function __construct(DatabaseDefinition $definition, $options = array())
    {
        $this->definition = new DatabaseDefinitionBuilder($definition);
        $this->init($options);
        $this->migrationAdapter = $this->getMigrationAdapter();
        $this->tableNames = $this->getTables();
        foreach ($this->tableNames as $table) {
            $this->tables[$table] = $this->getTable($table);
        }
    }

    /**
     * Get table.
     *
     * @param string $table
     *            Table name.
     * @return Table Table.
     */
    public function __get($table)
    {
        if (! isset($this->tables[$table])) {
            throw new InvalidTableException(tr('Table not found: "%1"', $table));
        }
        return $this->tables[$table];
    }

    /**
     * Whether or not table exists.
     *
     * @param string $table
     *            Table name.
     * @return bool True if table exists.
     */
    public function __isset($table)
    {
        return isset($this->tables[$table]);
    }

    /**
     * Database driver initialization.
     *
     * @param array $options
     *            Associative array of options for driver.
     * @throws ConnectionException If connection fails.
     */
    abstract protected function init($options);

    /**
     * Get migration and type adapter.
     *
     * @return MigrationTypeAdapter Migration and type adapter.
     */
    abstract protected function getMigrationAdapter();
    
    /**
     * {@inheritdoc}
     */
    public function tableExists($table)
    {
        return isset($this->tables[$table]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefinition(DatabaseDefinition $definition)
    {
        $this->definition = $definition;
        foreach ($definition->getTables() as $table) {
            $tableDefinition = $definition->getDefinition($table);
            if (! in_array($table, $this->tableNames)) {
                $this->tableNames[] = $table;
                $this->tables[$table] = $this->getTable($table);
            }
            $this->$table->setDefinition($tableDefinition);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function refreshDefinition()
    {
        $tables = array_intersect($this->definition->getTables(), $this->tableNames);
        $this->definition = new DatabaseDefinitionBuilder();
        foreach ($tables as $table) {
            $definition = $this->getTableDefinition($table);
            $this->definition->addDefinition($definition);
            $this->$table->setDefinition($definition);
        }
    }

    /**
     * Get tables.
     *
     * @return string[] List of table names.
     */
    public function getTables()
    {
        return $this->migrationAdapter->getTables();
    }

    /**
     * Get table definition.
     *
     * @param string $table
     *            Table name.
     * @return \Jivoo\Data\Definition Definition.
     */
    public function getTableDefinition($table)
    {
        return $this->migrationAdapter->getTableDefinition($table);
    }

    /**
     * {@inheritdoc}
     */
    public function createTable($table, \Jivoo\Data\Definition $definition = null)
    {
        if (! isset($definition)) {
            $definition = $this->definition->getDefinition($table);
        }
        $this->migrationAdapter->createTable($table, $definition);
        $this->definition->addDefinition($table, $definition);
        $table = $definition->getName();
        $this->tableNames[] = $table;
        $this->tables[$table] = $this->getTable($table);
    }

    /**
     * {@inheritdoc}
     */
    public function renameTable($table, $newName)
    {
        $this->migrationAdapter->renametable($table, $newName);
    }

    /**
     * {@inheritdoc}
     */
    public function dropTable($table)
    {
        $this->migrationAdapter->dropTable($table);
    }

    /**
     * {@inheritdoc}
     */
    public function addColumn($table, $column, DataType $type)
    {
        $this->migrationAdapter->addColumn($table, $column, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteColumn($table, $column)
    {
        $this->migrationAdapter->deleteColumn($table, $column);
    }

    /**
     * {@inheritdoc}
     */
    public function alterColumn($table, $column, DataType $type)
    {
        $this->migrationAdapter->alterColumn($table, $column, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function renameColumn($table, $column, $newName)
    {
        $this->migrationAdapter->renameColumn($table, $column, $newName);
    }

    /**
     * {@inheritdoc}
     */
    public function createIndex($table, $index, $options = array())
    {
        $this->migrationAdapter->createIndex($table, $index, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex($table, $index)
    {
        $this->migrationAdapter->deleteIndex($table, $index);
    }

    /**
     * {@inheritdoc}
     */
    public function alterIndex($table, $index, $options = array())
    {
        $this->migrationAdapter->alterIndex($table, $index, $options);
    }
}
