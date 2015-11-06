<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

use Jivoo\Data\Query\Selection;
use Jivoo\Data\Query\UpdateSelection;
use Jivoo\Data\Query\ReadSelection;

/**
 * An array data source.
 */
abstract class ArrayDataSource implements DataSource {

  public abstract function getData();

  public abstract function setData($data);


  /**
   * {@inheritdoc}
   */
  public function read(ReadSelection $selection) {
    // TODO: implement
  }

  /**
   * {@inheritdoc}
   */
  public function update(UpdateSelection $selection) {
    // TODO: implement
  }
  
  /**
   * {@inheritdoc}
   */
  public function delete(Selection $selection) {
    // TODO: implement
  }
}
