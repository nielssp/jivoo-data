<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Expression;

/**
 * A condition for selecting rows in a database table
 */
interface Expression {
  public function getString();
  
  public function getVars();

  /**
   * Implements methods {@see Condition::and()} and {@see Condition::or()}
   * @param string $method Method name ('and' or 'or')
   * @param mixed[] $args List of parameters
   * @return Expression Expression.
   */
  public function __call($method, $args);

  /**
   * Add clause with AND operator
   * @param Expression|string $expr Expression
   * @param mixed $vars,... Additional values to replace placeholders in
   * $expr with
   * @return Expression Expression.
   */
  public function where($expr);

  /**
   * Add clause with AND operator
   * @param Expression|string $expr Expression
   * @param mixed $vars,... Additional values to replace placeholders in
   * $expr with
   * @return Expression Expression.
   */
  public function andWhere($expr);

  /**
   * Add clause with OR operator
   * @param Expression|string $expr Expression
   * @param mixed $vars,... Additional values to replace placeholders in
   * $expr with
   * @return Expression Expression.
   */
  public function orWhere($expr);
}
