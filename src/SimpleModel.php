<?php
// Jivoo Data
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

/**
 * A simple {@see Model} implementation.
 */
class SimpleModel extends ModelBase
{
    
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var DataSource
     */
    private $source;
    
    /**
     * @var Definition
     */
    private $definition;
    
    /**
     * Construct simple model.
     *
     * @param type $name Model name.
     * @param DataSource $source Data source.
     * @param Definition $definition Data definition.
     */
    public function __construct($name, DataSource $source, Definition $definition)
    {
        $this->name = $name;
        $this->source = $source;
        $this->definition = $definition;
    }
    
    /**
     * {@inheritdoc}
     */
    public function countSelection(Query\ReadSelection $selection)
    {
        return $this->source->countSelection($selection);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSelection(Query\Selection $selection)
    {
        return $this->source->deleteSelection($selection);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(array $data, $replace = false)
    {
        return $this->source->insert($data, $replace);
    }

    /**
     * {@inheritdoc}
     */
    public function insertMultiple(array $records, $replace = false)
    {
        return $this->source->insertMultiple($records, $replace);
    }

    /**
     * {@inheritdoc}
     */
    public function joinWith(DataSource $other)
    {
        return $this->source->joinWith($other);
    }

    /**
     * {@inheritdoc}
     */
    public function readSelection(Query\ReadSelection $selection)
    {
        return $this->source->readSelection($selection);
    }

    /**
     * {@inheritdoc}
     */
    public function updateSelection(Query\UpdateSelection $selection)
    {
        return $this->source->updateSelection($selection);
    }
}
