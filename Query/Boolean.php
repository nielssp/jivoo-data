<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query;

/**
 * An interface for combining expressions using boolean logic.
 * @method Boolean and(Expression|string $expr, mixed $vars,... ) AND operator.
 * @method Boolean or(Expression|string $expr, mixed $vars,... ) OR operator.
 */
interface Boolean {
  /**
   * Implements methods {@see Boolean::and()} and {@see Boolean::or()}.
   * @param string $method Method name ('and' or 'or')
   * @param mixed[] $args List of parameters
   * @return Expression Expression.
   */
  public function __call($method, $args);

  /**
   * Combine expression using AND operator.
   * @param Expression|string $expr Expression
   * @param mixed $vars,... Additional values to replace placeholders in
   * $expr with.
   * @return Expression Expression.
   */
  public function where($expr);

  /**
   * Combine expression using AND operator.
   * @param Expression|string $expr Expression
   * @param mixed $vars,... Additional values to replace placeholders in
   * $expr with.
   * @return Expression Expression.
   */
  public function andWhere($expr);

  /**
   * Combine expression using OR operator.
   * @param Expression|string $expr Expression
   * @param mixed $vars,... Additional values to replace placeholders in
   * $expr with.
   * @return Expression Expression.
   */
  public function orWhere($expr);
}
