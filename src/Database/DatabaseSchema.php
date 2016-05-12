<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database;

use Jivoo\Data\Model;

/**
 * A wrapper for another database driver.
 */
class DatabaseSchema implements \Jivoo\Data\Schema
{

    /**
     * @var Model[] Models.
     */
    private $models = array();

    /**
     * @var Database Database.
     */
    private $database;
    
    /**
     * @var DatabaseDefinition Definition.
     */
    private $definition;

    /**
     * Construct database connection.
     *
     * @param Database $database
     *            Database.
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->definition = $database->getDefinition();
    }

    public function __get($model)
    {
        if (!isset($this->models[$model])) {
            $this->models[$model] = new SimpleModel(
                $model,
                $this->database->$model,
                $this->definition->getDefinition($model)
            );
        }
        return $this->models[$model];
    }

    public function __isset($model)
    {
        return in_array($model, $this->getModels());
    }
    
    public function map(callable $callable)
    {
        foreach ($this->getModels() as $model) {
            $this->models[$model] = $callable($this->$model);
        }
        return $this;
    }

    public function getModels()
    {
        return $this->definition->getTables();
    }
}
