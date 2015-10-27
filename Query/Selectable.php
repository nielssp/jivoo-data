<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

use Jivoo\Data\Query\Expression;

/**
 * An interface for models and selections.
 */
interface Selectable extends Expression, \IteratorAggregate, \Countable {
  /**
   * Order selection by a column or expression.
   * @param string|null $expression Expression or column. If null all ordering
   * will be removed from selection.
   * @return BasicSelection A selection.
   */
  public function orderBy($expression);
  
  /**
   * Order selection by a column or expression, in descending order.
   * @param string $expression Expression/column
   * @return BasicSelection A selection.
  */
  public function orderByDescending($expression);
  
  /**
   * Reverse the ordering.
   * @return BasicSelection A selection.
  */
  public function reverseOrder();
  
  /**
   * Limit number of records.
   * @param int Number of records.
   * @return BasicSelection A selection.
  */
  public function limit($limit);
  
  /**
   * Set alias for selection source.
   * @param string $alias Alias.
   * @return ReadSelection A read selection.
   */
  public function alias($alias);
  
  /**
   * Make a projection.
   * @param string|string[]|array $expression Expression or array of expressions
   * and aliases
   * @param string $alias Alias.
   * @return array[] List of associative arrays
   */
  public function select($expression, $alias = null);
  
  /**
   * Append an extra virtual field to the returned records.
   * @param string $alias Name of new field.
   * @param string $expression Expression for field, e.g. 'COUNT(*)'.
   * @param DataType|null $type Optional type of field.
   * @return ReadSelection A read selection.
   */
  public function with($field, $expression, DataType $type = null);

  /**
   * Append an extra virtual field (with a record as the value) to the returned
   * records.
   * @param string $alias Name of new field, expects the associated model to be
   * aliased with the same name.
   * @param BasicModel $model Model of associated record.
   * @return ReadSelection A read selection.
   */
  public function withRecord($field, BasicModel $model);
  
  /**
   * Group by one or more columns.
   * @param string|string[] $columns A single column name or a list of column
   * names.
   * @param Condition|string $condition Grouping condition.
   * @return ReadSelection A read selection.
   */
  public function groupBy($columns, $condition = null);

  /**
   * Perform an inner join with another model.
   * @param Model $other Other model.
   * @param string|Condition $condition Join condition.
   * @param string $alias Alias for joined model/table.
   * @return ReadSelection A read selection.
   */
  public function innerJoin(Model $other, $condition, $alias = null);
  /**
   * Perform a left join with another model.
   * @param Model $other Other model.
   * @param string|Condition $condition Join condition.
   * @param string $alias Alias for joined model/table.
   * @return ReadSelection A read selection.
   */
  public function leftJoin(Model $other, $condition, $alias = null);

  /**
   * Perform a right join with another model.
   * @param Model $other Other model.
   * @param string|Condition $condition Join condition.
   * @param string $alias Alias for joined model/table.
   * @return ReadSelection A read selection.
   */
  public function rightJoin(Model $other, $condition, $alias = null);

  /**
   * Fetch only distinct records (i.e. prevent duplicate records in result).
   * @param bool $distinct Whether to fetch only distinct records.
   * @return ReadSelection A read selection.
   */
  public function distinct($distinct = true);
  
  /**
   * Return first record in selection.
   * @return Record|null A record if available..
  */
  public function first();
  /**
   * Return last record in selection.
   * @return Record|null A record if available.
  */
  public function last();

  /**
   * Count number of records in selection.
   * @return int Number of records.
  */
//   public function count();
  
  /**
   * Convert selection to an array.
   * @return \Jivoo\Models\Record[] Array of records.
   */
  public function toArray();

  /**
   * Set offset.
   * @param int $offset Offset.
   * @return ReadSelection A read selection.
  */
  public function offset($offset);
}