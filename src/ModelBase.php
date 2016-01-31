<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

use Jivoo\Data\Query\UpdatableTrait;
use Jivoo\Data\Query\DeletableTrait;

/**
 * A selectable data source with a schema.
 */
abstract class ModelBase implements Model
{
    use UpdatableTrait, DeletableTrait;
        
    /**
     * {@inheritDoc}
     */
    public function create(array $data = array(), $allowedFields = null)
    {
        return RecordBuilder::createNew($this, $data, $allowedFields);
    }
}
