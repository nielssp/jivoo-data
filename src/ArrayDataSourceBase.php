<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

use Jivoo\Data\Query\Selection;
use Jivoo\Data\Query\UpdateSelection;
use Jivoo\Data\Query\ReadSelection;
use Jivoo\Assume;

/**
 * A data source based on an array of records.
 */
abstract class ArrayDataSourceBase implements DataSource
{

    /**
     * Array of records. The keys of the array are used for {@see deleteKey} and
     * {@see updateKey}.
     *
     * @return Record[]
     */
    abstract public function getData();

    /**
     * Delete record associated with key.
     *
     * @param mixed $key Record key.
     */
    abstract public function deleteKey($key);

    /**
     * Update record associated with key.
     *
     * @param mixed $key Record key.
     * @param array $record New record data.
     */
    abstract public function updateKey($key, array $record);

    /**
     * {@inheritdoc}
     */
    public function read(ReadSelection $selection)
    {
        $data = $this->getData();
        if (count($selection->getJoins()) > 0) {
            throw new \Exception('unsupported operation');
        }
        $predicate = $selection->getPredicate();
        if (isset($predicate)) {
            $data = new PredicateArray($data, $predicate);
        }
        $grouping = $selection->getGrouping();
        if (count($grouping)) {
            $data = self::sortAll($data, array_map(function ($column) {
                return [$column, false];
            }, $grouping));
            $previous = null;
            $data = array_filter($data, function (Record $record) use ($grouping, &$previous) {
                $group = array_intersect_key($record->getData(), array_flip($grouping));
                if (isset($previous) and $group == $previous) {
                    return false;
                }
                $previous = $group;
                return true;
            });
            $predicate = $selection->getGroupPredicate();
            if (isset($predicate)) {
                $data = new PredicateArray($data, $predicate);
            }
        }
        $data = self::sortAll($data, $selection->getOrdering());
        $limit = $selection->getLimit();
        $offset = $selection->getOffset();
        if (isset($limit)) {
            $data = array_slice($data, $offset, $limit);
        }
        $projection = $selection->getProjection();
        if (count($projection)) {
            $projected = array();
            foreach ($data as $key => $record) {
                $recordData = array();
                $i = 0;
                foreach ($projection as $field) {
                    if (isset($field['alias'])) {
                        $alias = $field['alias'];
                    } else {
                        $alias = 'expr#' . $i++;
                    }
                    $recordData[$alias] = $field['expression']->__invoke($record);
                }
                $projected[] = new ArrayRecord($recordData);
            }
            $data = $projected;
        }
        if ($data instanceof \Iterator) {
            return $data;
        }
        return new \ArrayIterator($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateSelection $selection)
    {
        $data = $this->getData();
        $data = self::sortAll($data, $selection->getOrdering());
        $updates = $selection->getData();
        $limit = $selection->getLimit();
        $predicate = $selection->getPredicate();
        $count = 0;
        foreach ($data as $key => $record) {
            if ($predicate === null or $predicate($record)) {
                $this->updateKey($key, array_merge($record->getData(), $updates));
                $count++;
                if (isset($limit) and $count >= $limit) {
                    break;
                }
            }
        }
        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Selection $selection)
    {
        $data = $this->getData();
        $data = self::sortAll($data, $selection->getOrdering());
        $limit = $selection->getLimit();
        $predicate = $selection->getPredicate();
        $count = 0;
        foreach ($data as $key => $record) {
            if ($predicate === null or $predicate($record)) {
                $this->deleteKey($key);
                $count++;
                if (isset($limit) and $count >= $limit) {
                    break;
                }
            }
        }
        return $count;
    }
    
    /**
     * {@inheritdoc}
     */
    public function joinWith(DataSource $other)
    {
        return null;
    }

    public static function sortAll($data, $orderings, $assoc = true)
    {
        if (! count($orderings)) {
            return $data;
        }
        Assume::isArray($data);
        $func = function (Record $a, Record $b) use ($orderings) {
            foreach ($orderings as $ordering) {
                list($field, $descending) = $ordering;
                if ($a->$field == $b->$field) {
                    continue;
                }
                if ($descending) {
                    if (is_numeric($a->$field)) {
                        return $b->$field - $a->$field;
                    }
                    return strcmp($b->$field, $a->$field);
                } else {
                    if (is_numeric($a->$field)) {
                        return $a->$field - $b->$field;
                    }
                    return strcmp($a->$field, $b->$field);
                }
            }
        };
        if ($assoc) {
            uasort($data, $func);
        } else {
            usort($data, $func);
        }
        return $data;
    }

    public static function sort($data, $field, $descending = false, $assoc = true)
    {
        Assume::isArray($data);
        $func = function (Record $a, Record $b) use ($field, $descending) {
            if ($a->$field == $b->$field) {
                return 0;
            }
            if ($descending) {
                if (is_numeric($a->$field)) {
                    return $b->$field - $a->$field;
                }
                return strcmp($b->$field, $a->$field);
            } else {
                if (is_numeric($a->$field)) {
                    return $a->$field - $b->$field;
                }
                return strcmp($a->$field, $b->$field);
            }
        };
        if ($assoc) {
            uasort($data, $func);
        } else {
            usort($data, $func);
        }
        return $data;
    }
}
