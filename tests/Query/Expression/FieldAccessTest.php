<?php
namespace Jivoo\Data\Query\Expression;

use Jivoo\Data\DataType;

class FieldAccessTest extends \Jivoo\TestCase
{
    
    public function testInstance()
    {
        $record = $this->getMockBuilder('Jivoo\Data\Record')->getMock();
        $record->method('__get')->willReturnCallback(function ($property) {
            if ($property === 'foo') {
                return 'bar';
            }
        });
        $quoter = $this->getMockBuilder('Jivoo\Data\Query\Expression\Quoter')->getMock();
        $quoter->method('quoteModel')->willReturnCallback(function ($model) {
            return '{' . $model . '}';
        });
        $quoter->method('quoteField')->willReturnCallback(function ($field) {
            return '[' . $field . ']';
        });
        
        $node = new FieldAccess('foo');
        $this->assertEquals('bar', $node($record));
        $this->assertEquals('[foo]', $node->toString($quoter));

        $node = new FieldAccess('foo', false);
        $this->assertEquals('bar', $node($record));
        $this->assertEquals('foo', $node->toString($quoter));

        $node = new FieldAccess('foo', true, 'Foobar');
        $this->assertEquals('bar', $node($record));
        $this->assertEquals('{Foobar}.[foo]', $node->toString($quoter));

        $node = new FieldAccess('foo', true, 'foobar', false);
        $this->assertEquals('bar', $node($record));
        $this->assertEquals('foobar.[foo]', $node->toString($quoter));
    }
}
