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
        if (is_array($field)) {
            foreach ($field as $f => $val) {
                $this->set($f, $val);
            }
        } else {
            $this->data[$field] = $value;
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function update()
    {
        return $this->source->updateSelection($this);
    }
}
