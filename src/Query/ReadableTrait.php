<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query;

use Jivoo\Data\Query\Builders\ReadSelectionBuilder;

/**
 * A trait that implements {@see Readable}.
 */
trait ReadableTrait
{
    use SelectableTrait;


    /**
     * Set offset.
     *
     * @param int $offset
     *            Offset.
     * @return Readable A readable selection.
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
     * @return Readable A readable selection.
     */
    public function alias($alias)
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->alias($alias);
    }

    /**
     * Make a projection.
     *
     * @param string|string[]|array $expression
     *            Expression or array of expressions
     *            and aliases
     * @param string $alias
     *            Alias.
     * @return array[] List of associative arrays
     */
    public function select($expression, $alias = null)
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->select($expression, $alias);
    }

    /**
     * Append an extra virtual field to the returned records.
     *
     * @param string $alias
     *            Name of new field.
     * @param string $expression
     *            Expression for field, e.g. 'COUNT(*)'.
     * @param DataType|null $type
     *            Optional type of field.
     * @return ReadSelection A read selection.
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
     * @param string $alias
     *            Name of new field, expects the associated model to be
     *            aliased with the same name.
     * @param Schema $schema
     *            Schema of associated record.
     * @return Readable A readable selection.
     */
    public function withRecord($field, Schema $schema)
    {
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
     * @return Readable A readable selection.
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
     * @return Readable A readable selection.
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
     * @return Readable A readable selection.
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
     * @return Readable A readable selection.
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
     * @return Readable A readable selection.
     */
    public function distinct($distinct = true)
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->distinct($distinct);
    }

    /**
     * Return first record in selection.
     *
     * @return \Jivoo\Data\Record|null A record if available..
     */
    public function first()
    {
        $selection = new ReadSelectionBuilder($this->getSource());
        return $selection->first();
    }

    /**
     * Return last record in selection.
     *
     * @return \Jivoo\Data\Record|null A record if available.
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
     * @return \Jivoo\Data\Record[] Array of records.
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
