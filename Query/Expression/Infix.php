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
class Infix implements Expression {
  public $left;
  public $operator;
  public $right;
  
  public function __construct(Expression $left, $operator, Expression $right = null) {
    $this->left = $left;
    $this->operator = $operator;
    $this->right = $right;
  }
  
  /**
   * {@inheritDoc}
   */
  public function __invoke(Record $record) {
    $left = $this->left->__invoke($record);
    $right = $this->right->__invoke($record);
    switch ($this->operator) {
      //"like" | "in" | "!=" | "<>" | ">=" | "<=" | "!<" | "!>" | "=" | "<" | ">"
      case 'like':
        return $left == $right; // TODO: should be case insensitive?
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
        return !($left < $right);
      case '!>':
        return !($left > $right);
      case '=':
        return $left == $right;
      case '<':
        return $left < $right;
      case '>':
        return $left > $right;
    }
    trigger_error(E_USER_ERROR, 'undefined operator: ' . $this->operator);
  }

  /**
   * {@inheritDoc}
   */
  public function toString(Quoter $quoter) {
    return $this->left->toString($quoter) . ' ' . $this->operator . ' ' . $this->right->toString($quoter);
  }
}