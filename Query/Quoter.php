<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models\Condition;

use Jivoo\Data\Query;

/**
 * Quotes strings, model names, and field names in expressions.
 */
interface Quoter {
  /**
   * Quote a literal.
   * @param DataType $type Type.
   * @param mixed $value Value.
   * @return string Quoted and/or encoded value.
   */
  public function quoteLiteral(DataType $type, $value);
  
  /**
   * Convert and quote a model name.
   * @param string $model Model name.
   * @return string Quoted model name.
   */
  public function quoteModel($model);

  /**
   * Convert and quote a field name.
   * @param string $field Field name.
   * @return string Quoted field name.
   */
  public function quoteField($field);
}