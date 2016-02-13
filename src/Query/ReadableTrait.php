<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query;

use Jivoo\Data\DataSource;
use Jivoo\Data\DataType;
use Jivoo\Data\Query\Builders\ReadSelectionBuilder;
use Jivoo\Data\Record;
use Jivoo\Data\Schema;

/**
 * A trait that implements {@see Readable}.
 */
trait ReadableTrait
{
    
    /**
     * Return the data source to make selections on.
     *
     * @return \Jivoo\Data\DataSource
     */
    abstract protected function getSource();

    /**
     * Set offset.
     *
     * @param int $offset
     *            Offset.
     * @return ReadSelectionBuilder A read selection.
     */
    public function offset($offset)
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->offset($offset);
    }

    /**
     * Set alias for selection source.
     *
     * @param string $alias
     *            Alias.
     * @return ReadSelectionBuilder A read selection.
     */
    public function alias($alias)
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->alias($alias);
    }

    /**
     * Make a projection.
     *
     * @param string|string[]|Expression|Expression[] $expression
     *            Expression or array of expressions (if the keys are strings,
     *            they are used as aliases).
     * @param string $alias
     *            Alias.
     * @return \Iterator A {@see Record} iterator.
     * @todo Rename to 'project' ?
     */
    public function select($expression, $alias = null)
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->select($expression, $alias);
    }

    /**
     * Append an extra virtual field to the returned records.
     *
     * @param string $field
     *            Name of new field.
     * @param Expression|string $expression
     *            Expression for field, e.g. 'COUNT(*)'.
     * @param DataType|null $type
     *            Optional type of field.
     * @return ReadSelectionBuilder A read selection.
     */
    public function with($field, $expression, DataType $type = null)
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->with($field, $expression, $type);
    }

    /**
     * Append an extra virtual field (with a record as the value) to the returned
     * records.
     *
     * @param string $field
     *            Name of new field, expects the associated model to be
     *            aliased with the same name.
     * @param Schema $schema
     *            Schema of associated record.
     * @return ReadSelectionBuilder A read selection.
     */
    public function withRecord($field, Schema $schema)  {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->withRecord($field, $schema);
    }

    /**
     * Group by one or more columns.
     *
     * @param string|string[] $columns
     *            A single column name or a list of column
     *            names.
     * @param Expression|string $predicate
     *            Grouping predicate.
     * @return ReadSelectionBuilder A read selection.
     */
    public function groupBy($columns, $predicate = null)
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->groupBy($columns, $predicate);
    }

    /**
     * Perform an inner join with another data source.
     *
     * @param DataSource $other
     *            Other source.
     * @param string|Expression $condition
     *            Join condition.
     * @param string $alias
     *            Alias for joined model/table.
     * @return ReadSelectionBuilder A read selection.
     */
    public function innerJoin(DataSource $other, $condition, $alias = null)
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->innerJoin($other, $condition, $alias);
    }

    /**
     * Perform a left join with another data source.
     *
     * @param DataSource $other
     *            Other source.
     * @param string|Expression $condition
     *            Join condition.
     * @param string $alias
     *            Alias for joined model/table.
     * @return ReadSelectionBuilder A read selection.
     */
    public function leftJoin(DataSource $other, $condition, $alias = null)
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->leftJoin($other, $condition, $alias);
    }

    /**
     * Perform a right join with another data source.
     *
     * @param DataSource $other
     *            Other source.
     * @param string|Expression $condition
     *            Join condition.
     * @param string $alias
     *            Alias for joined model/table.
     * @return ReadSelectionBuilder A read selection.
     */
    public function rightJoin(DataSource $other, $condition, $alias = null)
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->rightJoin($other, $condition, $alias);
    }

    /**
     * Fetch only distinct records (i.e.
     * prevent duplicate records in result).
     *
     * @param bool $distinct
     *            Whether to fetch only distinct records.
     * @return ReadSelectionBuilder A read selection.
     */
    public function distinct($distinct = true)
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->distinct($distinct);
    }

    /**
     * Return first record in selection.
     *
     * @return Record|null A record if available..
     */
    public function first()
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->first();
    }

    /**
     * Return last record in selection.
     *
     * @return Record|null A record if available.
     */
    public function last()
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->last();
    }

    /**
     * Count number of records in selection.
     *
     * @return int Number of records.
     */
    public function count()
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->count();
    }
    
    /**
     * Convert selection to an array.
     *
     * @return Record[] Array of records.
     */
    public function toArray()
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->toArray();
    }
    
    /**
     * Get iterator.
     *
     * @return \Iterator Iterator
     */
    public function getIterator()
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->getIterator();
    }
}
