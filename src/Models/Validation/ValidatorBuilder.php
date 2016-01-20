<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models\Validation;

use Jivoo\Models\BasicModel;
use Jivoo\Models\Record;

/**
 * A record validator.
 * @TODO Some information about $validator-array and use of validators here
 */
class ValidatorBuilder implements Validator {
  /**
   * @var ValidatorField[] Associative array of field names and ValidatorField objects
   */
  private $fields = array();

  /**
   * @var BasicModel Model.
   */
  private $model;

  /**
   * Construct validator.
   * @param BasicModel $model Associated model.
   * @param array $fields Associative array of field-names and rules.
   */
  public function __construct(BasicModel $model, $fields = array()) {
    $this->model = $model;
    foreach ($fields as $field => $rules) {
      $this->fields[$field] = new ValidatorField($rules);
    }
  }

  /**
   * Get a field or create it if it doesn't exist.
   * @param string $field Field name.
   * @return ValidatorField A validator field.
   */
  public function __get($field) {
    return $this->get($field);
  }

  /**
   * Check whether or not a field is set.
   * @param string $field Field name.
   * @return bool True if set, false otherwise.
   */
  public function __isset($field) {
    return isset($this->fields[$field]);
  }

  /**
   * Remove a validator field.
   * @param string $field Field name.
   */
  public function __unset($field) {
    $this->remove($field);
  }

  /**
   * Get a field or create it if it doesn't exist.
   * @param string $field Field name.
   * @return ValidatorField A validator field.
   */
  public function get($field) {
    if (!isset($this->fields[$field])) {
      $this->fields[$field] = new ValidatorField();
    }
    return $this->fields[$field];
  }

  /**
   * Remove a validator field.
   * @param string $field Field name.
   */
  public function remove($field) {
    if (isset($this->fields[$field])) {
      unset($this->fields[$field]);
    }
  }

  /**
   * Get array of all fields.
   * @return array Associative array of field names and {@see ValidatorField}
   * objects.
   */
  public function getFields() {
    return $this->fields;
  }

  /**
   * Whether or not field is required.
   * @param string $field Field name.
   * @return boolean True if required, false if optional.
   */
  public function isRequired($field) {
    if (!isset($this->fields[$field]))
      return false;
    return $this->fields[$field]->isRequired();
  }

  /**
   * {@inheritdoc}
   */
  public function validate(Record $record) {
    $result = array();
    foreach ($this->fields as $field => $validator) {
      $fieldResult = $validator->validate($record, $field);
      if ($fieldResult !== true)
        $result[$field] = $fieldResult;
    }
    return $result;
  }

  /**
   * Validate a rule on a record.
   * @param Record $record A record.
   * @param string $field Field name.
   * @param string $ruleName Name of rule.
   * @param mixed $rule Value of rule.
   * @return true|string True if valid, otherwise returns an error message.
   */
  public static function validateRule(Record $record, $field, $ruleName, $rule) {
    if ($rule instanceof ValidatorRule) {
      return $rule->validate($record, $field);
    }
    $value = $record->$field;
    if ($ruleName != 'presence' and $ruleName != 'null'
        and $ruleName != 'callback' and ($value == null
        or $value == '')) {
      return true;
    }
//    if (!is_scalar($value)) {
//      return tr('Must be a scalar.');
//    }
    switch ($ruleName) {
      case 'type':
        return $rule->isValid($value);
      case 'presence':
        if ((!empty($value) or is_numeric($value)) == $rule)
          return true;
        else
          return $rule ? tr('Must not be empty.') : tr('Must be empty.');
      case 'null':
        if (is_null($value) == $rule)
          return true;
        else
          return $rule ? tr('Must not be set.') : tr('Must be set.');
      case 'email':
        if ((preg_match(
          "/^[a-z0-9.!#$%&*+\/=?^_`{|}~-]+@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i",
          $value
        ) == 1) == $rule)
          return true;
        else
          return $rule ? tr('Not a valid email address.') : tr('Must not be an email address.');
      case 'url':
        if ((preg_match(
          "/^https?:\/\/[-a-z0-9@:%_\+\.~#\?&\/=\[\]]+$/i",
          $value
        ) == 1) == $rule)
          return true;
        else
          return $rule ? tr('Not a valid URL.') : tr('Must not be a URL.');
      case 'date':
        $timestamp = false;
        if (preg_match('/^[-+]?\d+$/', $value) == 1)
          $timestamp = (int)$value;
        else
          $timestamp = strtotime($value);
        if (($timestamp !== false) == $rule)
          return true;
        else
          return $rule ? tr('Not a valid date.') : tr('Must not be a date.');
      case 'minLength':
        if (strlen($value) >= $rule)
          return true;
        else
          return tn(
            'Must be at least %1 characters long.',
            'Must be at least %1 character long.',
            $rule
          );
      case 'maxLength':
        if (strlen($value) <= $rule)
          return true;
        else
          return tn(
            'Must be at most %1 characters long.',
            'Must be at most %1 character long.',
            $rule
          );
      case 'numeric':
        if (is_numeric($value) == $rule)
          return true;
        else
          return $rule ? tr('Must be numeric.') : tr('Must not be numeric.');
      case 'integer':
        if ((preg_match('/^[-+]?\d+$/', $value) == 1) == $rule)
          return true;
        else
          return $rule ? tr('Must be an integer.') : tr('Must not be an integer.');
      case 'float':
        if ((preg_match('/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/', $value)
          == 1) == $rule)
          return true;
        else
          return $rule ? tr('Must be a decimal number.') : tr('Must not be a decimal number.');
      case 'boolean':
        if ((preg_match('/^(0|1|true|false|yes|no)$/i', $value) == 1) == $rule)
          return true;
        else
          return $rule ? tr('Must be boolean (true or false).') : tr('Must not be boolean.');
      case 'minValue':
        $value = is_float($rule) ? (float) $value : (int) $value;
        if ($value >= $rule)
          return true;
        else
          return tr('Must be greater than or equal to %1.', $rule);
      case 'maxValue':
        $value = is_float($rule) ? (float) $value : (int) $value;
        if ($value <= $rule)
          return true;
        else
          return tr('Must be less than or equal to %1.', $rule);
      case 'in':
        if (in_array($value, $rule))
          return true;
        else
          return tn(
            'Must be either "%1{", "}{" or "}".',
            'Must be "%1{", "}{" or "}".',
            $rule
          );
      case 'match':
        if (preg_match($rule, $value) == 1)
          return true;
        else
          return tr('Invalid value.');
      case 'unique':
        $selection = $record->getModel();
        if (!$record->isNew()) {
          $selection = $selection->selectNotRecord($record);
        }
        if (($selection->where($field . ' = ?', $value)->count() == 0) == $rule)
          return true;
        else
          return $rule ? tr('Must be unique.') : tr('Must not be unique.');
      case 'callback':
        if (!is_array($rule))
          $rule = array($record->getModel(), $rule);
        if (!is_callable($rule))
          return true;
        else
          return call_user_func($rule, $record, $field);
    }
    return true;
  }
}

