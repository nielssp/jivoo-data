<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Migration;

use Jivoo\Data\Database\DatabaseDefinition;
use Jivoo\Data\Database\Migratable;
use Jivoo\Data\Database\MigratableDatabase;
use Jivoo\Data\Database\SchemaBuilder;
use Jivoo\Models\DataType;
use Jivoo\Models\Schema;

/**
 * A modifiable database schema for use with migrations.
 */
class MigrationDefinition implements DatabaseDefinition, Migratable
{

    /**
     * @var DatabaseDefinition Target schema.
     */
    private $targetDefinition;

    /**
     * @var MigratableDatabase Database.
     */
    private $db;

    /**
     * @var Definition[] List of table schemas
     */
    private $definitions = array();

    /**
     * @var string[] List of table names.
     */
    private $tables = array();

    /**
     * Construct migration schema.
     *
     * @param
     *            MigratableDatabase The database to migrate.
     */
    public function __construct(MigratableDatabase $db)
    {
        $this->db = $db;
        $this->targetDefinition = $db->getDefinition();
        $db->refreshDefinition();
        $current = $db->getDefinition();
        foreach ($current->getTables() as $table) {
            $this->tables[] = $table;
            $this->definitions[$table] = $current->getDefinition($table);
        }
        $db->setDefinition($this);
    }

    /**
     * Finalize migration, sets target schema on database.
     */
    public function finalize()
    {
        $this->db->setDefinition($this->targetDefinition);
    }

    /**
     * Updates the schema of the associated database to match this migration
     * schema.
     */
    private function reload()
    {
        $this->db->setDefinition($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($table)
    {
        if (isset($this->definitions[$table])) {
            return $this->definitions[$table];
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function createTable($table, DefinitionBuilder $definition)
    {
        $this->tables[] = $table;
        $this->definitions[$table] = $definition;
        $this->reload();
    }

    /**
     * {@inheritdoc}
     */
    public function renameTable($table, $newName)
    {
        $this->tables = array_diff($this->tables, array(
            $table
        ));
        $definition = $this->definitions[$table];
        // TODO: Change name somehow...
        unset($this->definitions[$table]);
        $this->definitions[$newName] = $definition;
        $this->reload();
    }

    /**
     * {@inheritdoc}
     */
    public function dropTable($table)
    {
        $this->tables = array_diff($this->tables, array(
            $table
        ));
        unset($this->definitions[$table]);
        $this->reload();
    }

    /**
     * {@inheritdoc}
     */
    public function addColumn($table, $column, DataType $type)
    {
        $this->definitions[$table]->$column = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteColumn($table, $column)
    {
        unset($this->definitions[$table]->$column);
    }

    /**
     * {@inheritdoc}
     */
    public function alterColumn($table, $column, DataType $type)
    {
        $this->definitions[$table]->$column = $type;
    }

    public function renameColumn($table, $column, $newName)
    {
        $type = $this->definitions[$table]->$column;
        unset($this->definitions[$table]->$column);
        $this->definitions[$table]->$newName = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function createKey($table, $key, array $columns, $unique = true)
    {
        if ($unique) {
            $this->definitions[$table]->addUnique($columns, $key);
        } else {
            $this->definitions[$table]->addKey($columns, $key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteKey($table, $key)
    {
        $this->definitions[$table]->removeKey($key);
    }

    /**
     * {@inheritdoc}
     */
    public function alterKey($table, $key, array $columns, $unique = true)
    {
        $this->deleteKey($table, $key);
        $this->createKey($table, $key, $columns, $unique);
    }
}
