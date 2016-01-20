<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\ActiveModels;

use Jivoo\Models\Model;
use Jivoo\Models\Record;

/**
 * Record meta data object.
 */
class Meta implements Record {
  /**
   * @var Model Meta model.
   */
  private $model;

  /**
   * @var ActiveRecord
   */
  private $record;

  /**
   * @var string
   */
  private $recordKey;

  /**
   * @var mixed
   */
  private $id;

  /**
   * @var string[]
   */
  private $data = null;

  /**
   * @var string[]
   */
  private $changes = array();

  /**
   * @var bool[]
   */
  private $deletions = array();

  /**
   * Construct meta data object.
   * @param Model $model Meta data model.
   * @param string $recordKey Name of key column in meta model (e.g. 'userId').
   * @param ActiveRecord $record Record data meta data describes.
   */
  public function __construct(Model $model, $recordKey, ActiveRecord $record)  {
    $this->model = $model;
    $this->recordKey = $recordKey;
    $id = $record->getModel()->getAiPrimaryKey();
    $this->id = $record->$id;
    $this->record = $record;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getData() {
    if (!isset($this->data))
      $this->fetch();
    return $data;
  }
  
  /**
   * {@inheritdoc}
   */
  public function addData($data, $allowedFields = null) {
    assume(is_array($data));
    if (is_array($allowedFields)) {
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
  public function getVirtualData() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function __get($variable) {
    if (isset($this->deletions[$variable]))
      return null;
    if (array_key_exists($variable, $this->changes))
      return $this->changes[$variable];
    if (!isset($this->data))
      $this->fetch();
    if (!isset($this->data[$variable]))
      return null;
    return $this->data[$variable];
  }

  /**
   * {@inheritdoc}
   */
  public function __set($variable, $value) {
    if (!isset($value)) {
      if (isset($this->data[$variable]))
        $this->deletions[$variable] = true;
      unset($this->changes[$variable]);
    }
    else {
      $this->changes[$variable] = $value;
      unset($this->deletions[$variable]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function set($variable, $value) {
    $this->__set($variable, $value);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($variable) {
    if (isset($this->deletions[$variable]))
      return false;
    if (array_key_exists($variable, $this->changes))
      return isset($this->changes[$variable]);
    if (!isset($this->data))
      $this->fetch();
    return isset($this->data[$variable]);
  }

  /**
   * {@inheritdoc}
   */
  public function __unset($variable) {
    $this->__set($variable, null);
  }

  /**
   * Fetch variable values from model.
   * @param string[]|null $variables Optional list of variable names to fetch.
   */
  public function fetch($variables = null) {
    if ($this->record->isNew())
      return;
    $selection = $this->model->where('%c = %i', $this->recordKey, $this->id);
    if (isset($variables))
      $selection = $selection->and('variable IN %s()', $variables);
    if (!isset($this->data))
      $this->data = array();
    foreach ($selection->select(array('variable', 'value')) as $kv)
      $this->data[$kv['variable']] = $kv['value'];
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    if ($this->record->isNew())
      return;
    if (!empty($this->deletions)) {
      $this->model->where('%c = %i', $this->recordKey, $this->id)
        ->and('variable IN %s()', array_keys($this->deletions))
        ->delete();
      foreach ($this->deletions as $var => $val)
        unset($this->data[$var]);
      $this->deletions = array();
    }
    if (!empty($this->changes)) {
      $rows = array();
      foreach ($this->changes as $var => $val) {
        if (!isset($this->data[$var]) or $this->data[$var] != $val) {
          $this->data[$var] = $val;
          $rows[] = array(
            $this->recordKey => $this->id,
            'variable' => $var,
            'value' => $val
          );
        }
      }
      $this->model->insertMultiple($rows, true);
      $this->changes = array();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getModel() {
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getErrors() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function isValid() {
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function isNew() {
    return $this->record->isNew();
  }

  /**
   * {@inheritdoc}
   */
  public function isSaved() {
    return empty($this->changes) and empty($this->deletions);
  }
  
  /**
   * {@inheritdoc}
   */
  public function delete() {
    foreach ($this->data as $key => $value)
      $this->__unset($key);
    return $this->save();
  }

  /**
   * Determine if a field is set.
   * @param string $field Field name.
   * @return bool True if not null, false otherwise.
   * @throws \Jivoo\InvalidPropertyException If the field does not exist.
   */
  public function offsetExists($field) {
    return $this->__isset($field);
  }

  /**
   * Get value of a field.
   * @param string $field Field name.
   * @return mixed Value.
   * @throws \Jivoo\InvalidPropertyException If the field does not exist.
   */
  public function offsetGet($field) {
    return $this->__get($field);
  }

  /**
   * Set value of a field.
   * @param string $field Field name.
   * @param mixed $value Value.
   * @throws \Jivoo\InvalidPropertyException If the field does not exist.
   */
  public function offsetSet($field, $value) {
    $this->__set($field, $value);
  }

  /**
   * Set a field value to null.
   * @param string $field Field name.
   * @throws \Jivoo\InvalidPropertyException If the field does not exist.
   */
  public function offsetUnset($field) {
    $this->__unset($field);
  }
}
