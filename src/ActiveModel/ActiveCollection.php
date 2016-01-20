<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\ActiveModels;

use Jivoo\Models\ModelBase;
use Jivoo\Models\Selection\Selection;
use Jivoo\Models\Selection\UpdateSelectionBuilder;
use Jivoo\Models\Selection\DeleteSelectionBuilder;
use Jivoo\Models\Selection\ReadSelectionBuilder;
use Jivoo\Models\Selection\BasicSelection;
use Jivoo\Models\Selection\ReadSelection;
use Jivoo\Models\Selection\SelectionBuilder;
use Jivoo\Models\Condition\Condition;

/**
 * A special model representing an association collection as result from for
 * instance a many-to-many relationship between models.
 */
class ActiveCollection extends ModelBase {
  /**
   * @var ActiveModel "This" model.
   */
  private $model;
  
  /**
   * @var scalar Id of "this" record.
   */
  private $recordId;
  
  /**
   * @var ActiveModel "Other" model.
   */
  private $other;
  
  /**
   * @var string Name of the foreign key in "other" that points to "this".
   */
  private $thisKey;

  /**
   * @var string Name of the foreign key in "this" that points to "other".
   */
  private $otherKey;
  
  /**
   * @var BasicSelection Source selection.
   */
  private $source;
  
  /**
   * @var Model Model used for joining in a many-to-many relationship.
   */
  private $join = null;
  
  /**
   * @var string Name of "other" primary key.
   */
  private $otherPrimary;
  
  /**
   * @var int|null Number of items in collection.
   */
  private $count = null;
  
  /**
   * @var Condition
   */
  private $condition = null;
  
  /**
   * @var string
   */
  private $alias = null;

  /**
   * Construct active collection.
   * @param ActiveModel $thisModel "This" model.
   * @param scalar $recordId Id of "this" record.
   * @param array $association Associative array of association options, see
   * {@see ActiveModel}.
   */
  public function __construct(ActiveModel $thisModel, $recordId, $association) {
    $this->model = $thisModel;
    $this->recordId = $recordId;
    $this->other = $association['model'];
    $this->thisKey = $association['thisKey'];
    $this->otherKey = $association['otherKey'];
    if (isset($association['join'])) {
      $this->join = $association['join'];
      $this->otherPrimary = $association['otherPrimary'];
    }
    if (isset($association['name']))
      $this->alias = $association['name'];
    if (isset($association['condition']))
      $this->condition = $association['condition'];
    $this->source = $this->prepareSelection($this->other);
  }

  /**
   * Prepare selection, e.g. by joining with join table.
   * @param BasicSelection $selection Input selection or null for source.
   * @return ReadSelection Resulting selection.
   */
  private function prepareSelection(BasicSelection $selection = null) {
    if (!isset($selection))
      return $this->source;
    $selection->alias($this->alias);
    if (isset($this->join)) {
      assume($selection instanceof ReadSelection);
      $selection = $selection
        ->leftJoin($this->join, $this->otherPrimary . '= J.' . $this->otherKey, 'J')
        ->where('J.' . $this->thisKey . ' = ?', $this->recordId);
    }
    else {
      $selection = $selection->where($this->thisKey . ' = ?', $this->recordId);
      if ($selection instanceof SelectionBuilder)
        $selection = $selection->toReadSelection();
    }
    if (isset($this->condition))
      $selection = $selection->where($this->condition);
    return $selection;
  }

  /**
   * Add a record to collection.
   * @param ActiveRecord $record A record.
   */
  public function add(ActiveRecord $record) {
    if (isset($this->join)) {
      $pk = $this->otherPrimary;
      $this->join->insert(array(
        $this->thisKey => $this->recordId,
        $this->otherKey => $record->$pk
      ));
    }
    else {
      $key = $this->thisKey;
      $record->$key = $this->recordId;
      $record->save();
    }
  }

  /**
   * Add all records from selection to collection.
   * @param Selection $selection Selection of records.
   */
  public function addAll(Selection $selection) {
    if (isset($this->join)) {
      $pk = $this->otherPrimary;
      foreach ($selection as $record) {
        $this->join->insert(array(
          $this->thisKey => $this->recordId,
          $this->otherKey => $record->$pk
        ));
      }
    }
    else {
      $selection->set($this->thisKey, $this->recordId)->update();
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function count() {
    if (isset($this->count))
      return $this->count;
    return parent::count();
  }
  
  /**
   * Set the number of items in this collection.
   * @param int $count Count.
   */
  public function setCount($count) {
    $this->count = $count;
  }

  /**
   * Whether or not collection contains a record.
   * @param ActiveRecord $record A record.
   * @return boolean True if collection contains record, false otherwise.
   */
  public function contains(ActiveRecord $record) {
    if (isset($this->join)) {
      $pk = $this->otherPrimary;
      return $this->join->where($this->thisKey . ' = ?', $this->recordId)
        ->and($this->otherKey . ' = ?', $record->$pk)
        ->count() > 0;
    }
    else {
      return $this->source->where($this->thisKey . ' = ?', $this->recordId)
        ->count() > 0;
    }
  }

  /**
   * Remove a record from collection.
   * @param ActiveRecord $record A record.
   */
  public function remove(ActiveRecord $record) {
    if (isset($this->join)) {
      $pk = $this->otherPrimary;
      $this->join->where($this->thisKey . ' = ?', $this->recordId)
        ->and($this->otherKey . ' = ?', $record->$pk)
        ->delete();
    }
    else {
      $key = $this->thisKey;
      $record->$key = null;
      $record->save();
    }
  }

  /**
   * Remove all records in selection from collection.
   * @param Selection $selection A selection of records.
   */
  public function removeAll(Selection $selection = null) {
    $selection = $this->prepareSelection($selection);
    if (isset($this->join)) {
      $pk = $this->otherPrimary;
      foreach ($selection as $record) {
        $this->join->where($this->thisKey . ' = ?', $this->recordId)
          ->and($this->otherKey . ' = ?', $record->$pk)
          ->delete();
      }
    }
    else {
      $selection->set($this->thisKey, null)->update();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->other->getName();
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return $this->other->getSchema();
  }

  /**
   * {@inheritdoc}
   */
  public function create($data = array(), $allowedFields = null) {
    $record = $this->other->create($data, $allowedFields);
    if (!isset($this->join)) {
      $thisKey = $this->thisKey;
      $record->$thisKey = $this->recordId;
    }
    return $record;
  }
  
  /**
   * {@inheritdoc}
   */
  public function createExisting($data = array(), ReadSelectionBuilder $selection) {
    return $this->other->createExisting($data, $selection);
  }

  /**
   * {@inheritdoc}
   */
  public function updateSelection(UpdateSelectionBuilder $selection) {
    if (!isset($this->join))
      return $this->other->updateSelection(
        $selection->where($this->thisKey . ' = ?', $this->recordId)
      );
    $sets = $selection->sets;
    $read = $this->prepareSelection($selection->toSelection());
    $num = 0;
    foreach ($read->select($this->otherPrimary) as $otherId) {
      $num++;
      $this->other->where($this->otherPrimary . ' = ?', $otherId)
        ->set($sets)
        ->update();
    }
    return $num;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteSelection(DeleteSelectionBuilder $selection) {
    if (!isset($this->join))
      return $this->other->deleteSelection($this->prepareSelection($selection));
    $pk = $this->otherPrimary;
    $num = 0;
    if (isset($selection))
      $selection = $this->prepareSelection($selection->toSelection());
    else
      $selection = $this->source;
    foreach ($selection as $record) {
      $num++;
      $this->join->where($this->thisKey . ' = ?', $this->recordId)
        ->and($this->otherKey . ' = ?', $record->$pk)
        ->delete();
      $record->delete();
    }
    return $num;
  }

  /**
   * {@inheritdoc}
   */
  public function countSelection(ReadSelectionBuilder $selection) {
    return $this->other->countSelection($selection);
  }

  /**
   * {@inheritdoc}
   */
  public function firstSelection(ReadSelectionBuilder $selection) {
    return $this->other->firstSelection($this->prepareSelection($selection));
  }

  /**
   * {@inheritdoc}
   */
  public function lastSelection(ReadSelectionBuilder $selection) {
    return $this->other->lastSelection($this->prepareSelection($selection));
  }

  /**
   * {@inheritdoc}
   */
  public function read(ReadSelectionBuilder $selection) {
    return $this->other->read($this->prepareSelection($selection));
  }

  /**
   * {@inheritdoc}
   */
  public function readCustom(ReadSelectionBuilder $selection) {
    return $this->other->readCustom($this->prepareSelection($selection));
  }

  /**
   * {@inheritdoc}
   */
  public function insert($data, $replace = false) {
    if (!isset($this->join)) {
      $data[$this->thisKey] = $this->recordId;
    }
    $insertId = $this->other->insert($data, $replace);
    if (isset($this->join)) {
      $pk = $this->other->getAiPrimaryKey();
      if (isset($pk))
        $data[$pk] = $insertId;
      $this->join->insert(array(
        $this->thisKey => $this->recordId,
        $this->otherKey => $data[$this->otherPrimary]
      ), $replace);
    }
    return $insertId;
  }

  /**
   * {@inheritdoc}
   */
  public function insertMultiple($records, $replace = false) {
    $id = null;
    foreach ($records as $data)
      $id = $this->insert($data, $replace);
    return $id;
  }
}
