<?php

// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database;

use Jivoo\Data\Definition;

/**
 * A database schema.
 */
class DatabaseDefinitionBuilder implements DatabaseDefinition
{

    /**
     * @var Definition[] Associative array of names and definitions.
     */
    private $definitions = array();

    /**
     * @var string[] List of table names.
     */
    private $tables = array();

    /**
     * Construct database definition.
     *
     * @param Definition[]|DatabaseDefinition $definitions Associative array of
     * table names and definitions or another instance of {@see DatabaseDefinition}.
     * @param boolean $dynamic
     */
    public function __construct($definitions = array())
    {
        if ($definitions instanceof DatabaseDefinitionBuilder) {
            $this->tables = $definitions->tables;
            $this->definitions = $definitions->definitions;
        } elseif ($definitions instanceof DatabaseDefinition) {
            foreach ($definitions->getTables() as $table) {
                $this->addDefinition($table, $definitions->getDefinition($table));
            }
        } else {
            foreach ($definitions as $table => $definition) {
                $this->addDefinition($table, $definition);
            }
        }
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
     * Add a table to the definition.
     *
     * @param string $table Table name.
     * @param Definition $definition Table definition.
     */
    public function addDefinition($table, Definition $definition)
    {
        if (!in_array($table, $this->tables)) {
            $this->tables[] = $table;
        }
        $this->definitions[$table] = $definition;
    }
}
