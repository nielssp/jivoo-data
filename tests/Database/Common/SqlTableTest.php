<?php
namespace Jivoo\Data\Database\Common;

use Jivoo\Data\Query\Builders\DeleteSelectionBuilder;
use Jivoo\Data\Query\Builders\UpdateSelectionBuilder;
use Jivoo\Data\Query\Builders\ReadSelectionBuilder;
use Jivoo\Data\Query\E;

class SqlTableTest extends \Jivoo\TestCase
{
    
    private function getDb()
    {
        $def = new \Jivoo\Data\Database\DatabaseDefinitionBuilder();
        $db = $this->getMockBuilder('Jivoo\Data\Database\Common\SqlDatabase')
            ->getMock();
        $db->method('getDefinition')
            ->willReturn($def);
        $db->method('sqlLimitOffset')
            ->willReturnCallback(function ($limit, $offset) {
                return 'LIMIT ' . $limit . ' OFFSET ' . $offset;
            });
        $db->method('quoteModel')
            ->willReturnCallback(function ($model) {
                return '{' . $model . '}';
            });
        $db->method('quoteLiteral')
            ->willReturnCallback(function ($type, $value) {
                return '"' . $value . '"';
            });
        return $db;
    }
    
    private function getResultSet(array $rows)
    {
        $set = $this->getMockBuilder('Jivoo\Data\Database\ResultSet')->getMock();
        $set->method('hasRows')
            ->willReturnCallback(function () use ($rows) {
                return count($rows) > 0;
            });
        $set->method('fetchRow')
            ->willReturnCallback(function () use (&$rows) {
                return array_values(array_shift($rows));
            });
        $set->method('fetchAssoc')
            ->willReturnCallback(function () use (&$rows) {
                return array_shift($rows);
            });
        return $set;
    }
    
    public function testRead()
    {
        $db = $this->getDb();
        
        $table = new SqlTable($db, 'Foo');
        
        // Select all
        $selection = new ReadSelectionBuilder($table);
        $db->expects($this->exactly(7))
            ->method('query')
            ->withConsecutive(
                [$this->equalTo('SELECT {Foo}.* FROM {Foo}')],
                [$this->equalTo('SELECT {Foo}.* FROM {Foo} WHERE group = "user" ORDER BY name DESC')],
                [$this->equalTo(
                    'SELECT {Foo}.* FROM {Foo} GROUP BY group, name HAVING name IN ("foo", "bar", "foobar")'
                )],
                [$this->equalTo('SELECT {Foo}.* FROM {Foo} LIMIT 10 OFFSET 5')],
                [$this->equalTo('SELECT f.* FROM {Foo} AS f LEFT JOIN {Foo} AS o ON f.id = o.id')],
                [$this->equalTo('SELECT DISTINCT a FROM {Foo}')],
                [$this->equalTo('SELECT {Foo}.*, 2 + 2 AS ans FROM {Foo}')]
            )
            ->willReturn($this->getMock('Jivoo\Data\Database\ResultSet'));
        $selection->toArray();
        
        // Select with a predicate and ordering
        $selection->where('group = "user"')->orderByDescending('name')->toArray();
        
        // Select with groups
        $selection->groupBy(['group', 'name'], E::e('name IN %s()', ['foo', 'bar', 'foobar']))
            ->toArray();
        
        // Select with limit and offset
        $selection->limit(10)->offset(5)->toArray();
        
        // Select with join and alias
        $selection->alias('f')->leftJoin($table, 'f.id = o.id', 'o')->toArray();
        
        // Select with projection and distinct
        $selection->distinct()->select('a');
        
        // Select with additional fields
        $selection->with('ans', '2 + 2')->toArray();
    }
    
    public function testCount()
    {
        $db = $this->getDb();
        
        $table = new SqlTable($db, 'Foo');
        
        // Count all
        $selection = new ReadSelectionBuilder($table);
        $db->expects($this->exactly(2))
            ->method('query')
            ->withConsecutive(
                [$this->equalTo('SELECT COUNT(*) AS _count FROM {Foo}')],
                [$this->equalTo(
                    'SELECT COUNT(*) AS _count FROM (SELECT 1 FROM {Foo} GROUP BY a) AS _selection_count'
                )]
            )
            ->willReturnCallback(function () {
                return $this->getResultSet([['_count' => 42]]);
            });
        $this->assertEquals(42, $selection->count());
        
        // Count groups
        $this->assertEquals(42, $selection->groupBy('a')->count());
    }
}
