<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database;

use Jivoo\Data\Model;

/**
 * A database schema.
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

    /**
     * {@inheritdoc}
     */
    public function __get($model)
    {
        if (!isset($this->models[$model])) {
            $definition = $this->definition->getDefinition($model);
            if (! isset($definition)) {
                throw new \Jivoo\Data\UndefinedModelException('Undefined model: ' . $model);
            }
            $this->models[$model] = new \Jivoo\Data\SimpleModel(
                $model,
                $this->database->$model,
                $definition
            );
        }
        return $this->models[$model];
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($model)
    {
        return in_array($model, $this->getModels());
    }
    
    /**
     * Convert models in schema.
     *
     * @param \Jivoo\Data\Database\callable $callable A function that accepts
     * a {@see Model} and returns a {@see Model}.
     * @return self
     */
    public function map(callable $callable)
    {
        foreach ($this->getModels() as $model) {
            $this->models[$model] = $callable($this->$model);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getModels()
    {
        return $this->definition->getTables();
    }
}
