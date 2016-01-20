<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Common;

use Jivoo\Databases\Table;
use Jivoo\Core\App;
use Jivoo\Models\Schema;
use Jivoo\Models\Condition\ConditionBuilder;
use Jivoo\Models\Selection\ReadSelectionBuilder;
use Jivoo\Models\Selection\UpdateSelectionBuilder;
use Jivoo\Models\Selection\DeleteSelectionBuilder;
use Jivoo\Models\RecordBuilder;
use Jivoo\Models\Condition\NotCondition;
use Jivoo\Databases\InvalidTableException;
use Jivoo\Databases\QueryException;

/**
 * A table in an SQL database.
 */
class SqlTable extends Table {
  /**
   * @var SqlDatabase Owner database.
   */
  private $owner = null;

  /**
   * @var string Table name (without prefix etc.).
   */
  private $name = '';

  /**
   * @var Schema|null Table schema if set.
   */
  private $schema = null;
  
  /**
   * @var bool
   */
  private $caseInsensitive = false;

  /**
   * Construct table.
   * @param SqlDatabaseBase $database Owner database.
   * @param string $table Table name (without prefix etc.).
   */
  public function __construct(SqlDatabaseBase $database, $table) {
    $this->owner = $database;
    $this->name = $table;
    $this->schema = $this->owner->getSchema()->getSchema($table);
    $this->caseInsensitive = $this->owner->caseInsensitiveFields();
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return $this->schema;
  }

  /**
   * {@inheritdoc}
   */
  public function setSchema(Schema $schema) {
    $this->schema = $schema;
  }
  
  /**
   * {@inheritdoc}
   */
  public function createExisting($raw = array(), ReadSelectionBuilder $selection) {
    $typeAdapter = $this->owner->getTypeAdapter();
    $additional = $selection->additionalFields;
    $data = array();
    $virtual = array();
    $subrecords = array();
    if ($this->caseInsensitive) {
      $lower = array();
      foreach ($this->getFields() as $field)
        $lower[strtolower($field)] = $field;
      foreach ($additional as $field => $a)
        $lower[strtolower($field)] = $field;
    }
    foreach ($raw as $field => $value) {
      if (isset($lower) and isset($lower[$field]))
        $field = $lower[$field];
      if (isset($additional[$field])) {
        if (isset($additional[$field]['type']))
          $value = $typeAdapter->decode($additional[$field]['type'], $value);
        if (isset($additional[$field]['record'])) {
          $record = $additional[$field]['record'];
          if (!isset($subrecords[$record])) {
            $subrecords[$record] = array(
              'model' => $additional[$field]['model'],
              'null' => true,
              'data' => array()
            );
          }
          $subrecords[$record]['data'][$additional[$field]['recordField']] = $value;
          if (isset($value))
            $subrecords[$record]['null'] = false;
        }
        else {
          $virtual[$field] = $value;
        }
      }
      else {
        $type = $this->getType($field);
        if (!isset($type))
          throw new QueryException(tr(
            'Schema %1 does not contain field %2', $this->getName(), $field
          ));
        $data[$field] = $typeAdapter->decode($this->getType($field), $value);
      }
    }
    foreach ($subrecords as $field => $record) {
      if ($record['null']) {
        $virtual[$field] = null;
      }
      else {
        $virtual[$field] = RecordBuilder::createExisting($record['model'], $record['data']);
      }
    }
    return RecordBuilder::createExisting($this, $data, $virtual);
  }

  /**
   * Convert a condition to SQL.
   * @param ConditionBuilder $where The condition.
   * @return string SQL subquery.
   */
  protected function conditionToSql(ConditionBuilder $where) {
    return $where->toString($this->owner);
  }
  
  /**
   * Interpolate variables. See {@see Condition::interpolate}.
   * @param string $query Query.
   * @param array $vars Variables.
   * @return string Interpolated query.
   */
  protected function escapeQuery($query, $vars = array()) {
    if (!is_array($vars)) {
      $vars = func_get_args();
      array_shift($vars); 
    }
    return ConditionBuilder::interpolate($query, $vars, $this->owner);
  }
  
  /**
   * For use with array_walk(), will run {@see SqlTable::escapeQuery()} on
   * each column in an array. The input $value should be an associative array
   * as described in the documentation for {@see SelectQuery::$columns}.
   * The resulting $value will be a string.
   * @param array $value Array reference.
   * @param mixed $key Key (not used).
   */
  protected function getColumnList(&$value, $key) {
    $expression = $this->escapeQuery($value['expression'], array());
    if (isset($value['alias'])) {
      $value = $expression . ' AS ' . $value['alias'];
    }
    else {
      $value = $expression;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function countSelection(ReadSelectionBuilder $selection) {
    if (isset($selection->groupBy)) {
      $result = $this->owner->rawQuery(
        'SELECT COUNT(*) as _count FROM (' . $this->convertReadSelection($selection, '1') . ') AS _selection_count'
      );
      $row = $result->fetchAssoc();
      return $row['_count'];
    }
    else {
      $result = $selection->orderBy(null)->select('COUNT(*)', '_count');
      return intval($result[0]['_count']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function readSelection(ReadSelectionBuilder $selection) {
    return $this->owner->rawQuery($this->convertReadSelection($selection));
  }

  /**
   * Convert a read selection to an SQL query.
   * @param ReadSelectionBuilder $selection Read selection.
   * @param string|null $projection Projection override.
   * @return string SQL query.
   */
  private function convertReadSelection(ReadSelectionBuilder $selection, $projection = null) {
    $sqlString = 'SELECT ';
    if ($selection->distinct)
      $sqlString .= 'DISTINCT ';
    if (isset($projection)) {
      $sqlString .= $projection;
    }
    else if (!empty($selection->fields)) {
      $fields = $selection->fields;
      array_walk($fields, array($this, 'getColumnList'));
      $sqlString .= implode(', ', $fields);
    }
    else {
      if (isset($selection->alias))
        $sqlString .= $selection->alias . '.*';
      else
        $sqlString .= $this->owner->quoteModel($this->name) . '.*';
      if (!empty($selection->additionalFields)) {
        $fields = $selection->additionalFields;
        array_walk($fields, array($this, 'getColumnList'));
        $sqlString .= ', ' . implode(', ', $fields);
      }
    }
    $sqlString .= ' FROM ' . $this->owner->quoteModel($this->name);
    if (isset($selection->alias))
      $sqlString .= ' AS ' . $selection->alias; 
    if (!empty($selection->sources)) {
      foreach ($selection->sources as $source) {
        if (is_string($source['source'])) {
          $table = $source['source'];
        }
        else if ($source['source'] instanceof SqlTable) {
          $table = $source['source']->name;
        }
        else {
          continue;
        }
        $sqlString .= ', ' . $this->owner->quoteModel($table);
        if (isset($source['alias'])) {
          $sqlString .= ' AS ' . $source['alias'];
        }
      }
    }
    if (!empty($selection->joins)) {
      foreach ($selection->joins as $join) {
        $joinSource = $join['source']->asInstanceOf('Jivoo\Databases\Common\SqlTable');
        if (!isset($joinSource)) {
          throw new InvalidTableException(tr(
            'Unable to join SqlTable with data source of type "%1"',
            get_class($join['source'])
          ));
        }

        if ($joinSource->owner !== $this->owner) {
          throw new InvalidTableException(tr(
            'Unable to join SqlTable with table of different database'
          ));
        }
        $table = $joinSource->name;

        $sqlString .= ' ' . $join['type'] . ' JOIN ' . $this->owner->quoteModel($table);
        if (isset($join['alias'])) {
          $sqlString .= ' AS ' . $join['alias'];
        }
        if (isset($join['condition']) AND $join['condition']->hasClauses()) {
          $sqlString .= ' ON ' . $this->conditionToSql($join['condition']);
        }
      }
    }
    if ($selection->where->hasClauses()) {
      $sqlString .= ' WHERE ' . $this->conditionToSql($selection->where);
    }
    if (isset($selection->groupBy)) {
      $columns = array();
      foreach ($selection->groupBy['columns'] as $column) {
        $columns[] = $this->escapeQuery($column);
      }
      $sqlString .= ' GROUP BY ' . implode(', ', $columns);
      if (isset($selection->groupBy['condition'])
          AND $selection->groupBy['condition']->hasClauses()) {
        $sqlString .= ' HAVING '
          . $this->conditionToSql($selection->groupBy['condition']);
      }
    }
    if (!empty($selection->orderBy)) {
      $columns = array();
      foreach ($selection->orderBy as $orderBy) {
        $columns[] = $this->escapeQuery($orderBy['column'])
        . ($orderBy['descending'] ? ' DESC' : ' ASC');
      }
      $sqlString .= ' ORDER BY ' . implode(', ', $columns);
    }
    if (isset($selection->limit))
      $sqlString .= ' ' . $this->owner->sqlLimitOffset($selection->limit, $selection->offset);
    return $sqlString;
  }
  
  /**
   * {@inheritdoc}
   */
  public function updateSelection(UpdateSelectionBuilder $selection) {
    $typeAdapter = $this->owner->getTypeAdapter();
    $sqlString = 'UPDATE ' . $this->owner->quoteModel($this->name);
    $sets = $selection->sets;
    if (!empty($sets)) {
      $sqlString .= ' SET';
      reset($sets);
      $first = true;
      foreach ($sets as $key => $value) {
        if ($first) {
          $first = false;
        }
        else {
          $sqlString .= ',';
        }
        if (strpos($key, '=') !== false) {
          $sqlString .= ' ' . $this->escapeQuery($key, $value);
        }
        else {
          $sqlString .= ' ' . $key . ' = ';
          $sqlString .= $typeAdapter->encode($this->getType($key), $value);
        }
      }
    }
    if ($selection->where->hasClauses()) {
      $sqlString .= ' WHERE ' . $this->conditionToSql($selection->where);
    }
    if (!empty($selection->orderBy)) {
      $columns = array();
      foreach ($selection->orderBy as $orderBy) {
        $columns[] = $this->escapeQuery($orderBy['column'])
          . ($orderBy['descending'] ? ' DESC' : ' ASC');
      }
      $sqlString .= ' ORDER BY ' . implode(', ', $columns);
    }
    if (isset($selection->limit))
      $sqlString .= ' ' . $this->owner->sqlLimitOffset($selection->limit);
    return $this->owner->rawQuery($sqlString);
  }
  
  /**
   * {@inheritdoc}
   */
  public function deleteSelection(DeleteSelectionBuilder $selection) {
    $sqlString = 'DELETE FROM ' . $this->owner->quoteModel($this->name);
    if ($selection->where->hasClauses()) {
      $sqlString .= ' WHERE ' . $this->conditionToSql($selection->where);
    }
    if (!empty($selection->orderBy)) {
      $columns = array();
      foreach ($selection->orderBy as $orderBy) {
        $columns[] = $this->escapeQuery($orderBy['column'])
          . ($orderBy['descending'] ? ' DESC' : ' ASC');
      }
      $sqlString .= ' ORDER BY ' . implode(', ', $columns);
    }
    if (isset($selection->limit))
      $sqlString .= ' ' . $this->owner->sqlLimitOffset($selection->limit);
    return $this->owner->rawQuery($sqlString);
  }

  /**
   * {@inheritdoc}
   */
  public function insert($data, $replace = false) {
    return $this->insertMultiple(array($data), $replace);
  }

  /**
   * {@inheritdoc}
   */
  public function insertMultiple($records, $replace = false) {
    if (count($records) == 0)
      return null;
    $typeAdapter = $this->owner->getTypeAdapter();
    $columns = array_keys($records[0]);
    if ($replace)
      $sqlString = 'REPLACE';
    else
      $sqlString = 'INSERT';
    $sqlString .= ' INTO ' . $this->owner->quoteModel($this->name) . ' (';
    $sqlString .= implode(', ', $columns);
    $sqlString .= ') VALUES ';
    $tuples = array();
    foreach ($records as $data) {
      $first = true;
      $tupleSql = '(';
      foreach ($data as $column => $value) {
        if ($first)
          $first = false;
        else
          $tupleSql .= ', ';
        $tupleSql .= $typeAdapter->encode($this->getType($column), $value);
      }
      $tupleSql .= ')';
      $tuples[] = $tupleSql;
    }
    $sqlString .= implode(', ', $tuples);
    return $this->owner->rawQuery($sqlString, $this->getAiPrimaryKey());
  }
  
}
