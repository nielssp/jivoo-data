<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database;

/**
 * A database table.
 */
interface Table extends \Jivoo\Data\Model
{
    
    /**
     * Whether the table exists.
     *
     * @return bool
     */
    public function exists();
    
    /**
     * Create the table.
     * @throws
     */
    public function create(); // TODO: rename method
    
    /**
     * Delete the table.
     */
    public function drop();
}
