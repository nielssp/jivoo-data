<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Builders;

use Jivoo\Data\Query\UpdateSelection;
use Jivoo\Data\Query\Updatable;

/**
 * An update selection.
 */
class UpdateSelectionBuilder extends SelectionBase implements Updatable, UpdateSelection
{

    /**
     * @var array
     */
    private $data = array();

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function set($field, $value = null)
    {
        $clone = clone $this;
        if (is_array($field)) {
            foreach ($field as $f => $val) {
                $clone->data[$f] = $val;
            }
        } else {
            $clone->data[$field] = $value;
        }
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function update()
    {
        return $this->source->updateSelection($this);
    }
}
