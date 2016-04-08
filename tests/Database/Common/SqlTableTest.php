<?php
namespace Jivoo\Data\Database\Common;

use Jivoo\Data\Query\Builders\DeleteSelectionBuilder;
use Jivoo\Data\Query\Builders\UpdateSelectionBuilder;
use Jivoo\Data\Query\Builders\ReadSelectionBuilder;
use Jivoo\Data\Query\E;

class SqlTableTest extends \Jivoo\TestCase
{
    
    public function testRead()
    {
        $def = new \Jivoo\Data\Database\DatabaseDefinitionBuilder();
        $db = $this->getMockBuilder('Jivoo\Data\Database\Common\SqlDatabase')
            ->getMock();
        $db->method('getDefinition')
            ->willReturn($def);
        $db->method('quoteModel')
            ->willReturnCallback(function ($model) {
                return '{' . $model . '}';
            });
        $db->method('quoteLiteral')
            ->willReturnCallback(function ($type, $value) {
                return '"' . $value . '"';
            });
        
        $table = new SqlTable($db, 'Foo');
        
        // Select all
        $selection = new ReadSelectionBuilder($table);
        $db->expects($this->exactly(2))
            ->method('query')
            ->withConsecutive(
                $this->equalTo('SELECT {Foo}.* FROM {Foo}'),
                $this->equalTo('SELECT {Foo}.* FROM {Foo} WHERE group = "user"')
            )
            ->willReturn($this->getMock('Jivoo\Data\Database\ResultSet'));
        $selection->toArray();
        
        // Select with a predicate
        $selection = $selection->where('group = "user"');
        $selection->toArray();
    }
}
