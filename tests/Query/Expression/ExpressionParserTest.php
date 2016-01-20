<?php

namespace Jivoo\Data\Query\Expression;

use Jivoo\Data\DataType;
class ExpressionParserTest extends \Jivoo\TestCase {

  protected function _before() {}

  protected function _after() {}

  public function testLex() {
    $expr = '1 = 15.2';
    $tokens = ExpressionParser::lex($expr, array())->toArray();
    $this->assertCount(3, $tokens);
    $this->assertEquals('literal', $tokens[0][0]);
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Literal', $tokens[0][1]);
    $this->assertEquals(1, $tokens[0][1]->value);
    $this->assertEquals('operator', $tokens[1][0]);
    $this->assertEquals('=', $tokens[1][1]);
    $this->assertEquals('literal', $tokens[2][0]);
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Literal', $tokens[2][1]);
    $this->assertEquals(15.2, $tokens[2][1]->value);

    $expr = '%s';
    $tokens = ExpressionParser::lex($expr, array('test'))->toArray();
    $this->assertCount(1, $tokens);
    $this->assertEquals('literal', $tokens[0][0]);
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Literal', $tokens[0][1]);
    $this->assertEquals('test', $tokens[0][1]->value);
  }
  
  public function testParseColumn() {
    $ast = ExpressionParser::parseColumn(ExpressionParser::lex('foo'));
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\FieldAccess', $ast);
    $this->assertEquals('foo', $ast->field);

    $ast = ExpressionParser::parseColumn(ExpressionParser::lex('foo.bar'));
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\FieldAccess', $ast);
    $this->assertEquals('bar', $ast->field);
    $this->assertEquals('foo', $ast->model);

    $ast = ExpressionParser::parseColumn(ExpressionParser::lex('foo.bar'));
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\FieldAccess', $ast);
    $this->assertEquals('bar', $ast->field);
    $this->assertEquals('foo', $ast->model);

    $ast = ExpressionParser::parseColumn(ExpressionParser::lex('[foo].[bar]'));
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\FieldAccess', $ast);
    $this->assertEquals('bar', $ast->field);
    $this->assertEquals('foo', $ast->model);

    $ast = ExpressionParser::parseColumn(ExpressionParser::lex('{Foo}.bar'));
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\FieldAccess', $ast);
    $this->assertEquals('bar', $ast->field);
    $this->assertEquals('Foo', $ast->model);
  }
  
  public function testParseAtomic() {
    $ast = ExpressionParser::parseAtomic(ExpressionParser::lex('foo'));
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\FieldAccess', $ast);
    $this->assertEquals('foo', $ast->field);

    $ast = ExpressionParser::parseAtomic(ExpressionParser::lex('15.5'));
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Literal', $ast);
    $this->assertEquals(15.5, $ast->value);

    $ast = ExpressionParser::parseAtomic(ExpressionParser::lex('?', array(42)));
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Literal', $ast);
    $this->assertEquals(42, $ast->value);

    $ast = ExpressionParser::parseAtomic(ExpressionParser::lex('%_', array(DataType::string(), 'test')));
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Literal', $ast);
    $this->assertEquals('test', $ast->value);
    $this->assertTrue($ast->type->isString());

    $ast = ExpressionParser::parseAtomic(ExpressionParser::lex('%i()', array(array(1, 2, 3))));
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\ArrayLiteral', $ast);
    $this->assertEquals(array(1, 2, 3), $ast->values);
  }
  
  public function testParseComparison() {
    $ast = ExpressionParser::parseComparison(ExpressionParser::lex('3 = 3'));
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Infix', $ast);
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Literal', $ast->left);
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Literal', $ast->right);
    $this->assertEquals('=', $ast->operator);
    $ast = ExpressionParser::parseComparison(ExpressionParser::lex('3 is null'));
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Infix', $ast);
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Literal', $ast->left);
    $this->assertNull($ast->right);
    $this->assertEquals('is', $ast->operator);
  }
  
  public function testParseExpression() {
    $ast = ExpressionParser::parseExpression(ExpressionParser::lex('3 = 3'));
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Infix', $ast);
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Literal', $ast->left);
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Literal', $ast->right);
    $this->assertEquals('=', $ast->operator);
    $ast = ExpressionParser::parseExpression(ExpressionParser::lex('not 3 = 3'));
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Prefix', $ast);
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Infix', $ast->operand);
    $this->assertEquals('not', $ast->operator);
    
    $ast = ExpressionParser::parseExpression(ExpressionParser::lex('not a in %e', array($ast)));
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Prefix', $ast);
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Infix', $ast->operand);
    $this->assertEquals('not', $ast->operator);
  }
}
