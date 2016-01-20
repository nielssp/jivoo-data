<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models\Selection;

/**
 * A delete selection.
 */
interface DeleteSelection extends BasicSelection {
  /**
   * Delete record in selection.
   * @return int Number of deleted records.
   */
  public function delete();
}