<?php
// Jivoo Data
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\ActiveModel;

use Jivoo\Data\DataType;
use Jivoo\Data\Definition;
use Jivoo\Data\DefinitionBuilder;
use Jivoo\Data\Validation\ValidatorBuilder;

/**
 * Description of ActiveDefinition
 */
class ActiveDefinition extends DefinitionBuilder
{
    private $validator;
    private $virtual = [];
    private $labels = [];
    private $associations = [];
    
    public function __construct(Definition $copy = null)
    {
        $this->validator = new ValidatorBuilder();
        parent::__construct($copy);
    }
    
    public function __set($field, DataType $type)
    {
        parent::__set($field, $type);
        $type->createValidationRules($this->validator->$field);
    }
    
    public function addTimestamps($created = 'created', $updated = 'updated')
    {
        parent::addTimestamps($created, $updated);
    }
    
    public function setLabel($field, $label)
    {
        $this->labels[$field] = $label;
    }
    
    public function addVirtual($field, DataType $type = null)
    {
        $this->virtual[$field] = $type;
    }
    
    public function hasAndBelongsToMany($field, $model, array $association = [])
    {
        $this->virtual[$field] = DataType::object();
        $association['type'] = 'hasAndBelongsToMany';
        $association['name'] = $field;
        $association['model'] = $model;
        $this->associations[] = $association;
    }
    
    public function hasMany($field, $model, array $association = [])
    {
        $this->virtual[$field] = DataType::object();
        $association['type'] = 'hasMany';
        $association['name'] = $field;
        $association['model'] = $model;
        $this->associations[] = $association;
    }
    
    public function hasOne($field, $model, $thisKey = null)
    {
        $this->virtual[$field] = DataType::object();
        $association['type'] = 'hasOne';
        $association['name'] = $field;
        $association['model'] = $model;
        $association['thisKey'] = $thisKey;
        $this->associations[] = $association;
    }
    
    public function belongsTo($field, $model, $otherKey = null)
    {
        if (!isset($otherKey)) {
            $otherKey = $field . 'Id';
        }
        $this->virtual[$field] = DataType::object();
        $association['type'] = 'hasOne';
        $association['name'] = $field;
        $association['model'] = $model;
        $association['otherKey'] = $otherKey;
        $this->associations[] = $association;
    }
    
    public function getValidator()
    {
        return $this->validator;
    }
    
    public function getAssociations()
    {
        return $this->associations;
    }
    
    public function validate($field)
    {
        return $this->validator->get($field);
    }
}
