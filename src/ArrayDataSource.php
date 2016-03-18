<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

/**
 * A data source based on an array of records.
 */
class ArrayDataSource extends ArrayDataSourceBase
{
    
    /**
     * @var array[]
     */
    private $data;
    
    /**
     * @var int
     */
    private $insertId = 0;
    
    /**
     * Construct data source from list of records.
     * @param array[] $data Records.
     */
    public function __construct(array $data)
    {
        $this->data = array_values($data);
        $this->insertId = count($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function insert(array $data, $replace = false)
    {
        $this->data[$this->insertId] = new $data;
        return $this->insertId++;
    }

    /**
     * {@inheritdoc}
     */
    public function insertMultiple(array $records, $replace = false)
    {
        foreach ($records as $record) {
            $this->insert($record, $replace);
        }
        return $this->insertId;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteKey($key)
    {
        unset($this->data[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function updateKey($key, array $record)
    {
        $this->data[$key] = array_merge($this->data[$key], $record);
    }
}
