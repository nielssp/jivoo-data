<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query;

/**
 * An undecided selection.
 */
interface Selection extends Selectable, Updatable, Deletable {
  /**
   * List of arrays describing ordering.
   *
   * Each array is of the format:
   * <code>
   * array(
   *   'column' => ..., // Column name (string)
   *   'descending' => .... // Whether or not to order in descending order (bool)
   * )
   * </code>
   * @var array[]
   */
  public function getOrdering();
  
  /**
   * @var int|null Limit.
  */
  public function getLimit();
  
  /**
   * @var Expression Select condition.
   */
  public function getExpression();
  
  /**
   * @var Model
   */
  public function getModel();
}