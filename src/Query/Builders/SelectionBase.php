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
use Jivoo\Data\Query\Expression\ExpressionParser;

/**
 * Base class for other selections.
 */
abstract class SelectionBase implements Selection, \Jivoo\Data\Query\Selectable
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
     * @var \Jivoo\Data\RecordSource
     */
    protected $source = null;

    /**
     * Construct selection base.
     *
     * @param DataSource $source
     *            Optional selectable target.
     * @param SelectionBase $copy
     *            Optional source for ordering, limit, and predicate.
     */
    public function __construct(\Jivoo\Data\RecordSource $source = null, SelectionBase $copy = null)
    {
        $this->source = $source;
        if (isset($copy)) {
            $this->predicate = $copy->predicate;
            $this->limit = $copy->limit;
            $this->ordering = $copy->ordering;
        }
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
        $clone = clone $this;
        $clone->limit = (int) $limit;
        return $clone;
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
        $clone = clone $this;
        $args = func_get_args();
        if (! isset($this->predicate)) {
            $expr = array_shift($args);
            $clone->predicate = new ExpressionParser($expr, $args);
        } else {
            $clone->predicate = call_user_func_array([$this->predicate, 'andWhere'], $args);
        }
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function orWhere($expr)
    {
        $clone = clone $this;
        $args = func_get_args();
        if (! isset($this->predicate)) {
            $expr = array_shift($args);
            $clone->predicate = new ExpressionParser($expr, $args);
        } else {
            $clone->predicate = call_user_func_array([$this->predicate, 'orWhere'], $args);
        }
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function orderBy($column)
    {
        $clone = clone $this;
        if (! isset($column)) {
            $clone->ordering = [];
        } else {
            $clone->ordering[] = array(
                $column,
                false
            );
        }
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function orderByDescending($column)
    {
        $clone = clone $this;
        if (! isset($column)) {
            $clone->ordering = [];
        } else {
            $clone->ordering[] = array(
                $column,
                true
            );
        }
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseOrder()
    {
        if (! count($this->ordering)) {
            return $this;
        }
        $clone = clone $this;
        foreach ($clone->ordering as $key => $column) {
            $clone->ordering[$key][1] = ! $column[1];
        }
        return $clone;
    }

    /**
     * Convert a basic selection to a full selection.
     * Removes all information specific to read/update/delete.
     *
     * @return SelectionBuilder Selection.
     */
    public function toSelection()
    {
        return new SelectionBuilder($this->source, $this);
    }
}
