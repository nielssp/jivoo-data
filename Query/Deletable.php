<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

/**
 * An interface for models and selections.
 */
interface Deletable extends Expression{
  /**
   * Delete records in selection.
   * @return int Number of deleted records.
   */
  public function delete();
}