<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Builders;

use Jivoo\InvalidMethodException;
use Jivoo\Data\Query\Expression;
use Jivoo\Data\Query\Boolean;
use Jivoo\Data\Record;
use Jivoo\Data\Query\Expression\Quoter;
use Jivoo\Data\Query\Expression\ExpressionParser;
use Jivoo\Core\Parse\ParseInput;

/**
 * Expression builder.
 */
class ExpressionBuilder implements Expression, Boolean {
  private $expr = '';
  
  private $vars = array();
  
  private $ast = null;

  /**
   * Construct condition. The function {@see where} is an alias.
   * @param Condition|string $expr Expression.
   * @param mixed $vars Additional values to replace placeholders in
   * $expr with.
   */
  public function __construct($expr = null, $vars = array()) {
    if (isset($expr) and !empty($expr)) {
      $this->expr = $expr;
      $this->vars = $vars;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __invoke(Record $record) {
    if (!isset($this->ast)) {
      $tokens = ExpressionParser::lex($this->expr, $this->vars);
      $this->ast = ExpressionParser::parseExpression($tokens);
    }
    return $this->ast->__invoke($record);
  }

  /**
   * {@inheritdoc}
   */
  public function toString(Quoter $quoter) {
    return self::interpolate($this->expr, $this->vars, $quoter);
  }

  /**
   * {@inheritdoc}
   */
  public function __call($method, $args) {
    switch ($method) {
      case 'and':
        return call_user_func_array(array($this, 'andWhere'), $args);
      case 'or':
        return call_user_func_array(array($this, 'orWhere'), $args);
    }
    throw new InvalidMethodException(tr('Invalid method: %1', $method));
  }


  /**
   * {@inheritdoc}
   */
  public function where($expr) {
    $args = func_get_args();
    return call_user_func_array(array($this, 'andWhere'), $args);
  }

  /**
   * {@inheritdoc}
   */
  public function andWhere($expr) {
    if (empty($expr)) {
      return $this;
    }
    if ($this->string != '')
      $this->string .= ' AND ';
    if ($expr instanceof Expression) {
      $this->vars[] = $expr;
      $this->expr .= '%e';
    }
    else {
      $vars = func_get_args();
      array_shift($vars);
      $this->vars = array_merge($this->vars, $vars);
      $this->string .= $expr;
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function orWhere($expr) {
    if (empty($expr)) {
      return $this;
    }
    if ($this->string != '')
      $this->string .= ' OR ';
    if ($expr instanceof Expression) {
      $this->vars[] = $expr;
      $this->expr .= '%e';
    }
    else {
      $vars = func_get_args();
      array_shift($vars);
      $this->vars = array_merge($this->vars, $vars);
      $this->string .= $expr;
    }
    return $this;
  }
  

}
