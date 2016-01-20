<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models;

use Jivoo\Models\Validation\ValidatorBuilder;

/**
 * A generic form.
 * @TODO rename: FormBuilder ??
 */
class Form implements BasicRecord, BasicModel {
  
  /**
   * @var array Associative array of data.
   */
  private $data = array();
  
  /**
   * @var string[] Associative array of field names and error messages.
   */
  private $errors = array();

  /**
   * @var string Form name.
   */
  private $name;
  
  /**
   * @var array Associative array of field names and information (label, type and
   * required).
   */
  private $fields = array();

  /**
   * @var Validator Validator.
   */
  private $validator;

  /**
   * Construct form.
   * @param string $name Form name.
   * @param array $data Associative array of data.
   */
  public function __construct($name, $data = array()) {
    $this->name = $name;
    $this->data = $data;
    foreach ($this->data as $field => $value) {
      $this->addField($field, DataType::detectType($value));
      $this->data[$field] = $value;
    } 
    $this->validator = new ValidatorBuilder($this);
  }

  /**
   * Get form validator.
   * @return ValidatorBuilder Validator.
   */
  public function getValidator() {
    return $this->validator;
  }

  /**
   * {@inheritdoc}
   */
  public function __get($field) {
    if (isset($this->data[$field])) {
      return $this->data[$field];
    }
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function __set($field, $value) {
    if (isset($this->fields[$field])) {
      $this->data[$field] = $value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($field) {
    return isset($this->data[$field]);
  }

  /**
   * {@inheritdoc}
   */
  public function addData($data, $allowedFields = null) {
    if (is_array($allowedFields)) {
      $allowedFields = array_flip($allowedFields);
      $data = array_intersect_key($data, $allowedFields);
    }
    foreach ($data as $field => $value) {
      if (isset($this->fields[$field])) {
        $this->data[$field] = $value;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getData() {
    return $this->data;
  }

  /**
   * {@inheritdoc}
   */
  public function getModel() {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isValid() {
    foreach ($this->fields as $field => $options) {
      if ($options['required'] AND empty($this->data[$field])
          AND !is_numeric($this->data[$field])) {
        $this->addError($field, tr('Must not be empty.'));
      }
    }
    return count($this->errors) == 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getErrors() {
    return $this->errors;
  }

  /**
   * Add a field to form.
   * @param string $field Field name.
   * @param DataType $type Type of field.
   * @param string $label Field label, if not set the field name will be used.
   * @param bool $required Whether or not the field is required.
   */
  public function addField($field, $type, $label = null,
                           $required = true) {
    if (!isset($label)) {
      $label = tr(ucfirst($field));
    }
    $this->fields[$field] = array('label' => $label, 'type' => $type,
      'required' => $required
    );
  }

  /**
   * Add a string field to form.
   * @param string $field Field name.
   * @param string $label Field label, if not set the field name will be used.
   * @param bool $required Whether or not the field is required.
   */
  public function addString($field, $label = null, $required = true) {
    $this->addField($field, DataType::string(), $label, $required);
  }

  /**
   * Add a text field to form.
   * @param string $field Field name.
   * @param string $label Field label, if not set the field name will be used.
   * @param bool $required Whether or not the field is required.
   */
  public function addText($field, $label = null, $required = true) {
    $this->addField($field, DataType::text(), $label, $required);
  }
  
  /**
   * Add an error.
   * @param string $field Field name.
   * @param string $errorMsg Error message.
   */
  public function addError($field, $errorMsg) {
    $this->errors[$field] = $errorMsg;
  }

  /* Model implementation */

  /**
   * Create a new form using same fields as this one.
   * @return Form New form from this model.
   */
  public function create($data = array(), $allowedFields = null) {
    if (is_array($allowedFields)) {
      $allowedFields = array_flip($allowedFields);
      $data = array_intersect_key($data, $allowedFields);
    }
    return new Form($this, $data);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    return array_keys($this->fields);
  }

  /**
   * {@inheritdoc}
   */
  public function getType($field) {
    if (isset($this->fields[$field])) {
      return $this->fields[$field]['type'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel($field) {
    if (isset($this->fields[$field])) {
      return $this->fields[$field]['label'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isRequired($field) {
    if (isset($this->fields[$field])) {
      return $this->fields[$field]['required'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasField($field) {
    return isset($this->fields[$field]);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetExists($field) {
    return $this->__isset($field);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetGet($field) {
    return $this->__get($field);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetSet($field, $value) {
    $this->__set($field, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset($field) {
    $this->__unset($field);
  }

}
