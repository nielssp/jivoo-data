<?php

namespace Jivoo\Data\Database\Common;

use Jivoo\Data\DataType;
use Jivoo\Json;

class MysqlTypeAdapterTest extends SqlTestBase
{

    public function testEncode()
    {
        $db = $this->getDb();
        $adapter = new MysqlTypeAdapter($db);

        $this->assertSame('NULL', $adapter->encode(DataType::integer(), null));
        $this->assertSame(15, $adapter->encode(DataType::integer(), '15'));
        $this->assertSame(33.0, $adapter->encode(DataType::float(), 33));
        $this->assertSame(1, $adapter->encode(DataType::boolean(), true));
        $this->assertSame(0, $adapter->encode(DataType::boolean(), false));
        $this->assertRegExp('/^"\d{4}-\d{2}-\d{2}"$/', $adapter->encode(DataType::date(), time()));
        $this->assertRegExp(
            '/^"\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}"$/',
            $adapter->encode(DataType::dateTime(), time())
        );
        
        $this->assertSame('"foo bar baz"', $adapter->encode(DataType::string(), 'foo bar baz'));
        $this->assertSame('"[]"', $adapter->encode(DataType::object(), []));
    }
    
    public function testDecode()
    {
        $db = $this->getDb();
        $adapter = new MysqlTypeAdapter($db);
        
        $this->assertNull($adapter->decode(DataType::integer(), null));
        $this->assertSame(15, $adapter->decode(DataType::integer(), '15'));
        $this->assertSame(33.0, $adapter->decode(DataType::float(), 33));
        $this->assertSame(true, $adapter->decode(DataType::boolean(), 1));
        $this->assertSame(false, $adapter->decode(DataType::boolean(), 0));
        $time = time();
        $encoded = $adapter->encode(DataType::dateTime(), $time);
        $this->assertTrue(preg_match('/^"(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})"$/', $encoded, $matches) === 1);
        $this->assertEquals($time, $adapter->decode(DataType::dateTime(), $matches[1]));
        $this->assertSame('foo bar baz', $adapter->decode(DataType::string(), 'foo bar baz'));
        $this->assertSame([], $adapter->decode(DataType::object(), '[]'));
    }
    
    public function testTableExists()
    {
        $db = $this->getDb();
        $adapter = new MysqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('query')
            ->with($this->equalTo('SHOW TABLES LIKE "foo_bar"'))
            ->willReturn($this->getResultSet([['foo_bar']]));
        
        $this->assertTrue($adapter->tableExists('FooBar'));
    }
    
    public function testGetTables()
    {
        $db = $this->getDb();
        $adapter = new MysqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('query')
            ->with($this->equalTo('SHOW TABLES'))
            ->willReturn($this->getResultSet([['foo_bar'], ['baz']]));
        
        $this->assertEquals(['FooBar', 'Baz'], $adapter->getTables());
    }
    
    public function testCreateTable()
    {
        $db = $this->getDb();
        $adapter = new MysqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('CREATE TABLE `foo_bar` ('
                . 'id INT UNSIGNED NOT NULL AUTO_INCREMENT, '
                . 'foo VARCHAR(255) NOT NULL DEFAULT "bar", '
                . 'PRIMARY KEY (id), '
                . 'INDEX (foo, id), '
                . 'UNIQUE (foo)'
                . ') CHARACTER SET utf8'));
        
        $def = new \Jivoo\Data\DefinitionBuilder();
        $def->addAutoIncrementId();
        $def->foo = DataType::string(255, false, "bar");
        $def->addKey(['foo', 'id']);
        $def->addUnique('foo');
        
        $adapter->createTable('FooBar', $def);
    }
    
    public function testRenameTable()
    {
        $db = $this->getDb();
        $adapter = new MysqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('RENAME TABLE `foo_bar` TO `baz_bar`'));
        
        $adapter->renameTable('FooBar', 'BazBar');
    }
    
    public function testDropTable()
    {
        $db = $this->getDb();
        $adapter = new MysqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('DROP TABLE `foo_bar`'));
        
        $adapter->dropTable('FooBar');
    }
    
    public function testAddColumn()
    {
        $db = $this->getDb();
        $adapter = new MysqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('ALTER TABLE `foo_bar` ADD baz TEXT NOT NULL'));
        
        $adapter->addColumn('FooBar', 'baz', DataType::text());
    }
    
    public function testDeleteColumn()
    {
        $db = $this->getDb();
        $adapter = new MysqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('ALTER TABLE `foo_bar` DROP baz'));
        
        $adapter->deleteColumn('FooBar', 'baz');
    }
    
    public function testAlterColumn()
    {
        $db = $this->getDb();
        $adapter = new MysqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('ALTER TABLE `foo_bar` CHANGE baz baz TEXT NOT NULL'));
        
        $adapter->alterColumn('FooBar', 'baz', DataType::text());
    }
    
    public function testRenameColumn()
    {
        $db = $this->getDb();
        $adapter = new MysqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('ALTER TABLE `foo` CHANGE b d VARCHAR(255) NOT NULL'));
        
        $adapter->renameColumn('Foo', 'b', 'd');
    }
    
    public function testCreateIndex()
    {
        $db = $this->getDb();
        $adapter = new MysqlTypeAdapter($db);
        
        $db->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('ALTER TABLE `foo_bar` ADD PRIMARY KEY (id)')],
                [$this->equalTo('ALTER TABLE `foo_bar` ADD UNIQUE foo (foo)')],
                [$this->equalTo('ALTER TABLE `foo_bar` ADD INDEX id_foo (id, foo)')]
            );
        
        $adapter->createIndex('FooBar', 'PRIMARY', ['id']);
        $adapter->createIndex('FooBar', 'foo', ['foo']);
        $adapter->createIndex('FooBar', 'id_foo', ['id', 'foo'], false);
    }
    
    public function testDeleteIndex()
    {
        $db = $this->getDb();
        $adapter = new MysqlTypeAdapter($db);
        
        $db->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('ALTER TABLE `foo_bar` DROP PRIMARY KEY')],
                [$this->equalTo('ALTER TABLE `foo_bar` DROP INDEX foo')]
            );
        
        $adapter->deleteIndex('FooBar', 'PRIMARY');
        $adapter->deleteIndex('FooBar', 'foo');
    }
    
    public function testAlterIndex()
    {
        $db = $this->getDb();
        $adapter = new MysqlTypeAdapter($db);
        
        $db->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('ALTER TABLE `foo_bar` DROP PRIMARY KEY, ADD PRIMARY KEY (id)')],
                [$this->equalTo('ALTER TABLE `foo_bar` DROP INDEX foo, ADD UNIQUE foo (foo)')],
                [$this->equalTo('ALTER TABLE `foo_bar` DROP INDEX id_foo, ADD INDEX id_foo (id, foo)')]
            );
        
        $adapter->alterIndex('FooBar', 'PRIMARY', ['id']);
        $adapter->alterIndex('FooBar', 'foo', ['foo']);
        $adapter->alterIndex('FooBar', 'id_foo', ['id', 'foo'], false);
    }
}
