<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query;

use Jivoo\Data\Query\Builders\SelectionBuilder;
use Jivoo\Data\Query\Builders\DeleteSelectionBuilder;

/**
 * A trait that implements {@see Deletable}.
 */
trait DeletableTrait
{
    use SelectableTrait;

    /**
     * Delete selected records.
     *
     * @return int Number of deleted records.
     */
    public function delete()
    {
        $selection = new DeleteSelectionBuilder($this->getSource());
        return $selection->delete();
    }
}
