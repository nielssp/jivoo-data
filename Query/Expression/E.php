<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Expression;

use Jivoo\Data\DataType;
use Jivoo\InvalidMethodException;
use Jivoo\Data\Query\Expression;

/**
 * Expression builder.
 */
class E implements Expression {
  private $string = '';
  
  private $vars = array();

  /**
   * Construct condition. The function {@see where} is an alias.
   * @param Condition|string $expr Expression.
   * @param mixed $vars,... Additional values to replace placeholders in
   * $expr with.
   */
  public function __construct($expr = null) {
    if (isset($expr) and !empty($expr)) {
      $args = func_get_args();
      call_user_func_array(array($this, 'andWhere'), $args);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getString() {
    return $this->string;
  }

  /**
   * {@inheritdoc}
   */
  public function getVars() {
    return $this->vars;
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
    $vars = func_get_args();
    array_shift($vars);
    $this->vars = array_merge($this->vars, $vars);
    if ($this->string != '')
      $this->string .= ' AND ';
    $this->string .= $expr;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function orWhere($expr) {
    if (empty($expr)) {
      return $this;
    }
    $vars = func_get_args();
    array_shift($vars);
    $this->vars = array_merge($this->vars, $vars);
    if ($this->string != '')
      $this->string .= ' OR ';
    $this->string .= $expr;
    return $this;
  }
  
  public static function e($expression) {
    $vars = func_get_args();
    array_shift($vars);
    return new E($expression, $vars);
  }

  /**
   * Escape string for use with the SQL LIKE operator.
   * @param string $string String.
   * @return string Escaped string.
   */
  public static function escapeLike($string) {
    return str_replace(
      array('%', '_'),
      array('\\%', '\\_'),
      str_replace('\\', '\\\\', $string)
    );
  }
  
/**
   * Substitute and encode variables in an expression.
   * 
   * Placeholders (see also {@see DataType::fromPlaceHolder()}:
   * <code>
   * true // Boolean true
   * false // Boolean false
   * {AnyModelName} // A model name
   * [anyFieldName] // A column/field name
   * "any string" // A string
   * ? // Any scalar value.
   * %m %model // A table/model object or name
   * %c %column %field // A column/field name
   * %_ // A placeholder placeholder, can also be a type, e.g. where(..., 'id = %_', $type, $value)
   * %i %int %integer // An integer value
   * %f %float // A floating point value
   * %s %str %string // A string
   * %t $text // Text
   * %b %bool %boolean // A boolean value
   * %date // A date value
   * %d %datetime // A date/time value
   * %n %bin %binary // A binary object
   * %AnyEnumClassName // An enum value of that class
   * %anyPlaceholder() // A tuple of values
   * </code>
   * 
   * @param string|Condition $format Expression format, use placeholders instead of values.
   * @param mixed[] $vars List of values to replace placeholders with.
   * @param Quoter $quoter Quoter object for quoting identifieres and literals.
   * @return string The interpolated expression.
   */
  public static function interpolate($format, $vars, Quoter $quoter) {
    if ($format instanceof self)
      return $format->toString($quoter);
    assume(is_string($format));
    $boolean = DataType::boolean();
    $true = $quoter->quoteLiteral($boolean, true);
    $false = $quoter->quoteLiteral($boolean, false);
    $format = preg_replace('/\btrue\b/i', $true, $format);
    $format = preg_replace('/\bfalse\b/i', $false, $format);
    $string = DataType::text();
    $format = preg_replace_callback('/"((?:[^"\\\\]|\\\\.)*)"|\{(.+?)\}|\[(.+?)\]/', function($matches) use($quoter, $string) {
      if (isset($matches[3]))
        return $quoter->quoteField($matches[3]);
      else if (isset($matches[2]))
        return $quoter->quoteModel($matches[2]);
      else
        return $quoter->quoteLiteral($string, stripslashes($matches[1]));
    }, $format);
    $i = 0;
    return preg_replace_callback('/((\?)|%([a-z_\\\\]+))(\(\))?/i', function($matches) use($vars, &$i, $quoter) {
      $value = $vars[$i];
      $i++;
      $type = null;
      if (isset($matches[3]) and $matches[3] == '_') {
        if (!is_string($value)) {
          assume($value instanceof DataType);
          $value = $value->placeholder;
        }
        $matches[3] = ltrim($value, '%');
        $value = $vars[$i];
        $i++;
      }
      if (isset($matches[3]) and ($matches[3] == 'm' or $matches[3] == 'model')) {
        if (!is_string($value)) {
          assume($value instanceof BasicModel);
          $value = $value->getName();
        }
        return $quoter->quoteModel($value);
      }
      if (isset($matches[3]) and ($matches[3] == 'c' or $matches[3] == 'column' or $matches[3] == 'field')) {
        assume(is_string($value));
        return $quoter->quoteField($value);
      }
      if (isset($matches[3]) and $matches[3] != '()')
        $type = DataType::fromPlaceholder($matches[3]);
      if (!isset($type))
        $type = DataType::detectType($value);
      if (isset($matches[4]) or (isset($matches[3]) and $matches[3] == '()')) {
        assume(is_array($value));
        foreach ($value as $key => $v)
          $value[$key] = $quoter->quoteLiteral($type, $v);
        return '(' . implode(', ', $value) . ')';
      }
      return $quoter->quoteLiteral($type, $value);
    }, $format);
  }
  
  public function toString(Quoter $quoter) {
    $sqlString = '';
    foreach ($this->clauses as $clause) {
      if ($sqlString != '') {
        $sqlString .= ' ' . $clause[0] . ' ';
      }
      if ($clause['clause'] instanceof Expression) {
        if ($clause['clause']->hasClauses()) {
          if ($clause['clause'] instanceof NotCondition) {
            $sqlString .= 'NOT ';
          }
          $sqlString .= '(' . $clause[1]->toString($quoter) . ')';
        }
      }
      else {
        $sqlString .= self::interpolate($clause[1], $clause[2], $quoter);
      }
    }
    return $sqlString;
  }
}
