<?php
namespace Jivoo\Data\Query\Expression;

use Jivoo\Data\DataType;

class ArrayLiteralTest extends \Jivoo\TestCase
{
    
    public function testInstance()
    {
        $record = [];
        $quoter = $this->getMockBuilder('Jivoo\Data\Query\Expression\Quoter')->getMock();
        $quoter->method('quoteLiteral')->willReturnCallback(function ($type, $value) {
            return '"' . $value . '"';
        });
        
        $array = new ArrayLiteral(DataType::string(), array('foo', 'bar'));
        
        $this->assertEquals(array('foo', 'bar'), $array($record));
        $this->assertEquals('("foo", "bar")', $array->toString($quoter));
    }
}
