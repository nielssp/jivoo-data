<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Builders;

use Jivoo\Models\ModelBase;
use Jivoo\Models\Condition\ConditionBuilder;
use Jivoo\InvalidPropertyException;
use Jivoo\InvalidMethodException;
use Jivoo\Data\Query\Selection;
use Jivoo\Data\DataSource;
use Jivoo\Data\Query\Expression\E;

/**
 * A basic selection. Base class for other selections.
 * @property-read array[] $orderBy List of arrays describing ordering.
 * @property-read int|null $limit Row limit or null for no limit.
 * @property-read Condition $where Select condition.
 * @property-read Model $model Target of selection.
 */
abstract class SelectionBase implements Selection {
  /**
   * List of arrays describing ordering.
   *
   * Each array is of the format:
   * <code>
   * array(
   *   'column' => ..., // Column name (string)
   *   'descending' => .... // Whether or not to order in descending order (bool)
   * )
   * </code>
   * @var array[]
   */
  protected $orderBy = array();

  /**
   * @var int|null Limit.
  */
  protected $limit = null;

  /**
   * @var Condition Select condition.
   */
  protected $where = null;

  /**
   * @var Model
   */
  protected $model = null;

  /**
   * Construct basic selection.
   * @param ModelBase $model Target of selection.
   */
  public function __construct(DataSource $source) {
    $this->where = new E();
    $this->model = $model;
  }

  /**
   * Get value of property.
   * @param string $property Property name.
   * @return mixed Value.
   * @throws InvalidPropertyException If property undefined.
   */
  public function __get($property) {
    if (isset($this->$property)) {
      return $this->$property;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }

  /**
   * Check if property is set
   * @param string $property Property name
   * @return bool True if set, false otherwise
   */
  public function __isset($property) {
    return isset($this->$property);
  }

  /**
   * {@inheritdoc}
   */
  public function __call($method, $args) {
    switch ($method) {
      case 'and':
        call_user_func_array(array($this->where, 'andWhere'), $args);
        return $this;
      case 'or':
        call_user_func_array(array($this->where, 'orWhere'), $args);
        return $this;
    }
    if (is_callable(array($this->model, $method))) {
      array_push($args, $this);
      return call_user_func_array(array($this->model, $method), $args);
    }
    throw new InvalidMethodException(tr('Invalid method: %1', $method));
  }


  /**
   * {@inheritdoc}
   */
  public function limit($limit) {
    $this->limit = (int) $limit;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasClauses() {
    return $this->where
    ->hasClauses();
  }

  /**
   * {@inheritdoc}
   */
  public function where($clause) {
    $args = func_get_args();
    call_user_func_array(array($this->where, 'where'), $args);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function andWhere($clause) {
    $args = func_get_args();
    call_user_func_array(array($this->where, 'andWhere'), $args);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function orWhere($clause) {
    $args = func_get_args();
    call_user_func_array(array($this->where, 'orWhere'), $args);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function orderBy($column) {
    if (!isset($column))
      $this->orderBy = array();
    else
      $this->orderBy[] = array('column' => $column, 'descending' => false);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function orderByDescending($column) {
    $this->orderBy[] = array('column' => $column, 'descending' => true);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function reverseOrder() {
    foreach ($this->orderBy as $key => $orderBy) {
      $this->orderBy[$key]['descending'] = !$orderBy['descending'];
    }
    return $this;
  }

  /**
   * Convert a basic selection to a full selection. Removes
   * all information specific to read/update/delete.
   * @return SelectionBuilder Selection.
   */
  public function toSelection() {
    $selection = new SelectionBuilder($this->model);
    $selection->where = $this->where;
    $selection->limit = $this->limit;
    $selection->orderBy = $this->orderBy;
    return $selection;
  }
}
