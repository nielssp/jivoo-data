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
class FieldAccess implements Expression {
  public $field;
  public $model;
  
  public function __construct($field, $model = null) {
    $this->field = $field;
    $this->model = $model;
  }
  
  /**
   * {@inheritDoc}
   */
  public function __invoke(Record $record) {
    $field = $this->field;
    return $record->$field;
  }

  /**
   * {@inheritDoc}
   */
  public function toString(Quoter $quoter) {
    if (isset($this->model))
      return $quoter->quoteModel($this->model) . '.' . $quoter->quoteField($this->field);
    return $quoter->quoteField($this->field);
  }
}