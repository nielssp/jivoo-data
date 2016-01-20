<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\ActiveModels;

use Jivoo\Core\Utilities;
use Jivoo\Databases\SchemaBuilder;
use Jivoo\Models\DataType;

/**
 * A schema for Meta-models. The default implementation assumes that the class name is
 * of the form '____MetaSchema' and creates a schema consisting of fields '___Id',
 * 'variable', and 'value'.
 */
abstract class MetaSchema extends SchemaBuilder {
  /**
   * Get the name of the id field.
   * @return string Name of id field.
   */
  protected function getId() {
    $class = Utilities::getClassName($this);
    if (preg_match('/^(.+)MetaSchema$/', $class, $matches) !== 1)
      throw new InvalidMixinException(tr('Invalid meta class name format.'));
    return lcfirst($matches[1]) . 'Id';
  }

  /**
   * Get the type of the id field.
   * @return DataType Type of id field.
   */
  protected function getIdType() {
    return DataType::integer(DataType::UNSIGNED);
  }

  /**
   * {@inheritdoc}
   */
  protected function createSchema() {
    $id = $this->getId();
    $this->$id = $this->getIdType();
    $this->variable = DataType::string(255);
    $this->value = DataType::text();
    $this->setPrimaryKey($id, 'variable');
  }
}
