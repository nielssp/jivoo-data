<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database;

/**
 * A database implementing migration methods.
 */
interface MigratableDatabase extends Database, Migratable
{

    /**
     * Refresh definition, i.e. query the actual database definition from the
     * database.
     */
    public function refreshDefinition();

    /**
     * Change definition of database.
     *
     * @param DatabaseDefinition $definition
     *            New database definition.
     */
    public function setDefinition(DatabaseDefinition $definition);
}
