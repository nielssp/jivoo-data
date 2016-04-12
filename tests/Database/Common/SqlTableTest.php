<?php
namespace Jivoo\Data\Database\Common;

use Jivoo\Data\Database\DatabaseDefinitionBuilder;
use Jivoo\Data\DataType;
use Jivoo\Data\DefinitionBuilder;
use Jivoo\Data\Query\Builders\ReadSelectionBuilder;
use Jivoo\Data\Query\Builders\SelectionBuilder;
use Jivoo\Data\Query\Builders\UpdateSelectionBuilder;
use Jivoo\Data\Query\E;

class SqlTableTest extends SqlTestBase
{
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
    
    public function testUpdate()
    {
        $db = $this->getDb();
        
        $table = new SqlTable($db, 'Foo');
        
        $selection = new UpdateSelectionBuilder($table);
        $selection = $selection->set('a', 'foo');
  
        // Update all
        $db->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('UPDATE {Foo} SET a = "foo", b = "baz", c = a + b')],
                [$this->equalTo('UPDATE {Foo} SET a = "foo" WHERE group = "user" ORDER BY name DESC')],
                [$this->equalTo('UPDATE {Foo} SET a = "foo" LIMIT 10')]
            )
            ->willReturn(0);
        $selection->set('b', 'baz')->set('c', E::e('a + b'))->update();
        
        // Update with predicate and ordering
        $selection->where('group = "user"')->orderByDescending('name')->update();
        
        // Update with limit
        $selection->limit(10)->update();
    }
    
    public function testDelete()
    {
        $db = $this->getDb();
        
        $table = new SqlTable($db, 'Foo');
        
        $selection = new SelectionBuilder($table);
  
        // Update all
        $db->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('DELETE FROM {Foo}')],
                [$this->equalTo('DELETE FROM {Foo} WHERE group = "user" ORDER BY name DESC')],
                [$this->equalTo('DELETE FROM {Foo} LIMIT 10')]
            )
            ->willReturn(0);
        $selection->delete();
        
        // Update with predicate and ordering
        $selection->where('group = "user"')->orderByDescending('name')->delete();
        
        // Update with limit
        $selection->limit(10)->delete();
    }
    
    public function testInsert()
    {
        $db = $this->getDb();
        
        $table = new SqlTable($db, 'Foo');
        
        // Update all
        $db->expects($this->exactly(3))
            ->method('insert')
            ->withConsecutive(
                [$this->equalTo('INSERT INTO {Foo} (a, b, c) VALUES ("foo", "bar", "baz")')],
                [$this->equalTo('INSERT INTO {Foo} (a) VALUES ("foo"), ("bar"), ("baz")')],
                [$this->equalTo('REPLACE INTO {Foo} (a) VALUES ("foo")')]
            )
            ->willReturn(0);
        $table->insert(['a' => 'foo', 'b' => 'bar', 'c' => 'baz']);
        
        $table->insertMultiple([['a' => 'foo'], ['a' => 'bar'], ['a' => 'baz']]);
        
        $table->insert(['a' => 'foo'], true);
        
        $table->insertMultiple([]);
    }
}
