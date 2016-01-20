<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database\Common;

use Jivoo\Data\Database\Database;

/**
 * An SQL database.
 */
interface SqlDatabase extends Database {
  /**
   * Execute a raw sql query on database.
   * @param string $sql Raw sql.
   * @param string|null $pk Name of auto incrementing primary key if any (only
   * supplied for inserts and only needed for some database systems).
   * @return ResultSet|int A result set if query is a select-, show-,
   * explain-, or describe-query, the last insert id if query is an insert- or
   * replace-query, or number of affected rows in any other case..
   * @throws \Jivoo\Data\Database\QueryException if query failed.
   */
  public function rawQuery($sql, $pk = null);

  /**
   * Get type adapter.
   * @return TypeAdapter Type adapter for database.
   */
  public function getTypeAdapter();
}
