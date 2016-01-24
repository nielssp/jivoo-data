<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query;

use Jivoo\Data\Query\Builders\SelectionBuilder;

/**
 * A trait that implements {@see Selectable}.
 */
trait SelectableTrait
{
    
    /**
     * Return the data source to make selections on.
     *
     * @return \Jivoo\Data\DataSource
     */
    abstract protected function getSource();

    /**
     * Order selection by a column or expression.
     *
     * @param Expression|string|null $expression
     *            Expression or column.
     *            If null all ordering will be removed from selection.
     * @return Selectable A selection.
     */
    public function orderBy($expr)
    {
        $selection = new SelectionBuilder($this->getSource());
        return $selection->orderBy($expr);
    }

    /**
     * Order selection by a column or expression, in descending order.
     *
     * @param Expression|string $expression
     *            Expression or column.
     * @return Selectable A selection.
     */
    public function orderByDescending($expr)
    {
        $selection = new SelectionBuilder($this->getSource());
        return $selection->orderByDescending($expr);
    }

    /**
     * Reverse the ordering.
     *
     * @return Selectable A selection.
     */
    public function reverseOrder()
    {
        $selection = new SelectionBuilder($this->getSource());
        return $selection->reversOrder();
    }

    /**
     * Limit number of records.
     *
     * @param
     *            int Number of records.
     * @return Selectable A selection.
     */
    public function limit($limit)
    {
        $selection = new SelectionBuilder($this->getSource());
        return $selection->limit($limit);
    }
}
