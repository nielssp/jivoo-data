<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models\Validation;

use Jivoo\Models\Record;

/**
 * Validator for a single field.
 */
class ValidatorField {
  /**
   * @var array Associative array of rule names and values
   */
  private $rules = array();

  /**
   * Construct field validator.
   * @param array $rules Associative array of validation rules for field.
   */
  public function __construct($rules = array()) {
    foreach ($rules as $rule => $value) {
      if (substr($rule, 0, 4) == 'rule') {
        $this->rules[$rule] = new ValidatorRule($value);
      }
      else {
        $this->rules[$rule] = $value;
      }
    }
  }

  /**
   * Get a single rule.
   * @param string $rule Rule name.
   * @return mixed|ValidatorRule Rule value or a more complex rule.
   */
  public function __get($rule) {
    return $this->get($rule);
  }

  /**
   * Add/set a rule.
   * @param string $rule Rule name.
   * @param mixed $value Rule value.
   */
  public function __set($rule, $value) {
    $this->add($rule, $value);
  }

  /**
   * Check whether or not a rule exists.
   * @param string $rule Rule name.
   * @return bool True if it exists, false otherwise.
   */
  public function __isset($rule) {
    return isset($this->rules[$rule]);
  }

  /**
   * Remove a rule.
   * @param string $rule Rule name.
   */
  public function __unset($rule) {
    $this->remove($rule);
  }

  /**
   * Get a single rule.
   * @param string $rule Rule name.
   * @return mixed|ValidatorRule Rule value or a more complex rule.
   */
  public function get($rule) {
    if (substr($rule, 0, 4) == 'rule') {
      if (!isset($this->rules[$rule])) {
        $this->rules[$rule] = new ValidatorRule();
      }
    }
    else if (!isset($this->rules[$rule])) {
      return null;
    }
    return $this->rules[$rule];
  }

  /**
   * Add/set a rule.
   * @param string $rule Rule name.
   * @param mixed $value Rule value.
   * @return self Self.
   */
  public function add($rule, $value = true) {
    if (substr($rule, 0, 4) == 'rule') {
      if (!isset($this->rules[$rule])) {
        $this->rules[$rule] = new ValidatorRule();
      }
      return $this->rules[$rule];
    }
    $this->rules[$rule] = $value;
    return $this;
  }

  /**
   * Remove a rule.
   * @param string $rule Rule name.
   */
  public function remove($rule) {
    if (isset($this->rules[$rule])) {
      unset($this->rules[$rule]);
    }
  }

  /**
   * Get all rules for field.
   * @return array Associative array of rule names and values.
   */
  public function getRules() {
    return $this->rules;
  }

  /**
   * Whether or not field is required.
   * @return boolean True if required, false otherwise.
   */
  public function isRequired() {
    if (isset($this->rules['presence']) and $this->rules['presence'])
      return true;
    return false;
  }

  /**
   * Validate a value. Return value must be compared using ===.
   * @param Record $record Record.
   * @param string $field Record field.
   * @return true|string True if valid or an error message if invalid.
   */
  public function validate(Record $record, $field) {
    foreach ($this->rules as $name => $rule) {
      $result = ValidatorBuilder::validateRule($record, $field, $name, $rule);
      if ($result !== true)
        return $result;
    }
    return true;
  }
}
