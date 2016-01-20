<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Models\ModelBase;
use Jivoo\Models\Selection\ReadSelectionBuilder;
use Jivoo\Models\Schema;

/**
 * A database table.
 */
abstract class Table extends ModelBase {
  /**
   * Set schema of table.
   * @param Schema $schema Schema.
   */
  public abstract function setSchema(Schema $schema);

  /**
   * {@inheritdoc}
   */
  public function firstSelection(ReadSelectionBuilder $selection) {
    $resultSet = $this->readSelection($selection->limit(1));
    if (!$resultSet->hasRows())
      return null;
    return $this->createExisting($resultSet->fetchAssoc(), $selection);
  }

  /**
   * {@inheritdoc}
   */
  public function lastSelection(ReadSelectionBuilder $selection) {
    $resultSet = $this->readSelection($selection->reverseOrder()->limit(1));
    if (!$resultSet->hasRows())
      return null;
    return $this->createExisting($resultSet->fetchAssoc(), $selection);
  }

  /**
   * {@inheritdoc}
   */
  public function read(ReadSelectionBuilder $selection) {
    $resultSet = $this->readSelection($selection);
    return new ResultSetIterator($this, $resultSet, $selection);
  }

  /**
   * {@inheritdoc}
   */
  public function readCustom(ReadSelectionBuilder $selection, ModelBase $model = null) {
    $resultSet = $this->readSelection($selection);
    if (isset($model))
      return new ResultSetIterator($model, $resultSet, $selection);
    $result = array();
    while ($resultSet->hasRows()) {
      $result[] = $resultSet->fetchAssoc();
    }
    return $result;
  }

  /**
   * Read a selection.
   * @param ReadSelectionBuilder $selection A read selection.
   * @return ResultSet A result set.
   */
  public abstract function readSelection(ReadSelectionBuilder $selection);
}
