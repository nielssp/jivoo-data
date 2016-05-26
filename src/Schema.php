<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data;

/**
 * A schema consisting of multiple models.
 */
interface Schema
{

    /**
     * Get a model.
     *
     * @param string $model
     *            Model name
     * @return Model Model.
     * @throws UndefinedModelException If model is undefined.
     */
    public function __get($model);

    /**
     * Whether a model is defined in the schema.
     *
     * @param string $model
     *            Model name.
     * @return bool True if model exists, false otherwise.
     */
    public function __isset($model);

    /**
     * Get names of models in schema.
     *
     * @return string[] Names of models in schema.
     */
    public function getModels();
}
