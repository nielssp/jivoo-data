<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models\Validation;

use Jivoo\Models\Record;

/**
 * A record validator.
 */
interface Validator {
  /**
   * Validate a record.
   * @param Record $record Record to validate.
   * @return string[] An associative array of field names and error messages (array should
   * be empty if record is valid).
   */
  public function validate(Record $record);
}
