<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Builders;

use Jivoo\InvalidMethodException;
use Jivoo\Data\Query\Selection;
use Jivoo\Data\DataSource;
use Jivoo\Data\Query\Expression;
use Jivoo\Data\Query\Selectable;
use Jivoo\Data\Query\Expression\ExpressionParser;

/**
 * Base class for other selections.
 */
abstract class SelectionBase implements Selectable, Selection
{

    /**
     * The ordering.
     *
     * @var array[]
     */
    protected $ordering = array();

    /**
     * Record limit.
     *
     * @var int|null Limit.
     */
    protected $limit = null;

    /**
     * @var Expression|null
     */
    protected $predicate = null;

    /**
     * The data source.
     *
     * @var DataSource
     */
    protected $source = null;

    /**
     * Construct selection base.
     *
     * @param DataSource $source
     *            Target of selection.
     */
    public function __construct(DataSource $source)
    {
        $this->source = $source;
    }

    /**
     * {@inheritdoc}
     */
    public function getPredicate()
    {
        return $this->predicate;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $args)
    {
        switch ($method) {
            case 'and':
            case 'or':
                return call_user_func_array([$this, $method . 'Where'], $args);
        }
        // TODO: document this behavior
        if (is_callable([$this->source, $method])) {
            array_push($args, $this);
            return call_user_func_array([$this->source, $method], $args);
        }
        throw new InvalidMethodException('Invalid method: ' . $method);
    }

    /**
     * {@inheritdoc}
     */
    public function limit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function where($expr)
    {
        $args = func_get_args();
        return call_user_func_array([$this, 'andWhere'], $args);
    }

    /**
     * {@inheritdoc}
     */
    public function andWhere($expr)
    {
        $args = func_get_args();
        if (! isset($this->predicate)) {
            $expr = array_shift($args);
            $this->predicate = new ExpressionParser($expr, $args);
        } else {
            $this->predicate = call_user_func_array([$this->predicate, 'andWhere'], $args);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orWhere($expr)
    {
        $args = func_get_args();
        if (! isset($this->predicate)) {
            $expr = array_shift($args);
            $this->predicate = new ExpressionParser($expr, $args);
        } else {
            $this->predicate = call_user_func_array([$this->predicate, 'orWhere'], $args);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orderBy($column)
    {
        $this->ordering[] = array(
            $column,
            false
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orderByDescending($column)
    {
        $this->ordering[] = array(
            $column,
            true
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseOrder()
    {
        foreach ($this->ordering as $key => $column) {
            $this->ordering[$key][1] = ! $column[1];
        }
        return $this;
    }

    /**
     * Convert a basic selection to a full selection.
     * Removes all information specific to read/update/delete.
     *
     * @return SelectionBuilder Selection.
     */
    public function toSelection()
    {
        $selection = new SelectionBuilder($this->source);
        $selection->predicate = $this->predicate;
        $selection->limit = $this->limit;
        $selection->ordering = $this->ordering;
        return $selection;
    }
}
