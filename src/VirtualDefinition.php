<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

/**
 * Adds additional virtual fields to a definition.
 */
class VirtualDefinition implements Definition
{
    
    /**
     * @var Definition Parent definition.
     */
    private $parent;

    /**
     * @var DataType[] List of column names.
     */
    private $fields = array();
    
    public function __construct(Definition $parent)
    {
        $this->parent = $parent;
        $this->fields = array_fill_keys($parent->getFields(), null);
    }

    /**
     * Set type of field.
     *
     * @param string $field
     *            Field name.
     * @param DataType $type
     *            Type.
     */
    public function __set($field, DataType $type)
    {
        $this->fields[$field] = $type;
    }

    /**
     * Delete field.
     *
     * @param string $field
     *            Field name.
     */
    public function __unset($field)
    {
        $this->fields[$field] = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($field)
    {
        if (! isset($this->fields[$field])) {
            return $this->parent->getType($field);
        }
        return $this->fields[$field];
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        return array_keys($this->fields);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return $this->parent->getPrimaryKey();
    }


    /**
     * {@inheritdoc}
     */
    public function getKeys()
    {
        return $this->parent->getKeys();
    }

    /**
     * {@inheritdoc}
     */
    public function getKey($key)
    {
        return $this->parent->getKey($key);
    }

    /**
     * {@inheritdoc}
     */
    public function isUnique($key)
    {
        return $this->parent->isUnique($key);
    }
}
