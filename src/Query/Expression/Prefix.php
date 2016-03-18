<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Expression;

use Jivoo\Data\Query\Expression;

/**
 * A prefix operator.
 */
class Prefix extends Node implements Expression
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
    public function __invoke(array $data)
    {
        if ($this->operator == 'not') {
            return ! $this->operand->__invoke($data);
        }
        trigger_error('undefined operator: ' . $this->operator, E_USER_ERROR);
    } // @codeCoverageIgnore

    /**
     * {@inheritdoc}
     */
    public function toString(Quoter $quoter)
    {
        if (! ($this->operand instanceof Atomic)) {
            return $this->operator . ' (' . $this->operand->toString($quoter) . ')';
        }
        return $this->operator . ' ' . $this->operand->toString($quoter);
    }
}
