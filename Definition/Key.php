<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Definition;

/**
 * Schema key.
 */
interface Key {
  /**
   * Get name of schema.
   * @return string Name.
   */
  public function isUnique();
  
  /** 
   * Get list of fields.
   * @return string[] List of field names.
   */
  public function getFields();
  
}
