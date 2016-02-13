<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query;

/**
 * An interface for updatable models and selections.
 */
interface Updatable extends Selectable
{

    /**
     * Assign value to field.
     * If `$field` is an associative array, then multiple
     * fields are assigned.
     *
     * @param (mixed|Expression)[]|mixed|Expression $field
     *            Field name or associative array of update data (see
     *            {@see UpdateSelection::getData} for format.
     * @param mixed|Expression $value
     *            Field value. May be an expression.
     * @return static
     */
    public function set($field, $value = null);

    /**
     * Execute updates.
     *
     * @return int Number of updated records.
     */
    public function update();
}
