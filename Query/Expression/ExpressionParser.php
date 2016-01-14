<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Expression;

use Jivoo\Core\Parse\ParseInput;
use Jivoo\Data\DataType;
use Jivoo\Core\Parse\RegexLexer;
use Jivoo\Data\Query\Expression;

/**
 * A parser for simple SQL-like comparison expressions.
 * 
 * <code>
 * expression ::= ["not"] comparison
 * comparison ::= atomic operator atomic
 *              | atomic "is" "null"
 * operator   ::= "like" | "in" | "!=" | "<>" | ">=" | "<=" | "!<" | "!>" | "=" | "<" | ">"
 * column     ::= [table "."] (field | name)
 * table      ::= model | name
 * field      ::= "[" name "]"
 *              | "%" ("field" | "column" | "c")
 * model      ::= "{" name "}"
 *              | "%" ("model" | "m")
 * atomic     ::= number
 *              | "true"
 *              | "false"
 *              | string
 *              | placeholder
 *              | column
 * </code>
 */
class ExpressionParser {


  /**
   * @param string $expression
   * @return ParseInput
   */
  public static function lex($expression, $vars) {
    $lexer = new RegexLexer(true, 'i');
    $lexer->is = 'is';
    $lexer->not = 'not';
    $lexer->bool = 'true|false';
    $lexer->null = 'null';
    $lexer->operator = 'like|in|!=|<>|>=|<=|!<|!>|=|<|>|and|or';
    $lexer->dot = '\.';
    $lexer->name = '[a-z][a-z0-9]*';
    $lexer->model = '\{(.+?)\}';
    $lexer->modelPlaceholder = '%(model|m)';
    $lexer->field = '\[(.+?)\]';
    $lexer->fieldPlaceholder = '%(column|field|c)';
    $lexer->number = '-?(0|[1-9]\d*)(\.\d+)?([eE][+-]?\d+)?';
    $lexer->string = '"((?:[^"\\\\]|\\\\.)*)"';
    $lexer->placeholder = '((\?)|%([a-z_\\\\]+))(\(\))?';
    
    $lexer->map('model', function($value, $matches) {
      return $matches[1];
    });

    $lexer->map('field', function($value, $matches) {
      return $matches[1];
    });
    
    $lexer->map('number', function($value) {
      if (strpos($value, '.') !== false or stripos($value, 'e') !== false) 
        return new Literal(DataType::float(), floatval($value));
      else 
        return new Literal(DataType::integer(), intval($value));
    });
    $lexer->mapType('number', 'literal');
    
    $lexer->map('string', function($value, $matches) {
      return new Literal(DataType::text(), stripslashes($matches[1]));
    });
    $lexer->mapType('string', 'literal');
    
    $lexer->map('bool', function($value) {
      return new Literal(DataType::boolean(), strtolower($value) == 'true');
    });
    $lexer->mapType('bool', 'literal');

    $lexer->map('model', function($value, $matches) {
      return $matches[1];
    });
    $lexer->map('field', function($value, $matches) {
      return $matches[1];
    });
    
    $i = 0;
    $lexer->map('modelPlaceholder', function($value, $matches) use(&$i, $vars) {
      $value = $vars[$i];
      $i++;
      if (!is_string($value)) {
        assume($value instanceof Model);
        $value = $value->getName();
      }
      return $value;
    });
    $lexer->mapType('modelPlaceholder', 'model');
    $lexer->map('fieldPlaceholder', function($value, $matches) use(&$i, $vars) {
      $value = $vars[$i];
      $i++;
      assume(is_string($value));
      return $value;
    });
    $lexer->mapType('fieldPlaceholder', 'field');
    
    
    $lexer->map('placeholder', function($value, $matches) use(&$i, $vars) {
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
          return $value;
        }
        if ($matches[3] != '()')
          $type = DataType::fromPlaceholder($matches[3]);
      }
      if (!isset($type)) {
        $type = DataType::detectType($value);
      }
      if (isset($matches[4]) or (isset($matches[3]) and $matches[3] == '()')) {
        assume(is_array($value));
        foreach ($value as $key => $v)
          $value[$key] = $v;
        return new ArrayLiteral($type, $value);
      }
      return new Literal($type, $value);
    });
    $lexer->mapType('placeholder', 'literal');
    
    return new ParseInput($lexer($expression));
  }
  
  /**
   * @param ParseInput $input
   * @return ast
   */
  public static function parseExpression(ParseInput $input) {
    $not = $input->acceptToken('not');
    $expr = self::parseComparison($input);
    if ($not)
      $expr = new Prefix('not', $expr);
    return $expr;
  }
  
  public static function parseComparison(ParseInput $input) {
    $left = self::parseAtomic($input);
    if ($input->acceptToken('is')) {
      $input->expectToken('null');
      return new Infix($left, 'is', null);
    }
    $op = $input->expectToken('operator');
    $right = self::parseAtomic($input);
    return new Infix($left, $op[1], $right);
  }
  
  public static function parseAtomic(ParseInput $input) {
    if ($input->acceptToken('literal', $token)) {
      return $token[1];
    }
    return self::parseColumn($input);
  }
  
  public static function parseColumn(ParseInput $input) {
    if ($input->acceptToken('model', $mToken)) {
      $input->expectToken('dot');
      if (!$input->acceptToken('field', $fToken))
        $fToken = $input->expectToken('name');
      return new FieldAccess($fToken[1], $mToken[1]);
    }
    if (!$input->acceptToken('field', $first))
      $first = $input->expectToken('name');
    if ($input->acceptToken('dot')) {
      if (!$input->acceptToken('field', $fToken))
        $fToken = $input->expectToken('name');
      return new FieldAccess($fToken[1], $first[1]);
    }
    return new FieldAccess($first[1]);
  }
}

