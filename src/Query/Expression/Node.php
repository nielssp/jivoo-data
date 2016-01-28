<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Expression;

use Jivoo\Data\Query\Expression;
use Jivoo\Data\Query\Boolean;
use Jivoo\InvalidMethodException;

/**
 * Base classs for AST classes.
 */
abstract class Node implements Boolean
{

    /**
     * {@inheritdoc}
     */
    public function __call($method, $args)
    {
        switch ($method) {
            case 'and':
                return call_user_func_array(array(
                    $this,
                    'andWhere'
                ), $args);
            case 'or':
                return call_user_func_array(array(
                    $this,
                    'orWhere'
                ), $args);
        }
        throw new InvalidMethodException('Invalid method: ' . $method);
    }
    
    /**
     * {@inheritdoc}
     */
    public function where($expr)
    {
        $args = func_get_args();
        return call_user_func_array(array(
            $this,
            'andWhere'
        ), $args);
    }
    
    /**
     * {@inheritdoc}
     */
    public function andWhere($expr)
    {
        if (empty($expr)) {
            return $this;
        }
        if (! ($expr instanceof Expression)) {
            $vars = func_get_args();
            $expr = new ExpressionParser($expr, $vars);
        }
        return new Infix($this, 'and', $expr);
    }
    
    /**
     * {@inheritdoc}
     */
    public function orWhere($expr)
    {
        if (empty($expr)) {
            return $this;
        }
        if (! ($expr instanceof Expression)) {
            $vars = func_get_args();
            $expr = new ExpressionParser($expr, $vars);
        }
        return new Infix($this, 'or', $expr);
    }
}
