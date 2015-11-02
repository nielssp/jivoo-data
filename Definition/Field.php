<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Definition;

/**
 * Schema field.
 */
interface Field {
  /**
   * Get name of field.
   * @return string Name.
   */
  public function getName();
  
  /** 
   * Get type of field.
   * @return \Jivoo\Data\DataType Type of field.
   */
  public function getType();
  
}
