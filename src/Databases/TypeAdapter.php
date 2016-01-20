<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Models\DataType;

/**
 * Convert types from database to schema and vice versa.
 */
interface TypeAdapter {
  /**
   * Encode value for database.
   * @param DataType $type Data type to convert from.
   * @param mixed $value Value to convert.
   * @return mixed Database-ready value.
   */
  public function encode(DataType $type, $value);

  /**
   * Decode value from database.
   * @param DataType $type Data type to convert to.
   * @param mixed $value Value from database.
   * @return mixed Value.
   */
  public function decode(DataType $type, $value);
}
