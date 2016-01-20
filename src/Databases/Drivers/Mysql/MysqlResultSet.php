<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Drivers\Mysql;

use Jivoo\Databases\ResultSet;

/**
 * Result set for MySQL driver.
 */
class MysqlResultSet implements ResultSet {
  /**
   * @var resource MySQL result resource.
   */
  private $mysqlResult;
  
  /**
   * @var array[] List of saved rows.
   */
  private $rows = array();

  /**
   * Construct result set.
   * @param resource MySQL result resource as returned by {@see mysql_query()}.
   */
  public function __construct($result) {
    $this->mysqlResult = $result;
  }

  /**
   * {@inheritdoc}
   */
  public function hasRows() {
    return ($this->rows[] = $this->fetchAssoc()) !== false;
  }

  /**
   * Get ordered array from associative array.
   * @param array $assoc Associative array.
   * @return mixed[] Ordered array.
   */
  private function rowFromAssoc($assoc) {
    return array_values($assoc);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchRow() {
    if (!empty($this->rows)) {
      return $this->rowFromAssoc(array_shift($this->rows));
    }
    return mysql_fetch_row($this->mysqlResult);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAssoc() {
    if (!empty($this->rows)) {
      return array_shift($this->rows);
    }
    return mysql_fetch_assoc($this->mysqlResult);
  }
}
