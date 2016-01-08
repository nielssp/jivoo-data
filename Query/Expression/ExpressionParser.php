<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Expression;

use Jivoo\Core\Parse\ParseInput;

/**
 * A parser for expressions.
 */
class ExpressionParser {


  /**
   * @param string $expression
   * @return ParseInput
   */
  public static function scan($expression) {
    $lexer = new RegexLexer(true, 'i');
    $lexer->keyword = 'and|not|or|like';
    $lexer->name = '[a-z][a-z0-9]+';
    $lexer->float = '[0-9]+.[0-9]+';
    $lexer->integer = '[0-9]+';
    
    return new ParseInput($lexer($expression));
  }
  
  /**
   * @param ParseInput $input
   * @return ast
   */
  public static function parseExpression(ParseInput $input) {
  }
  
  public static function parsePrefix(ParseInput $input) {
  }
}

