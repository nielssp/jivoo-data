<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Builders;

use Jivoo\Data\Query\Deletable;

/**
 * A delete selection.
 */
class DeleteSelectionBuilder extends SelectionBase implements Deletable {
  /**
   * {@inheritdoc}
   */
  public function delete() {
    $this->source->delete($this);
  }
}