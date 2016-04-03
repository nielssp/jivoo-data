<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database;

/**
 * A database schema.
 */
interface DatabaseDefinition
{

    /**
     * Get table names.
     *
     * @return string[] List of table names.
     */
    public function getTables();

    /**
     * Get definition of table.
     *
     * @param string $table
     *            Table name.
     * @return \Jivoo\Data\Definition Table definition.
     */
    public function getDefinition($table);
}
