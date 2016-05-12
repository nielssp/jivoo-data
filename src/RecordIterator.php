<?php
// Jivoo Data
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

/**
 * An iterator that converts associative arrays to {@see Record}s.
 */
class RecordIterator implements \Iterator
{
    
    /**
     * @var \Iterator
     */
    private $data;
    
    /**
     * @var Model
     */
    private $model;
    
    /**
     * @var Query\ReadSelection
     */
    private $selection;
    
    /**
     * Construct iterator.
     *
     * @param \Iterator $data Data iterator.
     * @param \Jivoo\Data\Model $model Model.
     * @param \Jivoo\Data\Query\ReadSelection $selection Read selection.
     */
    public function __construct(\Iterator $data, Model $model, \Jivoo\Data\Query\ReadSelection $selection)
    {
        $this->data = $data;
        $this->model = $model;
        $this->selection = $selection;
    }
    
    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->model->open($this->data->current(), $this->selection);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->data->key();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->data->next();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->data->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->data->valid();
    }
}
