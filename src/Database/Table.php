<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database;

use Jivoo\Models\ModelBase;
use Jivoo\Models\Selection\ReadSelectionBuilder;
use Jivoo\Models\Schema;

/**
 * A database table.
 */
interface Table extends \Jivoo\Data\DataSource
{
    
    public function getName();
    
    public function getDefinition();

    /**
     * Set schema of table.
     *
     * @param Schema $schema
     *            Schema.
     */
    public function setDefinition(Definition $definition);
}
