<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query;

/**
 * An update selection.
 */
interface UpdateSelection extends Selection
{

    /**
     * The update data as an associative array. The keys are field names and
     * the values are either values or expressions.
     *
     * @return (mixed|Expression)[]
     */
    public function getData();
}
