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
        return RecordBuilder::create($this, $data, $allowedFields);
    }
        
    /**
     * {@inheritDoc}
     */
    public function open(array $data, Query\ReadSelection $selection)
    {
        $additional = $selection->getAdditionalFields();
        if (empty($additional)) {
            return RecordBuilder::open($this, $data, []);
        }
        $virtual = array();
        $subrecords = array();
        foreach ($data as $field => $value) {
            if (isset($additional[$field])) {
                if (isset($additional[$field]['record'])) {
                    $record = $additional[$field]['record'];
                    if (!isset($subrecords[$record])) {
                        $subrecords[$record] = [
                            'model' => $additional[$field]['model'],
                            'null' => true,
                            'data' => []
                        ];
                    }
                    $subrecords[$record]['data'][$additional[$field]['recordField']] = $value;
                    if (isset($value)) {
                        $subrecords[$record]['null'] = false;
                    }
                } else {
                    $virtual[$field] = $value;
                }
                unset($data[$field]);
            }
        }
        foreach ($subrecords as $field => $record) {
            if ($record['null']) {
                $virtual[$field] = null;
            } else {
                $virtual[$field] = RecordBuilder::open($record['model'], $record['data']);
            }
        }
        return RecordBuilder::open($this, $data, $virtual);
    }
    
    /**
     * {@inheritDoc}
     */
    public function openSelection(Query\ReadSelection $selection)
    {
        return new RecordIterator($this->readSelection($selection), $this, $selection);
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
