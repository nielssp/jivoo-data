<?php

// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database\Common;

use Jivoo\Data\Database\Table;
use Jivoo\App;
use Jivoo\Models\Schema;
use Jivoo\Models\Condition\ConditionBuilder;
use Jivoo\Models\Selection\ReadSelectionBuilder;
use Jivoo\Models\Selection\UpdateSelectionBuilder;
use Jivoo\Models\Selection\DeleteSelectionBuilder;
use Jivoo\Models\RecordBuilder;
use Jivoo\Models\Condition\NotCondition;
use Jivoo\Data\Database\InvalidTableException;
use Jivoo\Data\Database\QueryException;

/**
 * A table in an SQL database.
 */
class SqlTable implements Table
{

    /**
     * @var SqlDatabase Owner database.
     */
    private $owner;

    /**
     * @var string Table name (without prefix etc.).
     */
    private $name = '';

    /**
     * @var bool
     */
    private $caseInsensitive = false;
    
    /**
     * @var \Jivoo\Data\Definition
     */
    private $definition;

    /**
     * Construct table.
     *
     * @param SqlDatabase $database
     *            Owner database.
     * @param string $table
     *            Table name (without prefix etc.).
     */
    public function __construct(SqlDatabase $database, $table)
    {
        $this->owner = $database;
        $this->name = $table;
        $this->caseInsensitive = $this->owner->caseInsensitiveFields();
        $this->definition = $this->owner->getDefinition()->getDefinition($this->name);
    }
    
    protected function getType($field)
    {
        return $this->definition->getType($field);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function exists()
    {
        return $this->owner->tableExists($this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $this->owner->createTable($this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function drop()
    {
        $this->owner->dropTable($this->name);
    }

    /**
     * Convert a condition to SQL.
     *
     * @param ConditionBuilder $where
     *            The condition.
     * @return string SQL subquery.
     */
    protected function conditionToSql(ConditionBuilder $where)
    {
        return $where->toString($this->owner);
    }

    /**
     * Interpolate variables.
     * See {@see Condition::interpolate}.
     *
     * @param string $query
     *            Query.
     * @param array $vars
     *            Variables.
     * @return string Interpolated query.
     */
    protected function escapeQuery($query, $vars = array())
    {
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
        }
        return \Jivoo\Data\Query\E::interpolate($query, $vars, $this->owner);
    }

    /**
     * For use with array_walk(), will run {@see SqlTable::escapeQuery()} on
     * each column in an array.
     * The input $value should be an associative array
     * as described in the documentation for {@see SelectQuery::$columns}.
     * The resulting $value will be a string.
     *
     * @param array $value
     *            Array reference.
     * @param mixed $key
     *            Key (not used).
     */
    protected function getColumnList(&$value, $key)
    {
        $expression = $this->escapeQuery($value['expression'], array());
        if (isset($value['alias'])) {
            $value = $expression . ' AS ' . $value['alias'];
        } else {
            $value = $expression;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function countSelection(\Jivoo\Data\Query\ReadSelection $selection)
    {
        $group = $selection->getGrouping();
        if (count($group)) {
            $result = $this->owner->query(
                'SELECT COUNT(*) as _count FROM ('
                . $this->convertReadSelection($selection, '1') . ') AS _selection_count'
            );
            $row = $result->fetchAssoc();
            return $row['_count'];
        } else {
            $result = $selection->orderBy(null)->select('COUNT(*)', '_count');
            return intval($result[0]['_count']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function readSelection(\Jivoo\Data\Query\ReadSelection $selection)
    {
        return $this->owner->query($this->convertReadSelection($selection));
    }

    /**
     * Convert a read selection to an SQL query.
     *
     * @param ReadSelectionBuilder $selection
     *            Read selection.
     * @param string|null $projection
     *            Projection override.
     * @return string SQL query.
     */
    private function convertReadSelection(\Jivoo\Data\Query\ReadSelection $selection, $projection = null)
    {
        $sqlString = 'SELECT ';
        if ($selection->isDistinct()) {
            $sqlString .= 'DISTINCT ';
        }
        if (isset($projection)) {
            $sqlString .= $projection;
        } elseif (!empty($selection->get)) {
            $fields = $selection->fields;
            array_walk($fields, array(
                $this,
                'getColumnList'
            ));
            $sqlString .= implode(', ', $fields);
        } else {
            if (isset($selection->alias)) {
                $sqlString .= $selection->alias . '.*';
            } else {
                $sqlString .= $this->owner->quoteModel($this->name) . '.*';
            }
            if (!empty($selection->additionalFields)) {
                $fields = $selection->additionalFields;
                array_walk($fields, array(
                    $this,
                    'getColumnList'
                ));
                $sqlString .= ', ' . implode(', ', $fields);
            }
        }
        $sqlString .= ' FROM ' . $this->owner->quoteModel($this->name);
        if (isset($selection->alias)) {
            $sqlString .= ' AS ' . $selection->alias;
        }
        if (!empty($selection->sources)) {
            foreach ($selection->sources as $source) {
                if (is_string($source['source'])) {
                    $table = $source['source'];
                } elseif ($source['source'] instanceof SqlTable) {
                    $table = $source['source']->name;
                } else {
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
                $joinSource = $join['source']->asInstanceOf('Jivoo\Data\Database\Common\SqlTable');
                if (!isset($joinSource)) {
                    throw new InvalidTableException(
                        'Unable to join SqlTable with data source of type "' . get_class($join['source']) . '"'
                    );
                }

                if ($joinSource->owner !== $this->owner) {
                    throw new InvalidTableException(
                        'Unable to join SqlTable with table of different database'
                    );
                }
                $table = $joinSource->name;

                $sqlString .= ' ' . $join['type'] . ' JOIN ' . $this->owner->quoteModel($table);
                if (isset($join['alias'])) {
                    $sqlString .= ' AS ' . $join['alias'];
                }
                if (isset($join['condition']) and $join['condition']->hasClauses()) {
                    $sqlString .= ' ON ' . $join['condition']->toString($this->owner);
                }
            }
        }
        if ($selection->where->hasClauses()) {
            $sqlString .= ' WHERE ' . $selection->where->toString($this->owner);
        }
        if (isset($selection->groupBy)) {
            $columns = array();
            foreach ($selection->groupBy['columns'] as $column) {
                $columns[] = $this->escapeQuery($column);
            }
            $sqlString .= ' GROUP BY ' . implode(', ', $columns);
            if (isset($selection->groupBy['condition']) and $selection->groupBy['condition']->hasClauses()) {
                $sqlString .= ' HAVING ' . $this->conditionToSql($selection->groupBy['condition']);
            }
        }
        $ordering = $selection->getOrdering();
        if (count($ordering)) {
            $columns = array();
            foreach ($ordering as $orderBy) {
                $columns[] = $this->escapeQuery($orderBy['column']) . ($orderBy['descending'] ? ' DESC' : ' ASC');
            }
            $sqlString .= ' ORDER BY ' . implode(', ', $columns);
        }
        $limit = $selection->getLimit();
        if (isset($limit)) {
            $sqlString .= ' ' . $this->owner->sqlLimitOffset($limit);
        }
        return $sqlString;
    }

    /**
     * {@inheritdoc}
     */
    public function updateSelection(\Jivoo\Data\Query\UpdateSelection $selection)
    {
        $typeAdapter = $this->owner->getTypeAdapter();
        $sqlString = 'UPDATE ' . $this->owner->quoteModel($this->name);
        $sets = $selection->getData();
        if (!empty($sets)) {
            $sqlString .= ' SET';
            reset($sets);
            $first = true;
            foreach ($sets as $key => $value) {
                if ($first) {
                    $first = false;
                } else {
                    $sqlString .= ',';
                }
                if (strpos($key, '=') !== false) {
                    $sqlString .= ' ' . $this->escapeQuery($key, $value);
                } else {
                    $sqlString .= ' ' . $key . ' = ';
                    $sqlString .= $typeAdapter->encode($this->getType($key), $value);
                }
            }
        }
        if ($selection->getPredicate() !== null) {
            $sqlString .= ' WHERE ' . $selection->getPredicate()->toString($this->owner);
        }
        $ordering = $selection->getOrdering();
        if (count($ordering)) {
            $columns = array();
            foreach ($ordering as $orderBy) {
                $columns[] = $this->escapeQuery($orderBy['column']) . ($orderBy['descending'] ? ' DESC' : ' ASC');
            }
            $sqlString .= ' ORDER BY ' . implode(', ', $columns);
        }
        $limit = $selection->getLimit();
        if (isset($limit)) {
            $sqlString .= ' ' . $this->owner->sqlLimitOffset($limit);
        }
        return $this->owner->execute($sqlString);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSelection(\Jivoo\Data\Query\Selection $selection)
    {
        $sqlString = 'DELETE FROM ' . $this->owner->quoteModel($this->name);
        if ($selection->getPredicate() !== null) {
            $sqlString .= ' WHERE ' . $selection->getPredicate()->toString($this->owner);
        }
        $ordering = $selection->getOrdering();
        if (count($ordering)) {
            $columns = array();
            foreach ($ordering as $orderBy) {
                $columns[] = $this->escapeQuery($orderBy['column']) . ($orderBy['descending'] ? ' DESC' : ' ASC');
            }
            $sqlString .= ' ORDER BY ' . implode(', ', $columns);
        }
        $limit = $selection->getLimit();
        if (isset($limit)) {
            $sqlString .= ' ' . $this->owner->sqlLimitOffset($limit);
        }
        return $this->owner->execute($sqlString);
    }

    /**
     * {@inheritdoc}
     */
    public function insert(array $data, $replace = false)
    {
        return $this->insertMultiple(array(
            $data
        ), $replace);
    }

    /**
     * {@inheritdoc}
     */
    public function insertMultiple(array $records, $replace = false)
    {
        if (count($records) == 0) {
            return null;
        }
        $typeAdapter = $this->owner->getTypeAdapter();
        $columns = array_keys($records[0]);
        if ($replace) {
            $sqlString = 'REPLACE';
        } else {
            $sqlString = 'INSERT';
        }
        $sqlString .= ' INTO ' . $this->owner->quoteModel($this->name) . ' (';
        $sqlString .= implode(', ', $columns);
        $sqlString .= ') VALUES ';
        $tuples = array();
        foreach ($records as $data) {
            $first = true;
            $tupleSql = '(';
            foreach ($data as $column => $value) {
                if ($first) {
                    $first = false;
                } else {
                    $tupleSql .= ', ';
                }
                $tupleSql .= $typeAdapter->encode($this->getType($column), $value);
            }
            $tupleSql .= ')';
            $tuples[] = $tupleSql;
        }
        $sqlString .= implode(', ', $tuples);
        return $this->owner->insert($sqlString, $this->getAiPrimaryKey());
    }

    /**
     * {@inheritdoc}
     */
    public function joinWith(\Jivoo\Data\DataSource $other)
    {
        if ($other instanceof SqlTable) {
            return $this;
        }
        return null;
    }
}
