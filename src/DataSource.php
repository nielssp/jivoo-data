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
interface DataSource
{

    /**
     * Insert data directly into model.
     *
     * @param array $data
     *            Associative array of record data.
     * @param bool $replace
     *            Whether to replace records on conflict.
     * @return int|null Last insert id if any.
     */
    public function insert(array $data, $replace = false);

    /**
     * Insert multiple data records directly into model.
     * Each record-array MUST cotain the same fields and order of fields.
     *
     * @param array[] $records
     *            List of associative arrays of record data.
     * @param bool $replace
     *            Whether to replace records on conflict.
     * @return int|null Last insert id if any.
     */
    public function insertMultiple(array $records, $replace = false);

    /**
     * Count the selected records.
     *
     * @param Selection $selection
     *            Record selection.
     * @return int Number of records in selection.
     */
    public function countSelection(ReadSelection $selection);

    /**
     * Retrieve the selected records.
     *
     * @param Selection $selection
     *            Record selection.
     * @return \Iterator An iterator of associative arrays.
     */
    public function readSelection(ReadSelection $selection);

    /**
     * Update the selected records.
     *
     * @param Selection $selection
     *            Update selection.
     * @return int Number of updated records if availabble.
     */
    public function updateSelection(UpdateSelection $selection);

    /**
     * Delete the selected records.
     *
     * @param Selection $selection
     *            Selection.
     * @return int Number of deleted records if availabble.
     */
    public function deleteSelection(Selection $selection);

    /**
     * Join two data sources.
     *
     * @param DataSource $other
     *            Other data sorce.
     * @return DataSource|null A compatible data source or null if join not
     *         possible.
     */
    public function joinWith(DataSource $other);
}
