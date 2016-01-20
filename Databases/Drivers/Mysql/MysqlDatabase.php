<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Drivers\Mysql;

use Jivoo\Databases\Common\SqlDatabaseBase;
use Jivoo\Databases\Common\MysqlTypeAdapter;
use Jivoo\Databases\ConnectionException;
use Jivoo\Databases\QueryException;

/**
 * MySQL database driver.
 * @deprecated Use {@see MysqliDatabase} or {@see PdoMysqlDatabase} instead,
 * if available.
 */
class MysqlDatabase extends SqlDatabaseBase {
  /**
   * @var resource MySQL connection handle.
   */
  private $handle;

  /**
   * {@inheritdoc}
   */
  protected function init($options = array()) {
    $this->setTypeAdapter(new MysqlTypeAdapter($this));
    if (isset($options['tablePrefix'])) {
      $this->tablePrefix = $options['tablePrefix'];
    }
    $this->handle = mysql_connect($options['server'], $options['username'],
      $options['password'], true);
    if (!$this->handle) {
      throw new ConnectionException(mysql_error());
    }
    if (!mysql_select_db($options['database'], $this->handle)) {
      throw new ConnectionException(mysql_error());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function close() {
    mysql_close($this->handle);
  }

  /**
   * {@inheritdoc}
   */
  public function quoteString($string) {
    return '"' . mysql_real_escape_string($string) . '"';
  }

  /**
   * {@inheritdoc}
   */
  public function rawQuery($sql, $pk = null) {
    $this->logger->debug(
      'MySQL query: {query}',
      array('query' => $sql)
    );
    $result = mysql_query($sql, $this->handle);
    if (!$result) {
      throw new QueryException(mysql_error());
    }
    if (preg_match('/^\\s*(select|show|explain|describe) /i', $sql)) {
      return new MysqlResultSet($result);
    }
    else if (preg_match('/^\\s*(insert|replace) /i', $sql)) {
      return mysql_insert_id($this->handle);
    }
    else {
      return mysql_affected_rows($this->handle);
    }
  }
}
