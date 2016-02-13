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
use Jivoo\Data\Schema;
use Jivoo\Data\DataSource;
use Jivoo\Data\Query\Expression;
use Jivoo\Data\Query\Expression\ExpressionParser;

/**
 * A read selection.
 */
class ReadSelectionBuilder extends SelectionBase implements Readable, ReadSelection
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
        $this->alias = $alias;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function select($expression, $alias = null)
    {
        $this->projection = array();
        if (is_array($expression)) {
            foreach ($expression as $alias => $expression) {
                if (! ($expression instanceof Expression)) {
                    $expression = new ExpressionParser($expression);
                }
                if (is_int($alias)) {
                    $this->projection[] = array(
                        'expression' => $expression,
                        'alias' => null
                    );
                } else {
                    $this->projection[] = array(
                        'expression' => $expression,
                        'alias' => $alias
                    );
                }
            }
        } else {
            if (! ($expression instanceof Expression)) {
                $expression = new ExpressionParser($expression);
            }
            $this->projection[] = array(
                'expression' => $expression,
                'alias' => $alias
            );
        }
        $result = $this->source->readSelection($this);
        $this->projection = array();
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function with($field, $expression, DataType $type = null)
    {
        $this->additionalFields[$field] = array(
            'alias' => $field,
            'expression' => $expression,
            'type' => $type
        );
        $this->source->addVirtual($field, $type);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withRecord($field, Schema $schema)
    {
        foreach ($schema->getFields() as $schemaField) {
            $alias = $field . '_' . $schemaField;
            $this->additionalFields[$alias] = array(
                'alias' => $alias,
                'expression' => $field . '.' . $schemaField,
                'type' => $schema->getType($schemaField),
                'schema' => $schema,
                'record' => $field,
                'recordField' => $schemaField
            );
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function groupBy($columns, $predicate = null)
    {
        if (! is_array($columns)) {
            $columns = array(
                $columns
            );
        }
        if (is_string($predicate)) {
            $predicate = new ExpressionParser($predicate);
        }
        $this->grouping = $columns;
        $this->groupPredicate = $predicate;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function innerJoin(DataSource $dataSource, $predicate = null, $alias = null)
    {
        if (! ($predicate instanceof Expression)) {
            $predicate = new ExpressionParser($predicate);
        }
        $this->joins[] = array(
            'source' => $dataSource,
            'type' => 'INNER',
            'alias' => $alias,
            'predicate' => $predicate
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function leftJoin(DataSource $dataSource, $predicate, $alias = null)
    {
        if (! ($predicate instanceof Expression)) {
            $predicate = new ExpressionParser($predicate);
        }
        $this->joins[] = array(
            'source' => $dataSource,
            'type' => 'LEFT',
            'alias' => $alias,
            'condition' => $predicate
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function rightJoin(DataSource $dataSource, $predicate, $alias = null)
    {
        if (! ($predicate instanceof Expression)) {
            $predicate = new ExpressionParser($predicate);
        }
        $this->joins[] = array(
            'source' => $dataSource,
            'type' => 'RIGHT',
            'alias' => $alias,
            'condition' => $predicate
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function distinct($distinct = true)
    {
        $this->distinct = $distinct;
        return $this;
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
     * Find row number of a record in selection.
     *
     * @param Record $record
     *            A record.
     * @return int Row number.
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
    public function offset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->source->readSelection($this);
    }
}
