<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

/**
 * Represents the result of a database query.
 */
interface ResultSet {
  /**
   * Check if resultset is empty.
   * @return bool True if there are rows in resultset.
   */
  public function hasRows();
  
  /**
   * Fetch the next row as an ordered array.
   * @return mixed[]|false The array or false if no more rows.
   */
  public function fetchRow();
  
  /**
   * Fetch the next row as an associative array.
   * @return array|false The array or false if no more rows.
   */
  public function fetchAssoc();
}
