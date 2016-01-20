<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models;

use Jivoo\Core\Module;
use Jivoo\Models\Selection\UpdateSelectionBuilder;
use Jivoo\Models\Selection\DeleteSelectionBuilder;
use Jivoo\Models\Selection\ReadSelectionBuilder;
use Jivoo\Models\Selection\SelectionBuilder;
use Jivoo\Models\Selection\ReadSelection;
use Jivoo\Models\Condition\ConditionBuilder;

/**
 * A base class for models.
 */
abstract class ModelBase extends Module implements Model {
  /**
   * @var string|null The auto increment primary key.
   */
  private $aiPrimaryKey = null;
  
  /**
   * @var DataType[] Types of virtual fields introduced by selections.
   */
  private $virtualFields = array();

  /**
   * {@inheritdoc}
   */
  public function create($data = array(), $allowedFields = null) {
    return RecordBuilder::createNew($this, $data, $allowedFields);
  }
  
  /**
   * Create a record for existing data.
   * @param array $data Associative array of record data.
   * @param ReadSelectionBuilder $selection The selection that led to the creation of
   * this record.
   * @return RecordBuilder A record.
   */
  public function createExisting($data = array(), ReadSelectionBuilder $selection) {
    $additonal = $selection->additionalFields;
    if (empty($additonal))
      return RecordBuilder::createExisting($this, $data, array());
    $virtual = array();
    $subrecords = array();
    foreach ($raw as $field => $value) {
      if (isset($addtional[$field])) {
        if (isset($additional[$field]['record'])) {
          $record = $additional[$field]['record'];
          if (!isset($subrecords[$record])) {
            $subrecords[$record] = array(
              'model' => $additional[$field]['model'],
              'null' => true,
              'data' => array()
            );
          }
          $subrecords[$record]['data'][$additional[$field]['recordField']] = $value;
          if (isset($value))
            $subrecords[$record]['null'] = false;
        }
        else {
          $virtual[$field] = $value;
        }
        unset($data[$field]);
      }
    }
    foreach ($subrecords as $field => $record) {
      if ($record['null']) {
        $virtual[$field] = null;
      }
      else {
        $virtual[$field] = RecordBuilder::createExisting($record['model'], $record['data']);
      }
    }
    return RecordBuilder::createExisting($this, $data, $virtual);
  }

  /**
   * {@inheritdoc}
   */
  public function isVirtual($field) {
    return !isset($this->getSchema()->$field);
  }
  
  /**
   * Add a temporary virtual field.
   * @param string $field Field name.
   * @param DataType $type Type.
   */
  public function addVirtual($field, DataType $type = null) {
    $this->virtualFields[$field] = $type;
  }
  
  /**
   * Remove a virtual field.
   * @param string $field Field name.
   */
  public function removeVirtual($field) {
    unset($this->virtualFields[$field]);
  }
  
  /**
   * Remove all virtual fields.
   */
  public function clearVirtual() {
    $this->virtualFields = array();
  }

  /**
   * {@inheritdoc}
   */
  public function getAiPrimaryKey() {
    if (!isset($this->aiPrimaryKey)) {
      $pk = $this->getSchema()->getPrimaryKey();
      if (count($pk) == 1) {
        $pk = $pk[0];
        $type = $this->getSchema()->$pk;
        if ($type->isInteger() and $type->autoIncrement)
          $this->aiPrimaryKey = $pk;
      }
    }
    return $this->aiPrimaryKey;
  }

  /**
   * {@inheritdoc}
   */
  public function selectRecord(Record $record) {
    $primaryKey = $this->getSchema()->getPrimaryKey();
    $selection = $this;
    foreach ($primaryKey as $field) {
      $selection = $selection->where($field . ' = ?', $record->$field);
    }
    return $selection;
  }

  /**
   * {@inheritdoc}
   */
  public function selectNotRecord(Record $record) {
    $primaryKey = $this->getSchema()->getPrimaryKey();
    $condition = new ConditionBuilder();
    foreach ($primaryKey as $field) {
      $condition = $condition->or($field . ' != ?', $record->$field);
    }
    return $this->where($condition);
  }
  
  /**
   * {@inheritdoc}
   */
  public function asInstanceOf($class) {
    if ($this instanceof $class)
      return $this;
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function find($primary) {
    $args = func_get_args();
    $primaryKey = $this->getSchema()->getPrimaryKey();
    sort($primaryKey);
    $selection = $this;
    if (count($args) != count($primaryKey)) {
      throw new InvalidSelectionException(tn(
        'find() must be called with %1 parameters',
        'find() must be called with %1 parameter',
        count($primaryKey)
      ));
    }
    for ($i = 0; $i < count($args); $i++) {
      $type = $this->gettype($primaryKey[$i]);
      $selection = $selection->where($primaryKey[$i] . ' = ' . $type->placeholder, $args[$i]);
    }
    return $selection->first();
  }

  /**
   * {@inheritdoc}
   */
  public function update() {
    return $this->updateSelection(new UpdateSelectionBuilder($this));
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    return $this->deleteSelection(new DeleteSelectionBuilder($this));
  }

  /**
   * Count number of records in model.
   * @return int Number of records.
   */
  public function count() {
    return $this->countSelection(new ReadSelectionBuilder($this));
  }

  /**
   * Get row number of record.
   * @param Record $record A record.
   * @return int The row number.
   */
  public function rowNumber(Record $record) {
    return $this->rowNumberSelection(new ReadSelectionBuilder($this), $record);
  } 

  /**
   * {@inheritdoc}
   */
  public function first() {
    return $this->firstSelection(new ReadSelectionBuilder($this));
  }

  /**
   * {@inheritdoc}
   */
  public function last() {
    return $this->lastSelection(new ReadSelectionBuilder($this));
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    $array = array();
    foreach ($this as $record)
      $array[] = $record;
    return $array;
  }
  
  /**
   * Find row number of a record in the result set of a selection. The selection
   * must be ordered.
   * @param ReadSelectionBuilder $selection A read selection.
   * @param Record $record A record.
   * @throws InvalidSelectionException If the selection is not ordered.
   * @return int Row number.
   */
  public function rowNumberSelection(ReadSelectionBuilder $selection, Record $record) {
    if (empty($selection->orderBy)) {
      throw new InvalidSelectionException(tr('Can\'t find row number in selection without ordering'));
    }
    $condition = new ConditionBuilder();
    foreach ($selection->orderBy as $orderBy) {
      $column = $orderBy['column'];
      $type = $this->getType($column)->placeholder;
      if ($orderBy['descending']) {
        $condition->and($column . ' > ' . $type, $record->$column);
      }
      else {
        $condition->and($column . ' < ' . $type, $record->$column);
      }
    }
    return $selection->and($condition)->count() + 1;
  }

  /**
   * Execute an update selection.
   * @param UpdateSelectionBuilder $selection Update selection.
   * @return int Number of affected records.
   */
  public abstract function updateSelection(UpdateSelectionBuilder $selection);
  /**
   * Execute a delete selection.
   * @param DeleteSelectionBuilder $selection Delete selection.
   * @return int Number of affected records.
  */
  public abstract function deleteSelection(DeleteSelectionBuilder $selection);
  
  /**
   * Count size of the result of a selection.
   * @param ReadSelectionBuilder $selection Read selection.
   * @return int Size of selection.
   */
  public abstract function countSelection(ReadSelectionBuilder $selection);
  
  /**
   * Return first record in a selection.
   * @param ReadSelectionBuilder $selection Read selection.
   * @return Record A record.
   */
  public abstract function firstSelection(ReadSelectionBuilder $selection);
  
  /**
   * Return last record in a selection.
   * @param ReadSelectionBuilder $selection Read selection.
   * @return Record A record.
   */
  public abstract function lastSelection(ReadSelectionBuilder $selection);
  
  /**
   * Execute a read selection without creating records.
   * @param ReadSelectionBuilder $selection Read selection.
   * @return array[] A list of associative arrays.
   */
  public abstract function readCustom(ReadSelectionBuilder $selection); 

  /**
   * Execute a read selection and create an iterator.
   * @param ReadSelectionBuilder $selection Read selection.
   * @return RecordIterator Iterator.
  */
  public abstract function read(ReadSelectionBuilder $selection);

  /**
   * {@inheritdoc}
   */
  public function getValidator() {
    return null;
  }
  
  
  /**
   * {@inheritdoc}
   */
  public function getFields() {
    if (!empty($this->virtualFields))
      return array_merge($this->getSchema()->getFields(), array_keys($this->virtualFields));
    return $this->getSchema()->getFields();
  }

  /**
   * {@inheritdoc}
   */
  public function getType($field) {
    if (array_key_exists($field, $this->virtualFields))
      return $this->virtualFields[$field];
    return $this->getSchema()->$field;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel($field) {
    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function hasField($field) {
    if (array_key_exists($field, $this->virtualFields))
      return true;
    return isset($this->getSchema()->$field);
  }

  /**
   * {@inheritdoc}
   */
  public function isRequired($field) {
    return false;
  }
  
  /**
   * {@inheritdoc}
   */
  public function __call($method, $args) {
    switch ($method) {
      case 'and':
        return call_user_func_array(array(new SelectionBuilder($this), 'andWhere'), $args);
      case 'or':
        return call_user_func_array(array(new SelectionBuilder($this), 'orWhere'), $args);
    }
    return parent::__call($method, $args);
  }

  /**
   * {@inheritdoc}
   */
  public function hasClauses() {
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function where($clause) {
    $args = func_get_args();
    return call_user_func_array(array(new SelectionBuilder($this), 'where'), $args);
  }

  /**
   * {@inheritdoc}
   */
  public function andWhere($clause) {
    $args = func_get_args();
    return call_user_func_array(array(new SelectionBuilder($this), 'andWhere'), $args);
  }

  /**
   * {@inheritdoc}
   */
  public function orWhere($clause) {
    $args = func_get_args();
    return call_user_func_array(array(new SelectionBuilder($this), 'orWhere'), $args);
  }

  /**
   * {@inheritdoc}
   */
  public function limit($limit) {
    $selection = new SelectionBuilder($this);
    return $selection->limit($limit);
  }

  /**
   * {@inheritdoc}
   */
  public function orderBy($column) {
    $selection = new SelectionBuilder($this);
    return $selection->orderBy($column);
  }

  /**
   * {@inheritdoc}
   */
  public function orderByDescending($column) {
    $selection = new SelectionBuilder($this);
    return $selection->orderByDescending($column);
  }

  /**
   * {@inheritdoc}
   */
  public function reverseOrder() {
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function set($column, $value = null) {
    $selection = new UpdateSelectionBuilder($this);
    return $selection->set($column, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function alias($alias) {
    $select = new ReadSelectionBuilder($this);
    return $select->alias($alias);
  }

  /**
   * {@inheritdoc}
   */
  public function select($expression, $alias = null) {
    $select = new ReadSelectionBuilder($this);
    return $select->select($expression, $alias);
  }

  /**
   * {@inheritdoc}
   */
  public function with($field, $expression, DataType $type = null) {
    $select = new ReadSelectionBuilder($this);
    return $select->with($field, $expression, $type);
  }

  /**
   * {@inheritdoc}
   */
  public function withRecord($field, BasicModel $Model) {
    $select = new ReadSelectionBuilder($this);
    return $select->withRecord($field, $model);
  }

  /**
   * {@inheritdoc}
   */
  public function groupBy($columns, $condition = null) {
    $select = new ReadSelectionBuilder($this);
    return $select->groupBy($columns, $condition);
  }

  /**
   * {@inheritdoc}
   */
  public function innerJoin(Model $other, $condition, $alias = null) {
    $select = new ReadSelectionBuilder($this);
    return $select->innerJoin($other, $condition, $alias);
  }

  /**
   * {@inheritdoc}
   */
  public function leftJoin(Model $other, $condition, $alias = null) {
    $select = new ReadSelectionBuilder($this);
    return $select->leftJoin($other, $condition, $alias);
  }

  /**
   * {@inheritdoc}
   */
  public function rightJoin(Model $other, $condition, $alias = null) {
    $select = new ReadSelectionBuilder($this);
    return $select->rightJoin($other, $condition, $alias);
  }

  /**
   * {@inheritdoc}
   */
  public function distinct($distinct = true) {
    $select = new ReadSelectionBuilder($this);
    return $select->distinct($distinct);
  }

  /**
   * {@inheritdoc}
   */
  public function offset($offset) {
    $select = new ReadSelectionBuilder($this);
    return $select->offset($offset);
  }
  

  /**
   * Get iterator.
   * @param ReadSelection $selection Optional selection to get iterator for.
   * @return RecordIterator A record iterator.
   */
  public function getIterator(ReadSelection $selection = null) {
    if (!isset($selection))
      $selection = new ReadSelectionBuilder($this);
    return $this->read($selection);    
  }
}
