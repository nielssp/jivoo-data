<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

use Jivoo\Data\Query\Selection;
use Jivoo\Data\Query\UpdateSelection;
use Jivoo\Data\Query\ReadSelection;
use Jivoo\Core\Assume;

/**
 * An array data source.
 */
abstract class ArrayDataSource implements DataSource {
  /**
   * @return array[] List of records.
   */
  public abstract function getData();

  /**
   * @param mixed $key
   */
  public abstract function deleteKey($key);

  /**
   * @param mixed $key
   * @param array $record
   */
  public abstract function updateKey($key, $record);


  /**
   * {@inheritdoc}
   */
  public function read(ReadSelection $selection) {
    $data = $this->getData();
    $predicate = $selection->getPredicate();
    if (count($selection->getJoins()) > 0)
      throw new \Exception('unsupported operation');
    // TODO: implement
    // JOIN
    // WHERE
    // GROUP BY
    // HAVING
    // ORDER BY
    // LIMIT
    // SELECT/PROJECTION
  }

  /**
   * {@inheritdoc}
   */
  public function update(UpdateSelection $selection) {
    $data = $this->getData();
    $data = self::sortAll($data, $selection->getOrdering());
    $updates = $selection->getData();
    $limit = $selection->getLimit();
    $predicate = $selection->getPredicate();
    $count = 0;
    foreach ($data as $key => $record) {
      if ($predicate($record)) {
        $this->updateKey($key, array_merge($record, $updates));
        $count++;
        if (isset($limit) and $count >= $limit)
          break;
      }
    }
    return $count;
  }
  
  /**
   * {@inheritdoc}
   */
  public function delete(Selection $selection) {
    $data = $this->getData();
    $data = self::sortAll($data, $selection->getOrdering());
    $limit = $selection->getLimit();
    $predicate = $selection->getPredicate();
    $count = 0;
    foreach ($data as $key => $record) {
      if ($predicate($record)) {
        $this->deleteKey($key);
        $count++;
        if (isset($limit) and $count >= $limit)
          break;
      }
    }
    return $count;
  }
  
  public static function sortAll($data, $orderings) {
    $orderings = array_reverse($orderings);
    foreach ($orderings as $ordering)
      $data = self::sort($data, $ordering[0], $ordering[1]);
    return $data;
  }
  
  public static function sort($data, $field, $descending = false) {
    Assume::isArray($data);
    usort($data, function(BasicRecord $a, BasicRecord $b) use($field, $descending) {
      if ($a->$field == $b->$field)
        return 0;
      if ($descending) {
        if (is_numeric($a->$field))
          return $b->$field - $a->$field;
        return strcmp($b->$field, $a->$field);
      }
      else {
        if (is_numeric($a->$field))
          return $a->$field - $b->$field;
        return strcmp($a->$field, $b->$field);
      }
    });
    return $data;
  }
}
