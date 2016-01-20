<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

use Jivoo\Data\Query\Selection;
use Jivoo\Data\Query\UpdateSelection;
use Jivoo\Data\Query\ReadSelection;

/**
 * A CRUD data source.
 */
interface DataSource {
  /**
   * Insert data directly into model.
   * @param array $data Associative array of record data.
   * @param bool $replace Whether to replace records on conflict.
   * @return int|null Last insert id if any.
   */
  public function insert($data, $replace = false);

  /**
   * Insert multiple data records directly into model. Each record-array MUST cotain the
   * same columns and order of columns.
   * @param array[] $records List of associative arrays of record data.
   * @param bool $replace Whether to replace records on conflict.
   * @return int|null Last insert id if any.
   */
  public function insertMultiple($records, $replace = false);

  /**
   * Retrieve the selected records.
   * @param Selection $selection Record selection.
   * @return Record[]|Traversable Selected records as an array or iterator.
   */
  public function read(ReadSelection $selection);

  /**
   * Update the selected records.
   * @param Selection $selection Update selection.
   * @return int Number of updated records if availabble.
   */
  public function update(UpdateSelection $selection);
  
  /**
   * Delete the selected records.
   * @param Selection $selection Selection.
   * @return int Number of deleted records if availabble.
   */
  public function delete(Selection $selection);
  
  /**
   * Join two data sources.
   * @param DataSource $other Other data sorce.
   * @return DataSource|null A compatible data source or null if join not
   * possible.
   */
  public function joinWith(DataSource $other);
}