<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

use Jivoo\Models\Validation\ValidatorBuilder;

/**
 * Represents a database table schema.
 */
class DefinitionBuilder implements Definition
{

    /**
     * @var DataType[] List of column names.
     */
    private $fields = array();

    /**
     * @var array List of keys.
     */
    private $keys = array();
    
    public function __construct(Definition $copy = null)
    {
        if (isset($copy)) {
            if ($copy instanceof self) {
                $this->fields = $copy->fields;
                $this->keys = $copy->keys;
            } else {
                foreach ($copy->getFields() as $field) {
                    $this->fields[$field] = $copy->getType($field);
                }
                foreach ($copy->getKeys() as $key) {
                    $this->keys[$key] = [
                        'columns' => $copy->getKey($key),
                        'unique' => $copy->isUnique($key)
                    ];
                }
            }
        }
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
        unset($this->fields[$field]);
    }

    public function getType($field)
    {
        if (! isset($this->fields[$field])) {
            return null;
        }
        return $this->fields[$field];
    }

    /**
     * Add an unsigned auto increment integer.
     *
     * @param string $id
     *            Field name.
     */
    public function addAutoIncrementId($id = 'id')
    {
        $this->$id = DataType::integer(DataType::SERIAL | DataType::UNSIGNED);
        $this->setPrimaryKey($id);
    }

    /**
     * Add created and updated timestamps to schema.
     *
     * @param string $created
     *            Created field name.
     * @param string $updated
     *            Updated field name.
     */
    public function addTimestamps($created = 'created', $updated = 'updated')
    {
        $this->$created = DataType::dateTime();
        $this->$updated = DataType::dateTime();
    }

    /**
     * Create validation rules based on types.
     *
     * @param ValidatorBuilder $validator
     *            Validator to create rules on.
     */
    public function createValidationRules(ValidatorBuilder $validator)
    {
        foreach ($this->fields as $field => $type) {
            $type->createValidationRules($validator->$field);
        }
        foreach ($this->keys as $key) {
            if ($key['unique'] and count($key['columns']) == 1) {
                $field = $key['columns'][0];
                $validator->$field->unique = true;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        return array_keys($this->fields);
    }

    /**
     * Set primary key.
     *
     * @param string|string[] $columns
     *            An array of column names or a single column
     *            name.
     * @param string $columns,...
     *            Additional column names (if $columns is a single
     *            column name).
     */
    public function setPrimaryKey($columns)
    {
        if (! is_array($columns)) {
            $params = func_get_args();
            if (count($params) > 1) {
                $columns = $params;
            } else {
                $columns = array(
                    $columns
                );
            }
        }
        $this->keys['PRIMARY'] = array(
            'columns' => $columns,
            'unique' => true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        if (! isset($this->keys['PRIMARY'])) {
            return array();
        }
        return $this->keys['PRIMARY']['columns'];
    }

    /**
     * Add a unique index to schema.
     *
     * @param string|string[] $columns
     *            An array of column names or a single column
     *            name.
     * @param string $name
     *            Optional key name.
     */
    public function addUnique($columns, $name = null)
    {
        if (! is_array($columns)) {
            $columns = array(
                $columns
            );
        }
        if (! isset($name)) {
            $name = implode('_', $columns);
        }
        if (isset($this->keys[$name])) {
            $this->keys[$name]['columns'] = array_merge($this->keys[$name]['columns'], $columns);
        } else {
            $this->keys[$name] = array(
                'columns' => $columns,
                'unique' => true
            );
        }
    }

    /**
     * Add an index to schema.
     *
     * @param string|string[] $columns
     *            An array of column names or a single column
     *            name.
     * @param string $name
     *            Optional key name.
     */
    public function addKey($columns, $name = null)
    {
        if (! is_array($columns)) {
            $columns = array(
                $columns
            );
        }
        if (! isset($name)) {
            $name = implode('_', $columns);
        }
        if (isset($this->keys[$name])) {
            $columns = array_merge($this->keys[$name]['columns'], $columns);
        }
        if (isset($this->keys[$name])) {
            $this->keys[$name]['columns'] = array_merge($this->keys[$name]['columns'], $columns);
        } else {
            $this->keys[$name] = array(
                'columns' => $columns,
                'unique' => false
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys()
    {
        return array_keys($this->keys);
    }

    /**
     * Remove a key.
     *
     * @param string $name
     *            Key name.
     */
    public function removeKey($name)
    {
        unset($this->keys[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey($key)
    {
        if (isset($this->keys[$key])) {
            return $this->keys[$key]['columns'];
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isUnique($key)
    {
        if (isset($this->keys[$key])) {
            return $this->keys[$key]['unique'];
        }
        return false;
    }
    
    /**
     * Create a definition from the provided field names.
     *
     * @param string[] $fields Field names.
     * @return DefinitionBuilder
     */
    public static function auto(array $fields)
    {
        $definition = new DefinitionBuilder();
        foreach ($fields as $field) {
            $definition->$field = DataType::text(true);
        }
        return $definition;
    }
}
