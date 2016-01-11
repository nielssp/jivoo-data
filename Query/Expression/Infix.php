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
class Infix extends Expression {
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
    
  }

  /**
   * {@inheritDoc}
   */
  public function toString(Quoter $quoter) {
    
  }
}