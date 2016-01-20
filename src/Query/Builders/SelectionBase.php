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
use Jivoo\Data\Query\Expression;

/**
 * A basic selection. Base class for other selections.
 */
abstract class SelectionBase implements Selectable, Selection {
  /**
   * @var array[]
   */
  protected $ordering = array();

  /**
   * @var int|null Limit.
  */
  protected $limit = null;

  /**
   * @var Expression
   */
  protected $predicate = null;

  /**
   * @var DataSource
   */
  protected $source = null;

  /**
   * Construct basic selection.
   * @param ModelBase $model Target of selection.
   */
  public function __construct(DataSource $source) {
    $this->predicate = new ExpressionBuilder();
    $this->source = $source;
  }


  /**
   * {@inheritdoc}
   */
  public function getPredicate() {
    return $this->predicate;
  }


  /**
   * {@inheritdoc}
   */
  public function getOrdering() {
    return $this->ordering;
  }


  /**
   * {@inheritdoc}
   */
  public function getLimit() {
    return $this->limit;
  }
  

  /**
   * {@inheritdoc}
   */
  public function __call($method, $args) {
    switch ($method) {
      case 'and':
        $this->predicate = call_user_func_array(array($this->predicate, 'andWhere'), $args);
        return $this;
      case 'or':
        $this->predicate = call_user_func_array(array($this->predicate, 'orWhere'), $args);
        return $this;
    }
    // TODO: document this
    if (is_callable(array($this->source, $method))) {
      array_push($args, $this);
      return call_user_func_array(array($this->source, $method), $args);
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
  public function where($clause) {
    $args = func_get_args();
    $this->predicate = call_user_func_array(array($this->predicate, 'where'), $args);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function andWhere($clause) {
    $args = func_get_args();
    $this->predicate = call_user_func_array(array($this->predicate, 'andWhere'), $args);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function orWhere($clause) {
    $args = func_get_args();
    $this->predicate = call_user_func_array(array($this->predicate, 'orWhere'), $args);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function orderBy($column) {
    if (!isset($column))
      $this->ordering = array();
    else
      $this->ordering[] = array($column, false);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function orderByDescending($column) {
    $this->ordering[] = array($column, true);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function reverseOrder() {
    foreach ($this->ordering as $key => $column) {
      $this->ordering[$key][1] = !$column[1];
    }
    return $this;
  }

  /**
   * Convert a basic selection to a full selection. Removes
   * all information specific to read/update/delete.
   * @return SelectionBuilder Selection.
   */
  public function toSelection() {
    $selection = new SelectionBuilder($this->source);
    $selection->predicate = $this->predicate;
    $selection->limit = $this->limit;
    $selection->ordering = $this->ordering;
    return $selection;
  }
}
