<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models;

/**
 * Describes the format of data in a record
 */
interface BasicModel {
  /**
   * Get name of model. 
   * @return string Name.
   */
  public function getName();

  /**
   * Get list of field names.
   * @return string[] List of field names.
   */
  public function getFields();

  /**
   * Get type of field.
   * @param string $field Field name.
   * @return DataType|null Type of field if it exists.
   */
  public function getType($field);

  /**
   * Get field label.
   * @param string $field Field label.
   * @return string A translated name for the field.
   */
  public function getLabel($field);

  /**
   * Determine if the field exists in the model.
   * @param string $field Field name.
   * @return bool True if the field exists, false otherwise.
   */
  public function hasField($field);
  
  /**
   * Determine if the field is required.
   * @param string $field Field name.
   * @return bool True if required, false otherwise.
   */
  public function isRequired($field);
}
