<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

use Jivoo\InvalidPropertyException;

/**
 * An array-record.
 */
class ArrayRecord implements Record
{

    /**
     * Associative array of record data.
     *
     * @var array
     */
    private $data = array();

    /**
     * Construct record.
     *
     * @param array $data
     *            Associative array of record data.
     */
    public function __construct($data = array())
    {
        $this->data = $data;
    }


    /**
     * {@inheritdoc}
     */
    public function addData(array $data, $allowedFields = null)
    {
        if (isset($allowedFields)) {
            $allowedFields = array_flip($allowedFields);
            $data = array_intersect_key($data, $allowedFields);
        }
        foreach ($data as $field => $value) {
            $this->__set($field, $data[$field]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function __get($field)
    {
        if (! array_key_exists($field, $this->data)) {
            throw new InvalidPropertyException('Invalid property: ' . $field);
        }
        return $this->data[$field];
    }

    /**
     * {@inheritdoc}
     */
    public function __set($field, $value)
    {
        if (! array_key_exists($field, $this->data)) {
            throw new InvalidPropertyException('Invalid property: ' . $field);
        }
        $this->data[$field] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($field)
    {
        if (! array_key_exists($field, $this->data)) {
            throw new InvalidPropertyException('Invalid property: ' . $field);
        }
        return isset($this->data[$field]);
    }

    /**
     * {@inheritdoc}
     */
    public function __unset($field)
    {
        if (! array_key_exists($field, $this->data)) {
            throw new InvalidPropertyException('Invalid property: ' . $field);
        }
        $this->data[$field] = null;
    }

    /**
     * {@inheritdoc}
     */
    public function set($field, $value)
    {
        $this->__set($field, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isSaved()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isNew()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
    }

    /**
     * Determine if a field is set.
     *
     * @param string $field
     *            Field name.
     * @return bool True if not null, false otherwise.
     * @throws InvalidPropertyException If the field does not exist.
     */
    public function offsetExists($field)
    {
        return $this->__isset($field);
    }

    /**
     * Get value of a field.
     *
     * @param string $field
     *            Field name.
     * @return mixed Value.
     * @throws InvalidPropertyException If the field does not exist.
     */
    public function offsetGet($field)
    {
        return $this->__get($field);
    }

    /**
     * Set value of a field.
     *
     * @param string $field
     *            Field name.
     * @param mixed $value
     *            Value.
     * @throws InvalidPropertyException If the field does not exist.
     */
    public function offsetSet($field, $value)
    {
        $this->__set($field, $value);
    }

    /**
     * Set a field value to null.
     *
     * @param string $field
     *            Field name.
     * @throws InvalidPropertyException If the field does not exist.
     */
    public function offsetUnset($field)
    {
        $this->__unset($field);
    }
}
