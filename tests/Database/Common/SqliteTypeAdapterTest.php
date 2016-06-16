<?php

namespace Jivoo\Data\Database\Common;

use Jivoo\Data\DataType;

class SqliteTypeAdapterTest extends SqlTestBase
{

    public function testEncode()
    {
        $db = $this->getDb();
        $adapter = new SqliteTypeAdapter($db);

        $this->assertSame('NULL', $adapter->encode(DataType::integer(), null));
        $this->assertSame(15, $adapter->encode(DataType::integer(), '15'));
        $this->assertSame(33.0, $adapter->encode(DataType::float(), 33));
        $this->assertSame(1, $adapter->encode(DataType::boolean(), true));
        $this->assertSame(0, $adapter->encode(DataType::boolean(), false));
        $this->assertSame(4242, $adapter->encode(DataType::date(), 4242));
        $this->assertSame(4242, $adapter->encode(DataType::dateTime(), 4242));
        $this->assertSame('"foo bar baz"', $adapter->encode(DataType::string(), 'foo bar baz'));
        $this->assertSame('"[]"', $adapter->encode(DataType::object(), []));
    }
    
    public function testDecode()
    {
        $db = $this->getDb();
        $adapter = new SqliteTypeAdapter($db);
        
        $this->assertNull($adapter->decode(DataType::integer(), null));
        $this->assertSame(15, $adapter->decode(DataType::integer(), '15'));
        $this->assertSame(33.0, $adapter->decode(DataType::float(), 33));
        $this->assertSame(true, $adapter->decode(DataType::boolean(), 1));
        $this->assertSame(false, $adapter->decode(DataType::boolean(), 0));
        $this->assertSame(4242, $adapter->decode(DataType::date(), 4242));
        $this->assertSame(4242, $adapter->decode(DataType::dateTime(), 4242));
        $this->assertSame('foo bar baz', $adapter->decode(DataType::string(), 'foo bar baz'));
        $this->assertSame([], $adapter->decode(DataType::object(), '[]'));
    }
    
    public function testGetDefinition()
    {
        $db = $this->getDb();
        $adapter = new SqliteTypeAdapter($db);
        
        $db->expects($this->exactly(3))
            ->method('query')
            ->withConsecutive(
                [$this->equalTo('PRAGMA table_info("foo_bar")')],
                [$this->equalTo('PRAGMA index_list("foo_bar")')],
                [$this->equalTo('PRAGMA index_info("foo_bar_foo_id")')]
            )->willReturnCallback(function ($query) {
                if (strpos($query, 'table_info') !== false) {
                    return $this->getResultSet([
                        [
                            'name' => 'id',
                            'type' => 'integer',
                            'pk' => '1',
                            'dflt_value' => '',
                            'notnull' => '1'
                        ],
                        [
                            'name' => 'foo',
                            'type' => 'text',
                            'pk' => '0',
                            'dflt_value' => 'baz',
                            'notnull' => '1'
                        ]
                    ]);
                } elseif (strpos($query, 'index_list') !== false) {
                    return $this->getResultSet([
                        [
                            'name' => 'foo_bar_foo_id',
                            'unique' => '0'
                        ]
                    ]);
                } else {
                    return $this->getResultSet([
                        ['name' => 'id'],
                        ['name' => 'foo']
                    ]);
                }
            });
            
        $def = $adapter->getDefinition('FooBar');
        $this->assertEquals(['id', 'foo'], $def->getFields());
        $this->assertTrue($def->getType('id')->isInteger());
        $this->assertFalse($def->getType('id')->null);
        $this->assertTrue($def->getType('foo')->isText());
        $this->assertEquals('baz', $def->getType('foo')->default);
        $this->assertFalse($def->getType('foo')->null);
        
        $this->assertEquals(['PRIMARY', 'foo_id'], $def->getKeys());
        $this->assertEquals(['id'], $def->getPrimaryKey());
        $this->assertEquals(['id', 'foo'], $def->getKey('foo_id'));
        $this->assertFalse($def->isUnique('foo_id'));
    }
    
    public function testTableExists()
    {
        $db = $this->getDb();
        $adapter = new SqliteTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('query')
            ->with($this->equalTo('PRAGMA table_info("foo_bar")'))
            ->willReturn($this->getResultSet([['foo_bar']]));
        
        $this->assertTrue($adapter->tableExists('FooBar'));
    }
    
    public function testGetTables()
    {
        $db = $this->getDb();
        $adapter = new SqliteTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('query')
            ->with($this->equalTo('SELECT name FROM sqlite_master WHERE type = "table"'))
            ->willReturn($this->getResultSet([['foo_bar'], ['baz']]));
        
        $this->assertEquals(['FooBar', 'Baz'], $adapter->getTables());
    }
    
    public function testCreateTable()
    {
        $db = $this->getDb();
        $adapter = new SqliteTypeAdapter($db);
        
        $db->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('CREATE TABLE "foo_bar" ('
                    . 'id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, '
                    . 'foo TEXT(255) NOT NULL DEFAULT "bar")')],
                [$this->equalTo('CREATE INDEX "foo_bar_foo_id" ON "foo_bar" (foo, id)')],
                [$this->equalTo('CREATE UNIQUE INDEX "foo_bar_foo" ON "foo_bar" (foo)')]
            );
        
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
        $adapter = new SqliteTypeAdapter($db);
        
        $db->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('CREATE TABLE "bar" ('
                    . 'a TEXT(255) NOT NULL, '
                    . 'b TEXT(255) NOT NULL, '
                    . 'c TEXT(255) NOT NULL, '
                    . 'PRIMARY KEY ())')],
                [$this->equalTo('INSERT INTO {Bar} SELECT * FROM {Foo}')],
                [$this->equalTo('DROP TABLE "foo"')]
            );
        
        $adapter->renameTable('Foo', 'Bar');
    }
    
    public function testDropTable()
    {
        $db = $this->getDb();
        $adapter = new SqliteTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('DROP TABLE "foo_bar"'));
        
        $adapter->dropTable('FooBar');
    }
    
    public function testAddColumn()
    {
        $db = $this->getDb();
        $adapter = new SqliteTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('ALTER TABLE "foo_bar" ADD baz TEXT NOT NULL'));
        
        $adapter->addColumn('FooBar', 'baz', DataType::text());
    }
    
    public function testDeleteColumn()
    {
        $db = $this->getDb();
        $adapter = new SqliteTypeAdapter($db);
        
        $db->expects($this->exactly(6))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('CREATE TABLE "foo__migration_backup" ('
                    . 'a TEXT(255) NOT NULL, '
                    . 'b TEXT(255) NOT NULL, '
                    . 'c TEXT(255) NOT NULL, '
                    . 'PRIMARY KEY ())')],
                [$this->equalTo('INSERT INTO {Foo_MigrationBackup} SELECT * FROM {Foo}')],
                [$this->equalTo('DROP TABLE "foo"')],
                [$this->equalTo('CREATE TABLE "foo" ('
                    . 'b TEXT(255) NOT NULL, '
                    . 'c TEXT(255) NOT NULL, '
                    . 'PRIMARY KEY ())')],
                [$this->equalTo('INSERT INTO {Foo} SELECT b, c FROM {Foo_MigrationBackup}')],
                [$this->equalTo('DROP TABLE "foo__migration_backup"')]
            );
        
        $adapter->deleteColumn('Foo', 'a');
    }
    
    public function testAlterColumn()
    {
        $db = $this->getDb();
        $adapter = new SqliteTypeAdapter($db);
        
        $db->expects($this->exactly(6))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('CREATE TABLE "foo__migration_backup" ('
                    . 'a TEXT(255) NOT NULL, '
                    . 'b TEXT(255) NOT NULL, '
                    . 'c TEXT(255) NOT NULL, '
                    . 'PRIMARY KEY ())')],
                [$this->equalTo('INSERT INTO {Foo_MigrationBackup} SELECT * FROM {Foo}')],
                [$this->equalTo('DROP TABLE "foo"')],
                [$this->equalTo('CREATE TABLE "foo" ('
                    . 'a INTEGER NOT NULL, '
                    . 'b TEXT(255) NOT NULL, '
                    . 'c TEXT(255) NOT NULL, '
                    . 'PRIMARY KEY ())')],
                [$this->equalTo('INSERT INTO {Foo} SELECT * FROM {Foo_MigrationBackup}')],
                [$this->equalTo('DROP TABLE "foo__migration_backup"')]
            );
        
        $adapter->alterColumn('Foo', 'a', DataType::integer());
    }
    
    public function testRenameColumn()
    {
        $db = $this->getDb();
        $adapter = new SqliteTypeAdapter($db);
        
        $db->expects($this->exactly(6))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('CREATE TABLE "foo__migration_backup" ('
                    . 'a TEXT(255) NOT NULL, '
                    . 'b TEXT(255) NOT NULL, '
                    . 'c TEXT(255) NOT NULL, '
                    . 'PRIMARY KEY ())')],
                [$this->equalTo('INSERT INTO {Foo_MigrationBackup} SELECT * FROM {Foo}')],
                [$this->equalTo('DROP TABLE "foo"')],
                [$this->equalTo('CREATE TABLE "foo" ('
                    . 'a TEXT(255) NOT NULL, '
                    . 'c TEXT(255) NOT NULL, '
                    . 'd TEXT(255) NOT NULL, '
                    . 'PRIMARY KEY ())')],
                [$this->equalTo('INSERT INTO {Foo} SELECT a, c, b FROM {Foo_MigrationBackup}')],
                [$this->equalTo('DROP TABLE "foo__migration_backup"')]
            );
        
        $adapter->renameColumn('Foo', 'b', 'd');
    }
    
    public function testCreateKey()
    {
        $db = $this->getDb();
        $adapter = new SqliteTypeAdapter($db);
        
        $db->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('CREATE UNIQUE INDEX "foo_bar_PRIMARY" ON "foo_bar" (id)')],
                [$this->equalTo('CREATE UNIQUE INDEX "foo_bar_foo" ON "foo_bar" (foo)')],
                [$this->equalTo('CREATE INDEX "foo_bar_id_foo" ON "foo_bar" (id, foo)')]
            );
        
        $adapter->createKey('FooBar', 'PRIMARY', ['id']);
        $adapter->createKey('FooBar', 'foo', ['foo']);
        $adapter->createKey('FooBar', 'id_foo', ['id', 'foo'], false);
    }
    
    public function testDeleteKey()
    {
        $db = $this->getDb();
        $adapter = new SqliteTypeAdapter($db);
        
        $db->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('DROP INDEX "foo_bar_PRIMARY"')],
                [$this->equalTo('DROP INDEX "foo_bar_foo"')]
            );
        
        $adapter->deleteKey('FooBar', 'PRIMARY');
        $adapter->deleteKey('FooBar', 'foo');
    }
    
    public function testAlterKey()
    {
        $db = $this->getDb();
        $adapter = new SqliteTypeAdapter($db);
        
        $db->expects($this->exactly(6))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('DROP INDEX "foo_bar_PRIMARY"')],
                [$this->equalTo('CREATE UNIQUE INDEX "foo_bar_PRIMARY" ON "foo_bar" (id)')],
                [$this->equalTo('DROP INDEX "foo_bar_foo"')],
                [$this->equalTo('CREATE UNIQUE INDEX "foo_bar_foo" ON "foo_bar" (foo)')],
                [$this->equalTo('DROP INDEX "foo_bar_id_foo"')],
                [$this->equalTo('CREATE INDEX "foo_bar_id_foo" ON "foo_bar" (id, foo)')]
            );
        
        $adapter->alterKey('FooBar', 'PRIMARY', ['id']);
        $adapter->alterKey('FooBar', 'foo', ['foo']);
        $adapter->alterKey('FooBar', 'id_foo', ['id', 'foo'], false);
    }
}
