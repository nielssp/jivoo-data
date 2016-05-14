<?php

// Jivoo_Data 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

/**
 * A data source capable of opening {@see Record}s.
 */
interface RecordSource extends DataSource
{
    
    /**
     * Create a {@see Record} object for an existing record using the provided
     * data.
     *
     * @param array $data Record data.
     * @param \Jivoo\Data\Query\ReadSelection $selection Read selection.
     * @return Record A record.
     */
    public function open(array $data, Query\ReadSelection $selection);
    
    /**
     * Like {@see readSelection}, but returns a {@see Record} iterator.
     *
     * @param \Jivoo\Data\Query\ReadSelection $selection Read selection.
     * @return \Iterator A {@see Record} iterator.
     */
    public function openSelection(Query\ReadSelection $selection);
}
