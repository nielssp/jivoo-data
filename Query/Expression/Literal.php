<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Expression;

use Jivoo\Data\Query\Expression;

/**
 * A literal.
 */
interface Literal extends Expression {
  /**
   * Type of literal.
   * @return DataType
   */
  public function getType();

  /**
   * Value.
   * @return mixed
   */
  public function getValue();
}