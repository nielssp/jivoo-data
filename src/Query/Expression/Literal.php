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
class Literal implements Expression {
  public $type;
  public $value;
  
  public function __construct(DataType $type, $value) {
    $this->type = $type;
    $this->value = $value;
  }
  
  /**
   * {@inheritDoc}
   */
  public function __invoke(Record $record) {
    return $this->value;
  }

  /**
   * {@inheritDoc}
   */
  public function toString(Quoter $quoter) {
    return $quoter->quoteLiteral($this->type, $this->value);
  }
}