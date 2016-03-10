<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

use Jivoo\Data\Query\DeletableTrait;
use Jivoo\Data\Query\ReadableTrait;
use Jivoo\Data\Query\UpdatableTrait;
use Jivoo\Data\Query\SelectableTrait;

/**
 * A selectable data source with a schema.
 */
abstract class ModelBase implements Model
{
    use SelectableTrait, UpdatableTrait, DeletableTrait, ReadableTrait;
    
    /**
     * Return the data source to make selections on.
     *
     * @return self Self.
     */
    protected function getSource()
    {
        return $this;
    }
        
    /**
     * {@inheritDoc}
     */
    public function create(array $data = array(), $allowedFields = null)
    {
        return RecordBuilder::createNew($this, $data, $allowedFields);
    }
    
    /**
     * {@inheritDoc}
     */
    public function selectRecord(Record $record)
    {
        $definition = $this->getDefinition();
        $selection = $this;
        foreach ($definition->getPrimaryKey() as $field) {
            $selection = $selection->where(
                '%c = %_',
                $field,
                $definition->getType($field),
                $record->$field
            );
        }
        return $selection;
    }

    /**
     * {@inheritDoc}
     */
    public function selectNotRecord(Record $record)
    {
        $definition = $this->getDefinition();
        $selection = $this;
        foreach ($definition->getPrimaryKey() as $field) {
            $selection = $selection->where(
                '%c != %_',
                $field,
                $definition->getType($field),
                $record->$field
            );
        }
        return $selection;
    }

    /**
     * {@inheritDoc}
     */
    public function find($primary)
    {
        $args = func_get_args();
        $definition = $this->getDefinition();
        $primaryKey = $definition->getPrimaryKey();
        sort($primaryKey);
        $selection = $this;
        if (count($args) != count($primaryKey)) {
            throw new InvalidSelectionException(
                'find() must be called with ' . count($primaryKey) . ' parameter(s)'
            );
        }
        for ($i = 0; $i < count($args); $i++) {
            $selection = $selection->where(
                '%c = %_',
                $primaryKey[$i],
                $definition->gettype($primaryKey[$i]),
                $args[$i]
            );
        }
        return $selection->first();
    }
}
