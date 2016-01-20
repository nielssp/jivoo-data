<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Drivers\Mysqli;

use Jivoo\Databases\Common\SqlDatabaseBase;
use Jivoo\Databases\Common\MysqlTypeAdapter;
use Jivoo\Databases\ConnectionException;
use Jivoo\Databases\QueryException;

/**
 * MySQLi database driver.
 */
class MysqliDatabase extends SqlDatabaseBase {
  /**
   * @var mysqli MySQLi object.
   */
  private $handle;

  /**
   * {@inheritdoc}
   */
  public function init($options = array()) {
    $this->setTypeAdapter(new MysqlTypeAdapter($this));
    if (isset($options['tablePrefix'])) {
      $this->tablePrefix = $options['tablePrefix'];
    }
    $this->handle = new \mysqli($options['server'], $options['username'],
      $options['password'], $options['database']);
    if ($this->handle->connect_error) {
      throw new ConnectionException($this->handle->connect_error);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function close() {
    $this->handle->close();
  }

  /**
   * {@inheritdoc}
   */
  public function quoteString($string) {
    return '"' . $this->handle->real_escape_string($string) . '"';
  }

  /**
   * {@inheritdoc}
   */
  public function rawQuery($sql, $pk = null) {
    $this->logger->debug('MySQLi query: {query}', array('query' => $sql));
    $result = $this->handle->query($sql);
    if (!$result) {
      throw new QueryException($this->handle->error);
    }
    if (preg_match('/^\\s*(select|show|explain|describe) /i', $sql)) {
      return new MysqliResultSet($result);
    }
    else if (preg_match('/^\\s*(insert|replace) /i', $sql)) {
      return $this->handle->insert_id;
    }
    else {
      return $this->handle->affected_rows;
    }
  }
}
