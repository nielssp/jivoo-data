<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Expression;

use Jivoo\Data\Query\Expression;

/**
 * An infix operator.
 */
interface Infix extends Expression {
  /**
   * Get the operator.
   * @return string Operator.
   */
  public function getOperator();

  /**
   * Left operand.
   * @return Expression
   */
  public function getLeft();

  /**
   * Right operand.
   * @return Expression
   */
  public function getRight();
}