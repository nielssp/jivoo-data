<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Builders;

use Jivoo\Data\Model;
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
 *
 * @property-read bool $distinct Distinct.
 * @property-read int $offset Offset.
 *                @proeprty-read array $groupBy An array describing grouping.
 *                @proeprty-read array[] $joins List of arrays describing joings.
 * @property-read array[] $fields List of arrays describing fields.
 * @property-read array[] $additionalFields List of arrays describing fields.
 */
class ReadSelectionBuilder extends SelectionBase implements Readable, ReadSelection
{

    /**
     * @var bool Distinct.
     */
    protected $distinct = false;

    /**
     * @var string|null Alias for source.
     */
    protected $alias = null;

    /**
     * An arrays describing grouping.
     *
     * Each array is of the following format:
     * <code>
     * array(
     * 'columns' => ... // List of columns
     * 'condition' => ... // Join condition ({@see Condition})
     * )
     * </code>
     *
     * @var array
     */
    protected $groupBy = null;

    /**
     * @var Predicate
     */
    private $groupPredicate = null;

    /**
     * @var int Offset
     */
    protected $offset = 0;

    /**
     * List of arrays describing joins.
     *
     * Each array is of the following format:
     * <code>
     * array(
     * 'source' => ..., // Data source to join with ({@see DataSource})
     * 'type' => ..., // Type of join: 'INNER', 'RIGHT' or 'LEFT'
     * 'alias' => ..., // Alias for other data source (string|null)
     * 'predicate' => ... // Join predicate ({@see Expression})
     * );
     * </code>
     *
     * @var array[]
     */
    protected $joins = array();

    /**
     * List of arrays describing columns.
     *
     * Each array is of the following format:
     * <code>
     * array(
     * 'expression' => ..., // Expression (string)
     * 'alias' => ... // Alias (string|null)
     * )
     * </code>
     *
     * @var array[]
     */
    protected $fields = array();

    /**
     * List of arrays describing columns.
     *
     * Each array is of the following format:
     * <code>
     * array(
     * 'alias' => ... // Alias (string)
     * 'expression' => ..., // Expression (string)
     * 'type' => ... // Type (DataType|null)
     * 'model' => ... // Model (BasicModel|null)
     * 'record' => ... // Record field (string|null)
     * )
     * </code>
     *
     * @var array[]
     */
    protected $additionalFields = array();

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
        return $this->groupBy;
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
        return $this->fields;
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
        $this->fields = array();
        if (is_array($expression)) {
            foreach ($expression as $alias => $expression) {
                if (! ($expression instanceof Expression)) {
                    $expression = new ExpressionParser($expression);
                }
                if (is_int($alias)) {
                    $this->fields[] = array(
                        'expression' => $expression,
                        'alias' => null
                    );
                } else {
                    $this->fields[] = array(
                        'expression' => $expression,
                        'alias' => $alias
                    );
                }
            }
        } else {
            if (! ($expression instanceof Expression)) {
                $expression = new ExpressionParser($expression);
            }
            $this->fields[] = array(
                'expression' => $expression,
                'alias' => $alias
            );
        }
        $result = $this->source->read($this);
        $this->fields = array();
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
        $this->groupBy = $columns;
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
        return $this->source->firstSelection($this);
    }

    /**
     * {@inheritdoc}
     */
    public function last()
    {
        return $this->source->lastSelection($this);
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
        return $this->source->read($this);
    }
}
