<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Builders;

use Jivoo\Models\Model;
use Jivoo\Models\Record;
use Jivoo\Models\Condition\ConditionBuilder;
use Jivoo\Models\DataType;
use Jivoo\Models\BasicModel;
use Jivoo\Models\ModelBase;
use Jivoo\Data\Query\Readable;

/**
 * A read selection.
 * @property-read bool $distinct Distinct.
 * @property-read int $offset Offset.
 * @proeprty-read array $groupBy An array describing grouping.
 * @proeprty-read array[] $joins List of arrays describing joings.
 * @property-read array[] $fields List of arrays describing fields.
 * @property-read array[] $additionalFields List of arrays describing fields.
 */
class ReadSelectionBuilder extends SelectionBase implements Readable, ReadSelection {
  /**
   * @var bool Distinct.
   */
  protected $distinct = false;
  
  /**
   * @var string|null Alias for source.
   */
  protected $alias = null;
  
  /**
   * An arrays describing grouping.
   * 
   * Each array is of the following format:
   * <code>
   * array(
   *   'columns' => ... // List of columns
   *   'condition' => ... // Join condition ({@see Condition})
   * )
   * </code>
   * @var array
   */
  protected $groupBy = null;
  
  /**
   * @var int Offset
   */
  protected $offset = 0;

  /**
   * List of arrays describing joins.
   *
   * Each array is of the following format:
   * <code>
   * array(
   *   'source' => ..., // Data source to join with ({@see DataSource})
   *   'type' => ..., // Type of join: 'INNER', 'RIGHT' or 'LEFT'
   *   'alias' => ..., // Alias for other data source (string|null)
   *   'condition' => ... // Join condition ({@see Condition})
   * );
   * </code>
   * @var array[]
   */
  protected $joins = array();

  /**
   * List of arrays describing columns.
   *
   * Each array is of the following format:
   * <code>
   * array(
   *   'expression' => ..., // Expression (string)
   *   'alias' => ... // Alias (string|null)
   * )
   * </code>
   * @var array[]
  */
  protected $fields = array();

  /**
   * List of arrays describing columns.
   *
   * Each array is of the following format:
   * <code>
   * array(
   *   'alias' => ... // Alias (string)
   *   'expression' => ..., // Expression (string)
   *   'type' => ... // Type (DataType|null)
   *   'model' => ... // Model (BasicModel|null)
   *   'record' => ... // Record field (string|null)
   * )
   * </code>
   * @var array[]
   */
  protected $additionalFields = array();

  /**
   * {@inheritdoc}
   */
  public function alias($alias) {
    $this->alias = $alias;
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function select($expression, $alias = null) {
    if ($alias instanceof ModelBase) {
      $this->fields = array(array(
        'expression' => $expression,
        'alias' => null
      ));
      return $this->source->readCustom($this, $alias);
    }
    $this->fields = array();
    if (is_array($expression)) {
      foreach ($expression as $exp => $alias) {
        if (is_int($exp)) {
          $this->fields[] = array(
            'expression' => $alias,
            'alias' => null
          );
        }
        else {
          $this->fields[] = array(
            'expression' => $exp,
            'alias' => $alias
          );
        }
      }
    }
    else {
      $this->fields[] = array(
        'expression' => $expression,
        'alias' => $alias
      );
    }
    $result = $this->source->readCustom($this);
    $this->fields = array();
    return $result;
  }
  
  /**
   * {@inheritdoc}
   */
  public function with($field, $expression, DataType $type = null) {
    $this->additionalFields[$field] = array(
      'alias' => $field,
      'expression' => $expression,
      'type' => $type
    );
    $this->source->addVirtual($field, $type);
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function withRecord($field, BasicModel $model) {
    foreach ($model->getFields() as $modelField) {
      if ($model->isVirtual($modelField))
        continue;
      $alias = $field . '_' . $modelField;
      $this->additionalFields[$alias] = array(
        'alias' => $alias,
        'expression' => $field . '.' . $modelField,
        'type' => $model->getType($modelField),
        'model' => $model,
        'record' => $field,
        'recordField' => $modelField
      );
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function groupBy($columns, $condition = null) {
    if (!is_array($columns)) {
      $columns = array($columns);
    }
    if (!($condition instanceof ConditionBuilder)) {
      $condition = new ConditionBuilder($condition);
    }
//     if (isset($this->groupBy)) {
//       $columns = array_merge($this->groupBy['columns'], $columns);
//       if ($this->groupBy['condition']->hasClauses())
//         $condition = where($this->groupBy['condition'])->and($condition);
//     }
    $this->groupBy = array('columns' => $columns, 'condition' => $condition,);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function innerJoin(Model $dataSource, $condition = null, $alias = null) {
    if (!($condition instanceof ConditionBuilder)) {
      $condition = new ConditionBuilder($condition);
    }
    $this->joins[] = array(
      'source' => $dataSource,
      'type' => 'INNER',
      'alias' => $alias,
      'condition' => $condition
    );
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function leftJoin(Model $dataSource, $condition, $alias = null) {
    if (!($condition instanceof ConditionBuilder)) {
      $condition = new ConditionBuilder($condition);
    }
    $this->joins[] = array('source' => $dataSource, 'type' => 'LEFT',
      'alias' => $alias, 'condition' => $condition
    );
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function rightJoin(Model $dataSource, $condition, $alias = null) {
    if (!($condition instanceof ConditionBuilder)) {
      $condition = new ConditionBuilder($condition);
    }
    $this->joins[] = array('source' => $dataSource, 'type' => 'RIGHT',
      'alias' => $alias, 'condition' => $condition
    );
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function distinct($distinct = true) {
    $this->distinct = $distinct;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function first() {
    return $this->source->firstSelection($this);
  }

  /**
   * {@inheritdoc}
   */
  public function last() {
    return $this->source->lastSelection($this);
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    return $this->source->countSelection($this);
  }
  
  /**
   * Find row number of a record in selection.
   * @param Record $record A record.
   * @return int Row number.
   */
  public function rowNumber(Record $record) {
    return $this->source->rowNumberSelection($this, $record);
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
   * {@inheritdoc}
   */
  public function offset($offset) {
    $this->offset = (int) $offset;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  function getIterator() {
    return $this->source->getIterator($this);
  }
}
