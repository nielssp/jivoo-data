<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query;

/**
 * A record selection with a predicate, an ordering and a limit.
 */
interface Selection {
  /**
   * @return Expression
   */
  public function getPredicate();

  /**
   * List of 2-tuples describing ordering. Each tuple consists of a string
   * (the field name) and a bool (true if descending order, false if ascending).
   * @return array[]
   */
  public function getOrdering();
  
  /**
   * Optional selection limit.
   * @return int|null Limit.
  */
  public function getLimit();
}