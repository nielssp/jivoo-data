<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Expression;

use Jivoo\Data\Query\Expression;

/**
 * A literal.
 */
class FieldAccess extends Node implements Expression, Atomic
{

    public $field;
    
    public $quoteField;

    public $model;
    
    public $quoteModel;

    public function __construct($field, $quoteField = true, $model = null, $quoteModel = true)
    {
        $this->field = $field;
        $this->quoteField = $quoteField;
        $this->model = $model;
        $this->quoteModel = $quoteModel;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $data)
    {
        return $data[$this->field];
    }

    /**
     * {@inheritdoc}
     */
    public function toString(Quoter $quoter)
    {
        $str = '';
        if (isset($this->model)) {
            if ($this->quoteModel) {
                $str = $quoter->quoteModel($this->model) . '.';
            } else {
                $str = $this->model . '.';
            }
        }
        if ($this->quoteField) {
            $str .= $quoter->quoteField($this->field);
        } else {
            $str .= $this->field;
        }
        return $str;
    }
}
