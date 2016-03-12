<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Builders;

use Jivoo\Data\DataSource;
use Jivoo\Data\DataType;
use Jivoo\Data\Query\AnySelectable;
use Jivoo\Data\Query\ReadSelection;
use Jivoo\Data\Query\UpdateSelection;
use Jivoo\Data\Record;
use Jivoo\Data\Definition;

/**
 * An undecided selection.
 * Will transform into a more specific selection based
 * on use.
 */
class SelectionBuilder extends SelectionBase implements
    \IteratorAggregate,
    AnySelectable,
    ReadSelection,
    UpdateSelection
{
    
    /**
     * {@inheritdoc}
     */
    public function getAdditionalFields()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupPredicate()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getGrouping()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getJoins()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getOffset()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getProjection()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function isDistinct()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return [];
    }

    /**
     * Copy attributes into a basic selection.
     *
     * @param SelectionBase $copy
     *            A basic selection.
     * @return SelectionBase A basic selection.
     */
    private function copyBasicAttr(SelectionBase $copy)
    {
        $copy->predicate = $this->predicate;
        $copy->limit = $this->limit;
        $copy->ordering = $this->ordering;
        return $copy;
    }

    /**
     * Convert to read selection.
     *
     * @return ReadSelectionBuilder A read selection.
     */
    public function toReadSelection()
    {
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source));
    }

    /**
     * {@inheritdoc}
     */
    public function set($column, $value = null)
    {
        return $this->copyBasicAttr(new UpdateSelectionBuilder($this->source))->set($column, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function update()
    {
        return $this->copyBasicAttr(new UpdateSelectionBuilder($this->source))->update();
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        return $this->copyBasicAttr(new DeleteSelectionBuilder($this->source))->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function alias($alias)
    {
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source))->alias($alias);
    }

    /**
     * {@inheritdoc}
     */
    public function select($column, $alias = null)
    {
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source))->select($column, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function with($field, $expression, DataType $type = null)
    {
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source))->with($field, $expression, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function withRecord($field, Definition $schema)
    {
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source))->withRecord($field, $schema);
    }

    /**
     * {@inheritdoc}
     */
    public function groupBy($columns, $condition = null)
    {
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source))->groupBy($columns, $condition);
    }

    /**
     * {@inheritdoc}
     */
    public function innerJoin(DataSource $other, $condition, $alias = null)
    {
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source))->innerJoin($other, $condition, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function leftJoin(DataSource $other, $condition, $alias = null)
    {
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source))->leftJoin($other, $condition, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function rightJoin(DataSource $other, $condition, $alias = null)
    {
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source))->rightJoin($other, $condition, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function distinct($distinct = true)
    {
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source))->distinct($distinct);
    }

    /**
     * {@inheritdoc}
     */
    public function first()
    {
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source))->first();
    }

    /**
     * {@inheritdoc}
     */
    public function last()
    {
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source))->last();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source))->count();
    }

    /**
     * Find row number of a record in selection.
     *
     * @param Record $record
     *            A record
     * @return int Row number.
     */
    public function rowNumber(Record $record)
    {
        // TODO: reimplement this functionality somwehere
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source))->rowNumber($record);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source))->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function offset($offset)
    {
        return $this->copyBasicAttr(new ReadSelectionBuilder($this->source))->offset($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->source->readSelection($this->copyBasicAttr(new ReadSelectionBuilder($this->source)));
    }
}
