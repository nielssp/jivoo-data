<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database\Common;

use Jivoo\Data\Database\InvalidTableException;
use Jivoo\Data\Database\ResultSetIterator;
use Jivoo\Data\Database\Table;
use Jivoo\Data\DataSource;
use Jivoo\Data\Definition;
use Jivoo\Data\Query\E;
use Jivoo\Data\Query\ReadSelection;
use Jivoo\Data\Query\Selection;
use Jivoo\Data\Query\UpdateSelection;
use Jivoo\Models\Condition\ConditionBuilder;
use Jivoo\Models\Selection\ReadSelectionBuilder;

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
     * @var Definition
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
        $this->definition = $this->owner->getDefinition()->getDefinition($this->name);
    }
    
    protected function getType($field)
    {
        return $this->definition->getType($field);
    }
    
    protected function getAiPrimaryKey()
    {
        $pk = $this->definition->getPrimaryKey();
        if (count($pk) == 1) {
            if ($this->definition->getType($pk[0])->serial) {
                return $pk[0];
            }
        }
        return null;
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
        return E::interpolate($query, $vars, $this->owner);
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
        $expression = $value['expression']->toString($this->owner);
        if (isset($value['alias'])) {
            $value = $expression . ' AS ' . $value['alias'];
        } else {
            $value = $expression;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function countSelection(ReadSelection $selection)
    {
        $group = $selection->getGrouping();
        if (count($group)) {
            $result = $this->owner->query(
                'SELECT COUNT(*) AS _count FROM ('
                . $this->convertReadSelection($selection, '1') . ') AS _selection_count'
            );
            $row = $result->fetchAssoc();
            return $row['_count'];
        } else {
            $result = iterator_to_array($selection->orderBy(null)->select('COUNT(*)', '_count'));
            return intval($result[0]['_count']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function readSelection(ReadSelection $selection)
    {
        return new ResultSetIterator($this->owner->query($this->convertReadSelection($selection)));
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
    private function convertReadSelection(ReadSelection $selection, $projection = null)
    {
        $sqlString = 'SELECT ';
        $alias = $selection->getAlias();
        if ($selection->isDistinct()) {
            $sqlString .= 'DISTINCT ';
        }
        if (isset($projection)) {
            $sqlString .= $projection;
        } elseif (count($selection->getProjection())) {
            $fields = $selection->getProjection();
            array_walk($fields, array(
                $this,
                'getColumnList'
            ));
            $sqlString .= implode(', ', $fields);
        } else {
            if (isset($alias)) {
                $sqlString .= $alias . '.*';
            } else {
                $sqlString .= $this->owner->quoteModel($this->name) . '.*';
            }
            $additional = $selection->getAdditionalFields();
            if (count($additional)) {
                array_walk($additional, array(
                    $this,
                    'getColumnList'
                ));
                $sqlString .= ', ' . implode(', ', $additional);
            }
        }
        $sqlString .= ' FROM ' . $this->owner->quoteModel($this->name);
        if (isset($alias)) {
            $sqlString .= ' AS ' . $alias;
        }
//        if (!empty($selection->sources)) {
//            foreach ($selection->sources as $source) {
//                if (is_string($source['source'])) {
//                    $table = $source['source'];
//                } elseif ($source['source'] instanceof SqlTable) {
//                    $table = $source['source']->name;
//                } else {
//                    continue;
//                }
//                $sqlString .= ', ' . $this->owner->quoteModel($table);
//                if (isset($source['alias'])) {
//                    $sqlString .= ' AS ' . $source['alias'];
//                }
//            }
//        }
        $joins = $selection->getJoins();
        if (count($joins)) {
            foreach ($joins as $join) {
                $joinSource = $join['source']->joinWith($this);
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
                if (isset($join['condition'])) {
                    $sqlString .= ' ON ' . $join['condition']->toString($this->owner);
                }
            }
        }
        if ($selection->getPredicate() !== null) {
            $sqlString .= ' WHERE ' . $selection->getPredicate()->toString($this->owner);
        }
        $grouping = $selection->getGrouping();
        if (count($grouping)) {
            $columns = array();
            foreach ($grouping as $column) {
                $columns[] = $this->escapeQuery($column);
            }
            $sqlString .= ' GROUP BY ' . implode(', ', $columns);
            $predicate = $selection->getGroupPredicate();
            if (isset($predicate)) {
                $sqlString .= ' HAVING ' . $predicate->toString($this->owner);
            }
        }
        $ordering = $selection->getOrdering();
        if (count($ordering)) {
            $columns = array();
            foreach ($ordering as $orderBy) {
                $columns[] = $this->escapeQuery($orderBy[0]) . ($orderBy[1] ? ' DESC' : ' ASC');
            }
            $sqlString .= ' ORDER BY ' . implode(', ', $columns);
        }
        $limit = $selection->getLimit();
        if (isset($limit)) {
            $sqlString .= ' ' . $this->owner->sqlLimitOffset($limit, $selection->getOffset());
        }
        return $sqlString;
    }

    /**
     * {@inheritdoc}
     */
    public function updateSelection(UpdateSelection $selection)
    {
        $typeAdapter = $this->owner->getTypeAdapter();
        $sqlString = 'UPDATE ' . $this->owner->quoteModel($this->name);
        $data = $selection->getData();
        if (count($data)) {
            $sqlString .= ' SET';
            $first = true;
            foreach ($data as $key => $value) {
                if ($first) {
                    $first = false;
                } else {
                    $sqlString .= ',';
                }
                $sqlString .= ' ' . $key . ' = ';
                if ($value instanceof \Jivoo\Data\Query\Expression) {
                    $sqlString .= $value->toString($this->owner);
                } else {
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
                $columns[] = $this->escapeQuery($orderBy[0]) . ($orderBy[1] ? ' DESC' : ' ASC');
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
    public function deleteSelection(Selection $selection)
    {
        $sqlString = 'DELETE FROM ' . $this->owner->quoteModel($this->name);
        if ($selection->getPredicate() !== null) {
            $sqlString .= ' WHERE ' . $selection->getPredicate()->toString($this->owner);
        }
        $ordering = $selection->getOrdering();
        if (count($ordering)) {
            $columns = array();
            foreach ($ordering as $orderBy) {
                $columns[] = $this->escapeQuery($orderBy[0]) . ($orderBy[1] ? ' DESC' : ' ASC');
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
    public function joinWith(DataSource $other)
    {
        if ($other instanceof SqlTable) {
            return $this;
        }
        return null;
    }
}
