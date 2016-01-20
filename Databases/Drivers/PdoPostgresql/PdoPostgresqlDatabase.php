<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Drivers\PdoPostgresql;

use Jivoo\Databases\Common\PostgresqlTypeAdapter;
use Jivoo\Databases\Common\PdoDatabase;
use Jivoo\Databases\QueryException;
use Jivoo\Databases\ConnectionException;
use Jivoo\Databases\Jivoo\Databases;

/**
 * PDO PostgreSQL database driver.
 */
class PdoPostgresqlDatabase extends PdoDatabase {
  /**
   * {@inheritdoc}
   */
  public function init($options = array()) {
    $this->setTypeAdapter(new PostgresqlTypeAdapter($this));
    if (isset($options['tablePrefix'])) {
      $this->tablePrefix = $options['tablePrefix'];
    }
    try {
      if (isset($options['password'])) {
        $this->pdo = new \PDO(
          'pgsql:host=' . $options['server'] . ';dbname=' . $options['database'],
          $options['username'], $options['password']);
      }
      else {
        $this->pdo = new \PDO(
          'pgsql:host=' . $options['server'] . ';dbname=' . $options['database'],
          $options['username']);
      }
    }
    catch (\PDOException $exception) {
      throw new ConnectionException($exception->getMessage(), 0, $exception);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function rawQuery($sql, $pk = null) {
    if (isset($pk)) {
      $result = $this->pdo->query($sql . ' RETURNING ' . $this->quoteField($pk));
      if (!$result) {
        $errorInfo = $this->pdo->errorInfo();
        throw new QueryException(
          $errorInfo[0] . ' - ' . $errorInfo[1] . ' - ' . $errorInfo[2]);
      }
      $row = $result->fetch(\PDO::FETCH_NUM);
      return $row[0];
    }
    return parent::rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function quoteModel($name) {
    return '"' . $this->tableName($name) . '"';
  }
  
  /**
   * {@inheritdoc}
   */
  public function caseInsensitiveFields() {
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function sqlLimitOffset($limit, $offset = null) {
    if (isset($offset))
      return 'LIMIT ' . $limit . ' OFFSET ' . $offset;
    return 'LIMIT ' . $limit;
  }
}
