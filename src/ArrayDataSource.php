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
class ArrayDataSource extends ArrayDataSourceBase
{
    
    /**
     * @var Record[]
     */
    private $data;
    
    /**
     * Construct data source from list of records.
     * @param Record[] $data Records.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(array $data, $replace = false)
    {
        $this->data[] = new ArrayRecord($data);
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function insertMultiple(array $records, $replace = false)
    {
        foreach ($records as $record) {
            $this->insert($record, $replace);
        }
        return null;
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
        $this->data[$key]->addData($record);
    }
}
