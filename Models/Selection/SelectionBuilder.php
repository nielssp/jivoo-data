<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models\Selection;

use Jivoo\Models\Model;
use Jivoo\Models\Record;
use Jivoo\Models\DataType;
use Jivoo\Models\BasicModel;

/**
 * An undecided selection. Will transform into a more specific selection based
 * on use.
 */
class SelectionBuilder extends BasicSelectionBase implements Selection {
  /**
   * Copy attributes into a basic selection.
   * @param BasicSelectionBase $copy A basic selection.
   * @return BasicSelectionBase A basic selection.
   */
  private function copyBasicAttr(BasicSelectionBase $copy) {
    $copy->where = $this->where;
    $copy->limit = $this->limit;
    $copy->orderBy = $this->orderBy;
    return $copy;
  }

  /**
   * Convert to read selection.
   * @return ReadSelectionBuilder A read selection.
   */
  public function toReadSelection() {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model));
  }

  /**
   * {@inheritdoc}
   */
  public function set($column, $value = null) {
    return $this->copyBasicAttr(new UpdateSelectionBuilder($this->model))->set($column, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function update() {
    return $this->copyBasicAttr(new UpdateSelectionBuilder($this->model))->update();
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    return $this->copyBasicAttr(new DeleteSelectionBuilder($this->model))->delete();
  }
  
  /**
   * {@inheritdoc}
   */
  public function alias($alias) {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model))->alias($alias);
  }

  /**
   * {@inheritdoc}
   */
  public function select($column, $alias = null) {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model))->select($column, $alias);
  }

  /**
   * {@inheritdoc}
   */
  public function with($field, $expression, DataType $type = null) {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model))->with($field, $expression, $type);
  }

  /**
   * {@inheritdoc}
   */
  public function withRecord($field, BasicModel $model) {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model))->withRecord($field, $model);
  }

  /**
   * {@inheritdoc}
   */
  public function groupBy($columns, $condition = null) {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model))->groupBy($columns, $condition);
  }

  /**
   * {@inheritdoc}
   */
  public function innerJoin(Model $other, $condition, $alias = null) {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model))->innerJoin($other, $condition, $alias);
  }

  /**
   * {@inheritdoc}
   */
  public function leftJoin(Model $other, $condition, $alias = null) {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model))->leftJoin($other, $condition, $alias);
  }

  /**
   * {@inheritdoc}
   */
  public function rightJoin(Model $other, $condition, $alias = null) {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model))->rightJoin($other, $condition, $alias);
  }

  /**
   * {@inheritdoc}
   */
  public function distinct($distinct = true) {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model))->distinct($distinct);
  }

  /**
   * {@inheritdoc}
   */
  public function first() {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model))->first();
  }

  /**
   * {@inheritdoc}
   */
  public function last() {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model))->last();
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model))->count();
  }

  /**
   * Find row number of a record in selection.
   * @param Record $record A record
   * @return int Row number.
   */
  public function rowNumber(Record $record) {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model))->rowNumber($record);
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model))->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function offset($offset) {
    return $this->copyBasicAttr(new ReadSelectionBuilder($this->model))->offset($offset);
  }

  /**
   * {@inheritdoc}
   */
  function getIterator() {
    return $this->model->getIterator($this->copyBasicAttr(new ReadSelectionBuilder($this->model)));
  }
}
