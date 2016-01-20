<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query;

/**
 * An interface for deletable models and selections.
 */
interface Deletable extends Selectable {
  /**
   * Delete selected records.
   * @return int Number of deleted records.
   */
  public function delete();
}