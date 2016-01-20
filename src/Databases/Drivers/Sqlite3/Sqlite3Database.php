<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Drivers\Sqlite3;

use Jivoo\Databases\Common\SqlDatabaseBase;
use Jivoo\Databases\Common\SqliteTypeAdapter;
use Jivoo\Databases\QueryException;
use Jivoo\Databases\ConnectionException;

/**
 * SQLite3 database driver.
 */
class Sqlite3Database extends SqlDatabaseBase {
  /**
   * @var SQLite3 SQLite3 object.
   */
  private $handle;

  /**
   * {@inheritdoc}
   */
  public function init($options = array()) {
    $this->setTypeAdapter(new SqliteTypeAdapter($this));
    if (isset($options['tablePrefix']))
      $this->tablePrefix = $options['tablePrefix'];
    try {
      $this->handle = new \SQLite3($options['filename']);
    }
    catch (\Exception $exception) {
      throw new ConnectionException(
        tr('SQLite database does not exist and could not be created: %1',
          $options['filename']),
        0, $exception
      );
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
    return '"' . $this->handle->escapeString($string) . '"';
  }

  /**
   * {@inheritdoc}
   */
  public function rawQuery($sql, $pk = null) {
    $this->logger->debug('SQLite3 query: {query}', array('query' => $sql));
    $result = $this->handle->query($sql);
    if (!$result) {
      throw new QueryException($this->handle
        ->lastErrorMsg());
    }
    if (preg_match('/^\\s*(pragma|select|show|explain|describe) /i', $sql)) {
      return new Sqlite3ResultSet($result);
    }
    else if (preg_match('/^\\s*(insert|replace) /i', $sql)) {
      return $this->handle->lastInsertRowID();
    }
    else {
      return $this->handle->changes();
    }
  }
}
