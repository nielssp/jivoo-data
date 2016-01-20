<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database\Drivers\PdoSqlite;

use Jivoo\Data\Database\Common\PdoDatabase;
use Jivoo\Data\Database\Common\SqliteTypeAdapter;
use Jivoo\Data\Database\ConnectionException;

/**
 * PDO SQLite database driver.
 */
class PdoSqliteDatabase extends PdoDatabase {
  /**
   * {@inheritdoc}
   */
  public function init($options = array()) {
    $this->setTypeAdapter(new SqliteTypeAdapter($this));
    if (isset($options['tablePrefix']))
      $this->tablePrefix = $options['tablePrefix'];
    try {
      $this->pdo = new \PDO('sqlite:' . $options['filename']);
    }
    catch (\PDOException $exception) {
      throw new ConnectionException(
        tr('SQLite database does not exist and could not be created: %1',
          $options['filename']),
        0, $exception
      );
    }
  }
}
