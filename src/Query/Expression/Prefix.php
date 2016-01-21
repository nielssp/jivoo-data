<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Expression;

use Jivoo\Data\Query\Expression;
use Jivoo\Data\Record;

/**
 * A prefix operator.
 */
class Prefix implements Expression
{

    public $operator;

    public $operand;

    public function __construct($operator, Expression $operand)
    {
        $this->operator = $operator;
        $this->operand = $operand;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Record $record)
    {
        if ($this->operator == 'not') {
            return ! $this->operand->__invoke($record);
        }
        trigger_error(E_USER_ERROR, 'undefined operator: ' . $this->operator);
    }

    /**
     * {@inheritdoc}
     */
    public function toString(Quoter $quoter)
    {
        return $this->operator . ' ' . $this->operand->toString($quoter);
    }
}
