<?php

namespace Jivoo\Data\Database\Common;

use Jivoo\Data\DataType;

class PostgresqlTypeAdapterTest extends SqlTestBase
{

    public function testEncode()
    {
        $db = $this->getDb();
        $adapter = new PostgresqlTypeAdapter($db);

        $this->assertSame('NULL', $adapter->encode(DataType::integer(), null));
        $this->assertSame(15, $adapter->encode(DataType::integer(), '15'));
        $this->assertSame(33.0, $adapter->encode(DataType::float(), 33));
        $this->assertSame('TRUE', $adapter->encode(DataType::boolean(), true));
        $this->assertSame('FALSE', $adapter->encode(DataType::boolean(), false));
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
        $adapter = new PostgresqlTypeAdapter($db);
        
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
    
    public function testGetDefinition()
    {
        $db = $this->getDb();
        $adapter = new PostgresqlTypeAdapter($db);
        
        $db->expects($this->exactly(2))
            ->method('query')
            ->withConsecutive(
                [$this->equalTo("SELECT * FROM information_schema.columns WHERE table_name = 'foo_bar'")],
                [$this->equalTo("SELECT i.relname AS index_name, a.attname AS column_name, "
                    . "indisunique, indisprimary FROM pg_class t, pg_class i, pg_index ix, "
                    . "pg_attribute a WHERE t.oid = ix.indrelid AND i.oid = ix.indexrelid "
                    . "AND a.attrelid = t.oid AND a.attnum = ANY(ix.indkey) AND t.relkind = 'r' "
                    . "AND t.relname = 'foo_bar'")]
            )->willReturnCallback(function ($query) {
                if (strpos($query, 'information_schema') !== false) {
                    return $this->getResultSet([
                        [
                            'column_name' => 'id',
                            'data_type' => 'int',
                            'character_maximum_length' => '',
                            'column_default' => "nextval('foo_bar_id_seq')",
                            'is_nullable' => 'NO'
                        ],
                        [
                            'column_name' => 'foo',
                            'data_type' => 'character',
                            'character_maximum_length' => '42',
                            'column_default' => "'baz'::character",
                            'is_nullable' => 'NO'
                        ]
                    ]);
                } else {
                    return $this->getResultSet([
                        [
                            'index_name' => 'foo_bar_PRIMARY',
                            'column_name' => 'id',
                            'indisunique' => '1'
                        ],
                        [
                            'index_name' => 'foo_bar_foo_id',
                            'column_name' => 'id',
                            'indisunique' => '0'
                        ],
                        [
                            'index_name' => 'foo_bar_foo_id',
                            'column_name' => 'foo',
                            'indisunique' => '0'
                        ]
                    ]);
                }
            });
            
        $def = $adapter->getDefinition('FooBar');
        $this->assertEquals(['id', 'foo'], $def->getFields());
        $this->assertTrue($def->getType('id')->isInteger());
        $this->assertFalse($def->getType('id')->null);
        $this->assertTrue($def->getType('foo')->isString());
        $this->assertSame(42, $def->getType('foo')->length);
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
        $adapter = new PostgresqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('query')
            ->with($this->equalTo("SELECT 1 FROM pg_catalog.pg_tables WHERE "
                . "schemaname = 'public' AND tablename = 'foo_bar'"))
            ->willReturn($this->getResultSet([['foo_bar']]));
        
        $this->assertTrue($adapter->tableExists('FooBar'));
    }
    
    public function testGetTables()
    {
        $db = $this->getDb();
        $adapter = new PostgresqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('query')
            ->with($this->equalTo("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'"))
            ->willReturn($this->getResultSet([['foo_bar'], ['baz']]));
        
        $this->assertEquals(['FooBar', 'Baz'], $adapter->getTables());
    }
    
    public function testCreateTable()
    {
        $db = $this->getDb();
        $adapter = new PostgresqlTypeAdapter($db);
        
        $db->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('CREATE TABLE {FooBar} ('
                    . 'id serial NOT NULL, '
                    . 'foo varchar(255) NOT NULL DEFAULT "bar", '
                    . 'CONSTRAINT "foo_bar_PRIMARY" PRIMARY KEY (id))')],
                [$this->equalTo('CREATE INDEX "foo_bar_foo_id" ON {FooBar} (foo, id)')],
                [$this->equalTo('CREATE UNIQUE INDEX "foo_bar_foo" ON {FooBar} (foo)')]
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
        $adapter = new PostgresqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('ALTER TABLE {Foo} RENAME TO {Bar}'));
        
        $adapter->renameTable('Foo', 'Bar');
    }
    
    public function testDropTable()
    {
        $db = $this->getDb();
        $adapter = new PostgresqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('DROP TABLE {FooBar}'));
        
        $adapter->dropTable('FooBar');
    }
    
    public function testAddColumn()
    {
        $db = $this->getDb();
        $adapter = new PostgresqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('ALTER TABLE {FooBar} ADD baz text NOT NULL'));
        
        $adapter->addColumn('FooBar', 'baz', DataType::text());
    }
    
    public function testDeleteColumn()
    {
        $db = $this->getDb();
        $adapter = new PostgresqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('ALTER TABLE {Foo} DROP a'));
        
        $adapter->deleteColumn('Foo', 'a');
    }
    
    public function testAlterColumn()
    {
        $db = $this->getDb();
        $adapter = new PostgresqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('ALTER TABLE {Foo} ALTER a TYPE int NOT NULL'));
        
        $adapter->alterColumn('Foo', 'a', DataType::integer());
    }
    
    public function testRenameColumn()
    {
        $db = $this->getDb();
        $adapter = new PostgresqlTypeAdapter($db);
        
        $db->expects($this->once())
            ->method('execute')
            ->with($this->equalTo('ALTER TABLE {Foo} RENAME b TO d'));
        
        $adapter->renameColumn('Foo', 'b', 'd');
    }
    
    public function testCreateKey()
    {
        $db = $this->getDb();
        $adapter = new PostgresqlTypeAdapter($db);
        
        $db->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('ALTER TABLE {FooBar} ADD CONSTRAINT "foo_bar_PRIMARY" PRIMARY KEY (id)')],
                [$this->equalTo('CREATE UNIQUE INDEX "foo_bar_foo" ON {FooBar} (foo)')],
                [$this->equalTo('CREATE INDEX "foo_bar_id_foo" ON {FooBar} (id, foo)')]
            );
        
        $adapter->createKey('FooBar', 'PRIMARY', ['id']);
        $adapter->createKey('FooBar', 'foo', ['foo']);
        $adapter->createKey('FooBar', 'id_foo', ['id', 'foo'], false);
    }
    
    public function testDeleteKey()
    {
        $db = $this->getDb();
        $adapter = new PostgresqlTypeAdapter($db);
        
        $db->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('ALTER TABLE {FooBar} DROP CONSTRAINT "foo_bar_PRIMARY"')],
                [$this->equalTo('DROP INDEX "foo_bar_foo"')]
            );
        
        $adapter->deleteKey('FooBar', 'PRIMARY');
        $adapter->deleteKey('FooBar', 'foo');
    }
    
    public function testAlterKey()
    {
        $db = $this->getDb();
        $adapter = new PostgresqlTypeAdapter($db);
        
        $db->expects($this->exactly(6))
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo('ALTER TABLE {FooBar} DROP CONSTRAINT "foo_bar_PRIMARY"')],
                [$this->equalTo('ALTER TABLE {FooBar} ADD CONSTRAINT "foo_bar_PRIMARY" PRIMARY KEY (id)')],
                [$this->equalTo('DROP INDEX "foo_bar_foo"')],
                [$this->equalTo('CREATE UNIQUE INDEX "foo_bar_foo" ON {FooBar} (foo)')],
                [$this->equalTo('DROP INDEX "foo_bar_id_foo"')],
                [$this->equalTo('CREATE INDEX "foo_bar_id_foo" ON {FooBar} (id, foo)')]
            );
        
        $adapter->alterKey('FooBar', 'PRIMARY', ['id']);
        $adapter->alterKey('FooBar', 'foo', ['foo']);
        $adapter->alterKey('FooBar', 'id_foo', ['id', 'foo'], false);
    }
}
