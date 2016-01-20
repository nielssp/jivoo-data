<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models\File;

use Jivoo\Models\BasicRecord;
use Jivoo\InvalidPropertyException;

/**
 * A file record.
 * @property-read string $path File path.
 * @property-read string $name File name.
 * @property-read string $type Type. 'file' or 'directory'.
 * @property-read int $size File size in bytes.
 * @property-read int $modified Modification time.
 * @property-read int $created Creation time.
 */
class FileRecord implements BasicRecord {
  /**
   * @var FileModel Model.
   */
  private $model;
  
  /**
   * @var string File path.
   */
  private $path;
  
  /**
   * @var string File name.
   */
  private $name;
  
  /**
   * @var string Type.
   */
  private $type = 'file';
  
  /**
   * @var int File size in bytes.
   */
  private $size = null;
  
  /**
   * @var int File modified time.
   */
  private $modified = null;
  
  /**
   * @var int File created time.
   */
  private $created = null;
  
  /**
   * Construct file record.
   * @param FileModel $model Model.
   * @param string $path File path.
   */
  public function __construct($path) {
    $this->model = FileModel::getInstance();
    $this->path = $path;
    $this->name = basename($path);
  }

  /**
   * {@inheritdoc}
   */
  public function __get($field) {
    switch ($field) {
      case 'path':
      case 'name':
      case 'type':
        return $this->$field;
      case 'size':
        if (!isset($this->size))
          $this->size = filesize($this->path);
        return $this->size;
      case 'modified':
        if (!isset($this->modified))
          $this->modified = filemtime($this->path);
        return $this->modified;
      case 'created':
        if (!isset($this->created))
          $this->created = filectime($this->path);
        return $this->created;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $field));
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($field) {
    return $this->__get($field) !== null;
  }

  /**
   * {@inheritdoc}
   */
  public function getData() {
    return array(
      'path' => $this->path,
      'name' => $this->name,
      'type' => $this->type,
      'size' => $this->size,
      'modified' => $this->modified,
      'created' => $this->created
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getModel() {
    return $this->model;
  }

  /**
   * {@inheritdoc}
   */
  public function getErrors() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function isValid() {
    return true;
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
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset($field) {
  }
}