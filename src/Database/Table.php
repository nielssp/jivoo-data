<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database;

/**
 * A database table.
 */
interface Table extends \Jivoo\Data\DataSource
{
    
    /**
     * Get table name.
     *
     * @return string Table name.
     */
    public function getName();
    
    /**
     * Whether table exists.
     *
     * @return bool True if table exists, false otherwise.
     */
    public function exists();
    
    /**
     * Create table.
     */
    public function create();
    
    /**
     * Drop table.
     */
    public function drop();
}
