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
class Prefix extends Expression {
  public $operator;
  public $operand;
  
  public function __construct($operator, Expression $operand) {
    $this->operator = $operator;
    $this->operand = $operand;
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