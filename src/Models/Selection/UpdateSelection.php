<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models\Selection;

/**
 * An update selection.
 */
interface UpdateSelection extends BasicSelection {
  /**
   * Assign value to field. If $field is an associative array, then multiple
   * fields are assigned. If $field contains an equals sign ('=') then $field
   * is used as the set expression.
   * @param string|array $field Field name or associative array of field names
   * and values
   * @param string $value Value
   * @return UpdateSelection An update selection.
   */
  public function set($field, $value = null);

  /**
   * Execute updates.
   * @return int Number of updated records.
   */
  public function update();
}
