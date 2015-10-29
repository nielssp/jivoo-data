<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query;

use Jivoo\Data\Selectable;

/**
 * A read selection.
 */
interface ReadSelection extends Selection, Selectable {
  /**
   * @var bool Distinct.
   */
  public function isDistinct();
  
  /**
   * @var string|null Alias for source.
   */
  public function getAlias();
  
  /**
   * An arrays describing grouping.
   * 
   * Each array is of the following format:
   * <code>
   * array(
   *   'columns' => ... // List of columns
   *   'condition' => ... // Join condition ({@see Condition})
   * )
   * </code>
   * @return array
   */
  public function getGrouping();
  
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
   *   'condition' => ... // Join condition ({@see Condition})
   * );
   * </code>
   * @return array[]
   */
  public function getJoins();

  /**
   * List of arrays describing columns.
   *
   * Each array is of the following format:
   * <code>
   * array(
   *   'expression' => ..., // Expression (string)
   *   'alias' => ... // Alias (string|null)
   * )
   * </code>
   * @return array[]
  */
  public function getFields();

  /**
   * List of arrays describing columns.
   *
   * Each array is of the following format:
   * <code>
   * array(
   *   'alias' => ... // Alias (string)
   *   'expression' => ..., // Expression (string)
   *   'type' => ... // Type (DataType|null)
   *   'model' => ... // Model (BasicModel|null)
   *   'record' => ... // Record field (string|null)
   * )
   * </code>
   * @return array[]
   */
  public function getAdditionalFields();
}