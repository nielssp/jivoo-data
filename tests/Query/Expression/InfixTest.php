<?php
namespace Jivoo\Data\Query\Expression;

use Jivoo\Data\DataType;

class InfixTest extends \Jivoo\TestCase
{
    
    private function getInfix($left, $operator, $right)
    {
        return new Infix(
            new Literal(DataType::detectType($left), $left),
            $operator,
            new Literal(DataType::detectType($right), $right)
        );
    }
    
    public function testInstance()
    {
        $record = $this->getMockBuilder('Jivoo\Data\Record')->getMock();
        $quoter = $this->getMockBuilder('Jivoo\Data\Query\Expression\Quoter')->getMock();
        $quoter->method('quoteLiteral')->willReturnCallback(function ($type, $value) {
            return $value;
        });
        
        $this->assertTrue($this->getInfix(5, '>', 4)->__invoke($record));
        $this->assertFalse($this->getInfix(5, '<', 4)->__invoke($record));
        $this->assertTrue($this->getInfix(5, '=', 5)->__invoke($record));
        $this->assertFalse($this->getInfix(5, '!=', 5)->__invoke($record));
        $this->assertTrue($this->getInfix(5, '<>', 4)->__invoke($record));
        $this->assertFalse($this->getInfix(5, '!>', 4)->__invoke($record));
        $this->assertTrue($this->getInfix(5, '!<', 4)->__invoke($record));
        $this->assertFalse($this->getInfix(5, '<=', 4)->__invoke($record));
        $this->assertTrue($this->getInfix(5, '>=', 5)->__invoke($record));
        $this->assertTrue($this->getInfix(2, 'in', array(1, 2, 4))->__invoke($record));
        $isNull = new Infix(new Literal(DataType::integer(), null), 'is', null);
        $this->assertTrue($isNull->__invoke($record));

        $this->assertEquals('2 = 5', $this->getInfix(2, '=', 5)->toString($quoter));
        
        $this->assertThrows('PHPUnit_Framework_Error', function () use ($record) {
            $this->getInfix(5, '==', 5)->__invoke($record);
        });
    }
    
    public function testLike()
    {
        $record = $this->getMockBuilder('Jivoo\Data\Record')->getMock();
        
        $this->assertTrue($this->getInfix('test', 'like', 'test')->__invoke($record));
        $this->assertTrue($this->getInfix('test', 'like', '%test%')->__invoke($record));
        $this->assertTrue($this->getInfix('test', 'like', '_est')->__invoke($record));
        $this->assertTrue($this->getInfix('test', 'like', '%')->__invoke($record));
        $this->assertFalse($this->getInfix('test', 'like', 'est%')->__invoke($record));
    }
}
