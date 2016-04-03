<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database;

/**
 * A database.
 */
interface Database
{

    /**
     * Get table object.
     *
     * @param string $table
     *            Table name
     * @return Table Table.
     * @throws InvalidTableException If the table is not defined.
     */
    public function __get($table);

    /**
     * Whether or not a table is defined.
     *
     * @param string $table
     *            Table name.
     * @return bool True if table is defined, false otherwise.
     */
    public function __isset($table);

    /**
     * Close database connection.
     */
    public function close();

    /**
     * Get definition of database.
     *
     * @return DatabaseDefinition Database definition.
     */
    public function getDefinition();

    /**
     * Begin database transaction.
     */
    public function beginTransaction();

    /**
     * Commit database transaction.
     */
    public function commit();

    /**
     * Rollback database transaction.
     */
    public function rollback();
}
