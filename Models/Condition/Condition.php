<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models\Condition;

/**
 * A condition for selecting rows in a database table
 * @method Condition and(Condition|string $clause, mixed $vars,... ) AND operator
 * @method Condition or(Condition|string $clause, mixed $vars,... ) OR operator
 */
interface Condition {
  /**
   * Implements methods {@see Condition::and()} and {@see Condition::or()}
   * @param string $method Method name ('and' or 'or')
   * @param mixed[] $args List of parameters
   * @return self Self.
   */
  public function __call($method, $args);

  /**
   * If this condition has any clauses
   * @return bool True if more than 0 clauses, false otherwise
   */
  public function hasClauses();

  /**
   * Add clause with AND operator
   * @param Condition|string $clause Clause
   * @param mixed $vars,... Additional values to replace placeholders in
   * $clause with
   * @return self Self.
   */
  public function where($clause);

  /**
   * Add clause with AND operator
   * @param Condition|string $clause Clause
   * @param mixed $vars,... Additional values to replace placeholders in
   * $clause with
   * @return self Self.
   */
  public function andWhere($clause);

  /**
   * Add clause with OR operator
   * @param Condition|string $clause Clause
   * @param mixed $vars,... Additional values to replace placeholders in
   * $clause with
   * @return self Self.
   */
  public function orWhere($clause);
}
