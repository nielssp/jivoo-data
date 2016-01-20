<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Models\ModelBase;
use Jivoo\Models\RecordIterator;
use Jivoo\Models\Selection\ReadSelectionBuilder;

/**
 * Iterator for {@see ResultSet} instances.
 */
class ResultSetIterator implements RecordIterator {
  /**
   * @var ResultSet Result set.
   */
  private $resultSet;
  
  /**
   * @var Model Model.
   */
  private $model;
  
  /**
   * @var ReadSelection Selection.
   */
  private $selection;

  /**
   * @var int Index.
   */
  private $position = 0;
  
  /**
   * @var Record[] Records.
   */
  private $array = array();

  /**
   * Construct iterator.
   * @param ModelBase $model Model.
   * @param ResultSet $resultSet Result set.
   * @param ReadSelectionBuilder $selection The selection that created this result set.
   */
  public function __construct(ModelBase $model, ResultSet $resultSet, ReadSelectionBuilder $selection) {
    $this->model = $model;
    $this->selection = $selection;
    $this->resultSet = $resultSet;
    if ($this->resultSet->hasRows())
      $this->array[] = $this->model->createExisting($this->resultSet->fetchAssoc(), $selection);
  }

  /**
   * {@inheritdoc}
   */
  public function rewind() {
    $this->position = 0;
  }

  /**
   * Get current record.
   * @return Record A record.
   */
  public function current() {
    return $this->array[$this->position];
  }

  /**
   * {@inheritdoc}
   */
  public function key() {
    return $this->position;
  }

  /**
   * {@inheritdoc}
   */
  public function next() {
    if ($this->resultSet->hasRows())
      $this->array[] = $this->model->createExisting($this->resultSet->fetchAssoc(), $this->selection);
    $this->position++;
  }

  /**
   * {@inheritdoc}
   */
  public function valid() {
    return isset($this->array[$this->position]);
  }
  
  /**
   * Convert result set to array.
   * @return \Jivoo\Databases\Record[] Array of records.
   */
  public function toArray() {
    while ($this->resultSet->hasRows())
      $this->next();
    return $this->array;
  }
}