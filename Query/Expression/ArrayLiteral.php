<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Expression;

use Jivoo\Data\Query\Expression;
use Jivoo\Data\DataType;
use Jivoo\Data\Record;

/**
 * A literal.
 */
class ArrayLiteral implements Expression {
  public $type;
  public $values;
  
  public function __construct(DataType $type, $values) {
    $this->type = $type;
    $this->values = $values;
  }
  
  /**
   * {@inheritDoc}
   */
  public function __invoke(Record $record) {
    return $this->values;
  }

  /**
   * {@inheritDoc}
   */
  public function toString(Quoter $quoter) {
    $values = $this->values;
    foreach ($values as $key => $v)
      $values[$key] = $quoter->quoteLiteral($this->type, $v);
    return '(' . implode(', ', $values) . ')';
  }
}