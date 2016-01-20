<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query;

use Jivoo\Data\DataType;
use Jivoo\Data\Query\Expression;
use Jivoo\Data\Query\Builders\ExpressionBuilder;
use Jivoo\Data\Model;
use Jivoo\Data\Query\Expression\Quoter;

/**
 * Expression utilities.
 */
class E {
  private function __construct() {}
  
  /**
   * Construct and expression.
   * @param Expression|string $expr Expression
   * @param mixed $vars,... Additional values to replace placeholders in
   * $expr with.
   * @return ExpressionBuilder Expression builder.
   */
  public static function e($expr) {
    $vars = func_get_args();
    array_shift($vars);
    return new ExpressionBuilder($expr, $vars);
  }

  /**
   * Escape string for use with the LIKE operator.
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
   * %e %expr %expression // A subexpression (instance of {@see Expression})
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
      if (isset($matches[3])) {
        if ($matches[3] == '_') {
          if (!is_string($value)) {
            assume($value instanceof DataType);
            $value = $value->placeholder;
          }
          $matches[3] = ltrim($value, '%');
          $value = $vars[$i];
          $i++;
        }
        if ($matches[3] == 'e' or $matches[3] == 'expr' or $matches[3] == 'expression') {
          assume($value instanceof Expression);
          return '(' . $value->toString($quoter) . ')';
        }
        if ($matches[3] == 'm' or $matches[3] == 'model') {
          if (!is_string($value)) {
            assume($value instanceof Model);
            $value = $value->getName();
          }
          return $quoter->quoteModel($value);
        }
        if ($matches[3] == 'c' or $matches[3] == 'column' or $matches[3] == 'field') {
          assume(is_string($value));
          return $quoter->quoteField($value);
        }
        if ($matches[3] != '()')
          $type = DataType::fromPlaceholder($matches[3]);
      }
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
}
