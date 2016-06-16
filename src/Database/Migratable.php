<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database;

use Jivoo\Data\DataType;
use Jivoo\Data\Definition;

/**
 * Interface containing methods for migrating databases.
 */
interface Migratable
{

    /**
     * Create a table based on a definition.
     *
     * @param string $table
     *            Table name.
     * @param Definition $definition
     *            Schema.
     */
    public function createTable($table, Definition $definition);

    /**
     * Rename a table.
     *
     * @param string $table
     *            Table name.
     * @param string $newName
     *            New table name.
     */
    public function renameTable($table, $newName);

    /**
     * Delete a table.
     *
     * @param string $table
     *            Table name.
     */
    public function dropTable($table);

    /**
     * Add a column to a table.
     *
     * @param string $table
     *            Table name.
     * @param string $column
     *            Column name.
     * @param DataType $type
     *            Type.
     */
    public function addColumn($table, $column, DataType $type);

    /**
     * Delete a column from a table.
     *
     * @param string $table
     *            Table name.
     * @param string $column
     *            Column name.
     */
    public function deleteColumn($table, $column);

    /**
     * Alter a column in a table.
     *
     * @param string $table
     *            Table name.
     * @param string $column
     *            Column name.
     * @param DataType $type
     *            Type.
     */
    public function alterColumn($table, $column, DataType $type);

    /**
     * Rename a column in a table.
     *
     * @param string $table
     *            Table name.
     * @param string $column
     *            Column name.
     * @param string $newName
     *            New column name.
     */
    public function renameColumn($table, $column, $newName);

    /**
     * Create a key.
     *
     * @param string $table
     *            Table name.
     * @param string $key
     *            Key name.
     * @param string[] $columns
     *            Columns.
     * @param bool $unique
     *            Uniqueness.
     */
    public function createKey($table, $key, array $columns, $unique = true);

    /**
     * Delete a key.
     *
     * @param string $table
     *            Table name.
     * @param string $key
     *            Key name.
     */
    public function deleteKey($table, $key);

    /**
     * Alter a key.
     *
     * @param string $table
     *            Table name.
     * @param string $key
     *            Key name.
     * @param string[] $columns
     *            Columns.
     * @param bool $unique
     *            Uniqueness.
     */
    public function alterKey($table, $key, array $columns, $unique = true);
}
