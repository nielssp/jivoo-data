<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Expression;

use Jivoo\Core\Parse\ParseInput;
use Jivoo\Data\DataType;

/**
 * A parser for simple SQL-like comparison expressions.
 * 
 * <code>
 * expression ::= ["not"] comparison
 * comparison ::= column operator atomic
 *              | column "is" "null"
 * operator   ::= "like" | "in" | "!=" | "<>" | ">=" | "<=" | "!<" | "!>" | "=" | "<" | ">"
 * column     ::= [table "."] (field | name)
 * table      ::= model | name
 * field      ::= "[" name "]"
 * model      ::= "{" name "}"
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
  public static function scan($expression) {
    $lexer = new RegexLexer(true, 'i');
    $lexer->is = 'is';
    $lexer->not = 'not';
    $lexer->true = 'true';
    $lexer->false = 'false';
    $lexer->null = 'null';
    $lexer->operator = 'like|in|!=|<>|>=|<=|!<|!>|=|<|>';
    $lexer->dot = '\.';
    $lexer->name = '[a-z][a-z0-9]+';
    $lexer->model = '\{(.+?)\}';
    $lexer->field = '\[(.+?)\]';
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
      return floatval($value);
    });
    
    $lexer->map('string', function($value, $matches) {
      return stripslashes($matches[1]);
    });
    
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
    $left = $this->parseAtomic($input);
    if ($input->acceptToken('is')) {
      $input->expectToken('null');
      return new Infix($left, 'is', null);
    }
    $op = $input->expectToken('operator');
    $right = $this->parseAtomic($input);
    return new Infix($left, $op[1], $right);
  }
  
  public static function parseAtomic(ParseInput $input) {
    if ($input->acceptToken('number', $token)) {
      return new Literal(DataType::float(), $token[1]);
    }
    if ($input->acceptToken('true')) {
      return new Literal(DataType::boolean(), true);
    }
    if ($input->acceptToken('false')) {
      return new Literal(DataType::boolean(), false);
    }
    if ($input->acceptToken('string', $token)) {
      return new Literal(DataType::text(), $token[1]);
    }
  }
  
  public static function parseColumn(ParseInput $input) {
    if ($input->acceptToken('model', $mToken)) {
      $input->expectToken('dot');
      if (!$input->acceptToken($type, $fToken))
        $fToken = $input->expectToken('name');
      
    }
  }
}

