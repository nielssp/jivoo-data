<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Common;

use Jivoo\Databases\ResultSet;

/**
 * A PDO database result set.
 */
class PdoResultSet implements ResultSet {

  /**
   * @var PDOStatement Statement.
   */
  private $pdoStatement;
  
  /**
   * @var array[] List of saved rows/
   */
  private $rows = array();

  /**
   * Construct result set.
   * @param PDOStatement $result PDO statement.
   */
  public function __construct(\PDOStatement $result) {
    $this->pdoStatement = $result;
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
    return $this->pdoStatement->fetch(\PDO::FETCH_NUM);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAssoc() {
    if (!empty($this->rows)) {
      return array_shift($this->rows);
    }
    return $this->pdoStatement->fetch(\PDO::FETCH_ASSOC);
  }
}
