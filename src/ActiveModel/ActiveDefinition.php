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
    private $name;
    private $validator;
    private $virtual = [];
    private $labels = [];
    
    public function __construct($name, Definition $copy = null)
    {
        $this->name = $name;
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
    }
    
    public function hasMany($field, $model, array $association = [])
    {
        $this->virtual[$field] = DataType::object();
    }
    
    public function hasOne($field, $model, $thisKey = null)
    {
        if (!isset($thisKey)) {
            $thisKey = lcfirst($this->name) . 'Id';
        }
        $this->virtual[$field] = DataType::object();
    }
    
    public function belongsTo($field, $model, $otherKey = null)
    {
        if (!isset($otherKey)) {
            $otherKey = $field . 'Id';
        }
        $this->virtual[$field] = DataType::object();
    }
    
    public function getValidator()
    {
        return $this->validator;
    }
    
    public function validate($field)
    {
        return $this->validator->get($field);
    }
}
