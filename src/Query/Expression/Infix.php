<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Expression;

use Jivoo\Data\Query\Expression;
use Jivoo\Data\Record;

/**
 * An infix operator.
 */
class Infix extends Node implements Expression
{

    /**
     * Left operand.
     *
     * @var Expression
     */
    public $left;

    /**
     * Operator.
     *
     * @var string
     */
    public $operator;

    /**
     * Right operand.
     *
     * @var Expression
     */
    public $right;

    public function __construct(Expression $left, $operator, Expression $right = null)
    {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Record $record)
    {
        $left = $this->left->__invoke($record);
        
        if ($this->operator == 'is' and $this->right === null) {
            return $left === null;
        }
        
        $right = $this->right->__invoke($record);
        // "like" | "in" | "!=" | "<>" | ">=" | "<=" | "!<" | "!>" | "=" | "<" | ">"
        switch ($this->operator) {
            case 'like':
                return strtolower($left) == strtolower($right);
            case 'in':
                return in_array($left, $right);
            case '!=':
                return $left != $right;
            case '<>':
                return $left != $right; // ??
            case '>=':
                return $left >= $right;
            case '<=':
                return $left <= $right;
            case '!<':
                return ! ($left < $right);
            case '!>':
                return ! ($left > $right);
            case '=':
                return $left == $right;
            case '<':
                return $left < $right;
            case '>':
                return $left > $right;
            case 'and':
                return $left and $right;
            case 'or':
                return $left or $right;
            default:
                trigger_error('undefined operator: ' . $this->operator, E_USER_ERROR);
        }
    } // @codeCoverageIgnore

    /**
     * {@inheritdoc}
     */
    public function toString(Quoter $quoter)
    {
        if (! ($this->left instanceof Atomic)) {
            return '(' . $this->left->toString($quoter) . ') '
                . $this->operator . ' ' . $this->right->toString($quoter);
        }
        return $this->left->toString($quoter) . ' '
            . $this->operator . ' ' . $this->right->toString($quoter);
    }
}
