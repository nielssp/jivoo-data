<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Definition;

/**
 * Model schema.
 */
interface Schema {
  /**
   * Get name of schema.
   * @return string Name.
   */
  public function getName();
  
  /** 
   * Get list of fields.
   * @return Field[] List of fields.
   */
  public function getFields();
  
  /**
   * Get fields of primary key.
   * @return string[] List of field names or empty array if no primary key
   */
  public function getPrimaryKey();

  /**
   * Get indexes. 
   * @return Key[] Keys.
   */
  public function getKeys();
  
}
