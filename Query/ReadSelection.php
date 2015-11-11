<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query;

/**
 * A read selection.
 */
interface ReadSelection extends Selection {
  /**
   * @return bool Distinct.
   */
  public function isDistinct();
  
  /**
   * @return string|null Alias for source.
   */
  public function getAlias();
  
  /**
   * @return string[] List of grouped columns.
   */
  public function getGrouping();
  
  /**
   * @return Predicate Grouping predicate.
   */
  public function getGroupPredicate();
  
  /**
   * return int Offset
   */
  public function getOffset();

  /**
   * List of arrays describing joins.
   *
   * Each array is of the following format:
   * <code>
   * array(
   *   'source' => ..., // Data source to join with ({@see DataSource})
   *   'type' => ..., // Type of join: 'INNER', 'RIGHT' or 'LEFT'
   *   'alias' => ..., // Alias for other data source (string|null)
   *   'predicate' => ... // Join predicate ({@see Expression})
   * );
   * </code>
   * @return array[]
   */
  public function getJoins();

  /**
   * List of projected fields.
   *
   * Each array is of the following format:
   * <code>
   * array(
   *   'expression' => ..., // Expression ({@see Expression})
   *   'alias' => ... // Optional alias (string|null)
   *   'type' => ... // Optional type hint (DataType|null)
   *   'source' => ... // Optional source (DataSource|null)
   *   'record' => ... // Optional record field (string|null)
   * )
   * </code>
   * @return array[]
  */
  public function getProjection();
}