<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query;

/**
 * An interface for models and selections.
 */
interface Selectable extends Boolean
{

    /**
     * Order selection by a column or expression.
     *
     * @param Expression|string|null $expression
     *            Expression or column.
     *            If null all ordering will be removed from selection.
     * @return static
     */
    public function orderBy($expr);

    /**
     * Order selection by a column or expression, in descending order.
     *
     * @param Expression|string $expression
     *            Expression or column.
     * @return static
     */
    public function orderByDescending($expr);

    /**
     * Reverse the ordering.
     *
     * @return static
     */
    public function reverseOrder();

    /**
     * Limit number of records.
     *
     * @param int $limit
     *            Number of records.
     * @return static
     */
    public function limit($limit);
}
