<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database;

/**
 * Iterator for {@see ResultSet} instances.
 */
class ResultSetIterator implements \Iterator
{

    /**
     * @var ResultSet Result set.
     */
    private $resultSet;

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
     *
     * @param ResultSet $resultSet
     *            Result set.
     */
    public function __construct(ResultSet $resultSet)
    {
        $this->resultSet = $resultSet;
        if ($this->resultSet->hasRows()) {
            $this->array[] = $this->resultSet->fetchAssoc();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Get current record.
     *
     * @return Record A record.
     */
    public function current()
    {
        return $this->array[$this->position];
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if ($this->resultSet->hasRows()) {
            $this->array[] = $this->resultSet->fetchAssoc();
        }
        $this->position ++;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return isset($this->array[$this->position]);
    }

    /**
     * Convert result set to array.
     *
     * @return \Jivoo\Data\Database\Record[] Array of records.
     */
    public function toArray()
    {
        while ($this->resultSet->hasRows()) {
            $this->next();
        }
        return $this->array;
    }
}
