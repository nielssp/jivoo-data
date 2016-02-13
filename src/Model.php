<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

use Jivoo\Data\Query\Readable;
use Jivoo\Data\Query\Updatable;
use Jivoo\Data\Query\Deletable;

/**
 * A selectable data source with a schema.
 */
interface Model extends Readable, Updatable, Deletable, DataSource
{

    /**
     * Get name of model.
     *
     * @return string Name.
     */
    public function getName();

    /**
     * Get schema for model.
     *
     * @return Schema Schema.
     */
    public function getShema();

    /**
     * Create a record.
     *
     * @param array $data
     *            Associative array of record data.
     * @param string[]|null $allowedFields
     *            List of allowed fields (null for all
     *            fields allowed), fields that are not allowed (or not in the model) will be
     *            ignored.
     * @return Record A record.
     */
    public function create(array $data = array(), $allowedFields = null);

    /**
     * Make a selection that selects a single record.
     * @param Record $record
     *            A record.
     * @return Query\AnySelectable A selection.
     */
    public function selectRecord(Record $record);

    /**
     * Make a selection that selects everything except for a single record.
     *
     * @param Record $record
     *            A record.
     * @return Query\AnySelectable A selection.
     */
    public function selectNotRecord(Record $record);

    /**
     * Find a record by its primary key. If the primary key
     * consists of multiple fields, this function expects a
     * parameter for each field (in alphabetical order).
     * @param mixed $primary
     *            Value of primary key.
     * @param mixed ...$primary
     *            For multifield primary key.
     * @return Record|null A single matching record or null if it doesn't exist.
     * @throws InvalidArgumentException If number of parameters does not
     * match size of primary key.
     */
    public function find($primary);
}
