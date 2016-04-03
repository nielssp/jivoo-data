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
    public function getName();
    
    public function getDefinition();
    
    public function exists();
    
    public function create();
    
    public function drop();
}
