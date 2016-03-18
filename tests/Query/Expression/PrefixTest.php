<?php
namespace Jivoo\Data\Query\Expression;

use Jivoo\Data\DataType;

class PrefixTest extends \Jivoo\TestCase
{
    
    public function testInstance()
    {
        $record = [];
        $quoter = $this->getMockBuilder('Jivoo\Data\Query\Expression\Quoter')->getMock();
        $quoter->method('quoteLiteral')->willReturnCallback(function ($type, $value) {
            return $value ? 'true' : 'false';
        });

        $prefix = new Prefix('not', new Literal(DataType::boolean(), true));
        $this->assertFalse($prefix->__invoke($record));
        $this->assertEquals('not true', $prefix->toString($quoter));

        $prefix = new Prefix('not', new Infix(
            new Literal(DataType::boolean(), true),
            'and',
            new Literal(DataType::boolean(), false)
        ));
        $this->assertTrue($prefix->__invoke($record));
        $this->assertEquals('not (true and false)', $prefix->toString($quoter));

        $prefix = new Prefix('!', new Literal(DataType::boolean(), true));
        $this->assertThrows('PHPUnit_Framework_Error', function () use ($prefix, $record) {
            $prefix($record);
        });
    }
}
