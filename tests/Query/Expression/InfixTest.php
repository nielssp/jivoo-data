<?php
namespace Jivoo\Data\Query\Expression;

use Jivoo\Data\DataType;

class InfixTest extends \Jivoo\TestCase
{
    
    public function testInstance()
    {
        $record = $this->getMockBuilder('Jivoo\Data\Record')->getMock();
        $quoter = $this->getMockBuilder('Jivoo\Data\Query\Expression\Quoter')->getMock();
        $quoter->method('quoteLiteral')->willReturnCallback(function ($type, $value) {
            return $value;
        });
        
        $getInfix = function ($left, $operator, $right) {
            return new Infix(
                new Literal(DataType::detectType($left), $left),
                $operator,
                new Literal(DataType::detectType($right), $right)
            );
        };
                
        $this->assertTrue($getInfix(5, '>', 4)->__invoke($record));
        $this->assertFalse($getInfix(5, '<', 4)->__invoke($record));
        $this->assertTrue($getInfix(5, '=', 5)->__invoke($record));
        $this->assertFalse($getInfix(5, '!=', 5)->__invoke($record));
        $this->assertTrue($getInfix(5, '<>', 4)->__invoke($record));
        $this->assertFalse($getInfix(5, '!>', 4)->__invoke($record));
        $this->assertTrue($getInfix(5, '!<', 4)->__invoke($record));
        $this->assertFalse($getInfix(5, '<=', 4)->__invoke($record));
        $this->assertTrue($getInfix(5, '>=', 5)->__invoke($record));
        $this->assertTrue($getInfix(2, 'in', array(1, 2, 4))->__invoke($record));
        $this->assertTrue($getInfix('test', 'like', 'test')->__invoke($record));
        $isNull = new Infix(new Literal(DataType::integer(), null), 'is', null);
        $this->assertTrue($isNull->__invoke($record));

        $this->assertEquals('2 = 5', $getInfix(2, '=', 5)->toString($quoter));
        
        $this->assertThrows('PHPUnit_Framework_Error', function () use ($getInfix, $record) {
            $getInfix(5, '==', 5)->__invoke($record);
        });
    }
}
