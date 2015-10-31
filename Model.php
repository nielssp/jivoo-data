<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

use Jivoo\Data\Query\Selection;
use Jivoo\Data\Query\UpdateSelection;
use Jivoo\Data\Query\ReadSelection;

/**
 * A CRUD data source.
 */
interface Model extends Selectable, Updatable, Deletable, DataSource {
  /**
   * Create a record.
   * @param array $data Associative array of record data.
   * @param string[]|null $allowedFields List of allowed fields (null for all
   * fields allowed), fields that are not allowed (or not in the model) will be
   * ignored.
   * @return Record A record.
   */
  public function create($data = array(), $allowedFields = null);
  
}