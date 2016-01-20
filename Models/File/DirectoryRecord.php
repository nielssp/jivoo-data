<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models\File;

/**
 * A directory record.
 */
class DirectoryRecord extends FileRecord {
  /**
   * {@inheritdoc}
   */
  public function __get($field) {
    if ($field == 'type')
      return 'directory';
    return parent::__get($field);
  }

  /**
   * {@inheritdoc}
   */
  public function getData() {
    $data = parent::getData();
    $data['type'] = 'directory';
    return $data;
  }
  
  /**
   * Get content of directory.
   * @return FileRecord[] List of file records.
   */
  public function getContent() {
    $files = scandir($this->path);
    if ($files === false)
      return array();
    $records = array();
    foreach ($files as $file) {
      if ($file[0] != '.') {
        $path = $this->path . '/' . $file;
        if (is_dir($path))
          $records[] = new DirectoryRecord($path);
        else
          $records[] = new FileRecord($path);
      }
    }
    return $records;
  }
}