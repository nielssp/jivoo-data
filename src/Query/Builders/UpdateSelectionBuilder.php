<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Builders;

use Jivoo\Data\Query\UpdateSelection;
use Jivoo\Data\Query\Updatable;

/**
 * An update selection.
 */
class UpdateSelectionBuilder extends SelectionBase implements Updatable, UpdateSelection {
  /**
   * @var array Associative array of field names and values
   */
  protected $data = array();
  
  /**
   * {@inheritdoc}
   */
  public function getData() {
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function set($field, $value = null) {
    if (is_array($field)) {
      foreach ($field as $f => $val) {
        $this->data($f, $val);
      }
    }
    else {
      if (strpos($field, '=') !== false) {
        if (!is_array($value)) {
          $value = func_get_args();
          $value = array_slice($value, 1);
        }
      }
      $this->data[$field] = $value;
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function update() {
    $this->source->update($this);
  }
}
