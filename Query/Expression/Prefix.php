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
interface Prefix extends Expression {
  /**
   * Get the operator.
   * @return string Operator.
   */
  public function getOperator();

  /**
   * Operand.
   * @return Expression
   */
  public function getOperand();
}