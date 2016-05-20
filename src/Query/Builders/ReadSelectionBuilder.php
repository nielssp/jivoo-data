<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Builders;

use Jivoo\Data\Record;
use Jivoo\Data\DataType;
use Jivoo\Data\Query\Readable;
use Jivoo\Data\Query\ReadSelection;
use Jivoo\Data\Definition;
use Jivoo\Data\DataSource;
use Jivoo\Data\Query\Expression;
use Jivoo\Data\Query\Expression\ExpressionParser;

/**
 * A read selection.
 */
class ReadSelectionBuilder extends SelectionBase implements \IteratorAggregate, Readable, ReadSelection
{

    /**
     * @var bool
     */
    private $distinct = false;

    /**
     * @var string|null
     */
    private $alias = null;

    /**
     * @var string[]|null
     */
    private $grouping = null;

    /**
     * @var Expression|null
     */
    private $groupPredicate = null;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var array[]
     */
    private $joins = array();

    /**
     * @var array[]
     */
    private $projection = array();

    /**
     * @var array[]
     */
    private $additionalFields = array();

    /**
     * {@inheritdoc}
     */
    public function isDistinct()
    {
        return $this->distinct;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * {@inheritdoc}
     */
    public function getGrouping()
    {
        return $this->grouping;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupPredicate()
    {
        return $this->groupPredicate;
    }

    /**
     * {@inheritdoc}
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * {@inheritdoc}
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * {@inheritdoc}
     */
    public function getProjection()
    {
        return $this->projection;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalFields()
    {
        return $this->additionalFields;
    }

    /**
     * {@inheritdoc}
     */
    public function alias($alias)
    {
        $clone = clone $this;
        $clone->alias = $alias;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function select($expression, $alias = null)
    {
        $clone = clone $this;
        $clone->projection = array();
        if (is_array($expression)) {
            foreach ($expression as $alias => $expression) {
                if (! ($expression instanceof Expression)) {
                    $expression = new ExpressionParser($expression);
                }
                if (is_int($alias)) {
                    $clone->projection[] = array(
                        'expression' => $expression,
                        'alias' => null
                    );
                } else {
                    $clone->projection[] = array(
                        'expression' => $expression,
                        'alias' => $alias
                    );
                }
            }
        } else {
            if (! ($expression instanceof Expression)) {
                $expression = new ExpressionParser($expression);
            }
            $clone->projection[] = array(
                'expression' => $expression,
                'alias' => $alias
            );
        }
        $result = $clone->source->readSelection($clone);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function with($field, $expression, DataType $type = null)
    {
        $clone = clone $this;
        if (! ($expression instanceof Expression)) {
            $expression = new ExpressionParser($expression);
        }
        $clone->additionalFields[$field] = array(
            'alias' => $field,
            'expression' => $expression,
            'type' => $type
        );
        // TODO: virtual fields defined in source?
//        $clone->source->addVirtual($field, $type);
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withRecord($field, Definition $definition)
    {
        $clone = clone $this;
        foreach ($definition->getFields() as $schemaField) {
            $alias = $field . '_' . $schemaField;
            $clone->additionalFields[$alias] = array(
                'alias' => $alias,
                'expression' => $field . '.' . $schemaField,
                'type' => $definition->getType($schemaField),
                'definition' => $definition,
                'record' => $field,
                'recordField' => $schemaField
            );
        }
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function groupBy($columns, $predicate = null)
    {
        $clone = clone $this;
        if (! is_array($columns)) {
            $columns = array(
                $columns
            );
        }
        if (is_string($predicate)) {
            $predicate = new ExpressionParser($predicate);
        }
        $clone->grouping = $columns;
        $clone->groupPredicate = $predicate;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function innerJoin(DataSource $dataSource, $predicate = null, $alias = null)
    {
        $clone = clone $this;
        if (! ($predicate instanceof Expression)) {
            $predicate = new ExpressionParser($predicate);
        }
        $clone->joins[] = array(
            'source' => $dataSource,
            'type' => 'INNER',
            'alias' => $alias,
            'predicate' => $predicate
        );
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function leftJoin(DataSource $dataSource, $predicate, $alias = null)
    {
        $clone = clone $this;
        if (! ($predicate instanceof Expression)) {
            $predicate = new ExpressionParser($predicate);
        }
        $clone->joins[] = array(
            'source' => $dataSource,
            'type' => 'LEFT',
            'alias' => $alias,
            'condition' => $predicate
        );
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function rightJoin(DataSource $dataSource, $predicate, $alias = null)
    {
        $clone = clone $this;
        if (! ($predicate instanceof Expression)) {
            $predicate = new ExpressionParser($predicate);
        }
        $clone->joins[] = array(
            'source' => $dataSource,
            'type' => 'RIGHT',
            'alias' => $alias,
            'condition' => $predicate
        );
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function distinct($distinct = true)
    {
        $clone = clone $this;
        $clone->distinct = $distinct;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function offset($offset)
    {
        $clone = clone $this;
        $clone->offset = (int) $offset;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function first()
    {
        $result = $this->limit(1)->toArray();
        if (! isset($result[0])) {
            return null;
        }
        return $result[0];
    }

    /**
     * {@inheritdoc}
     */
    public function last()
    {
        $result = $this->reverseOrder()->limit(1)->toArray();
        if (! isset($result[0])) {
            return null;
        }
        return $result[0];
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->source->countSelection($this);
    }

    /**
     * {@inheritdoc}
     */
    public function rowNumber(Record $record)
    {
        return $this->source->rowNumberSelection($this, $record);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $array = array();
        foreach ($this as $record) {
            $array[] = $record;
        }
        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->source->openSelection($this);
    }
}
