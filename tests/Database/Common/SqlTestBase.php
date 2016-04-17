<?php
namespace Jivoo\Data\Database\Common;

use Jivoo\Data\Database\DatabaseDefinitionBuilder;
use Jivoo\Data\DataType;
use Jivoo\Data\DefinitionBuilder;
use Jivoo\TestCase;

abstract class SqlTestBase extends TestCase
{
    
    protected function getDb()
    {
        $def = new DatabaseDefinitionBuilder();
        $tableDef = new DefinitionBuilder();
        $tableDef->a = DataType::string();
        $tableDef->b = DataType::string();
        $tableDef->c = DataType::string();
        $def->addDefinition('Foo', $tableDef);
        
        $typeAdapter = $this->getMockBuilder('Jivoo\Data\Database\TypeAdapter')
            ->getMock();
        $typeAdapter->method('encode')
            ->willReturnCallback(function ($type, $value) {
                return '"' . $value . '"';
            });
        
        
        $db = $this->getMockBuilder('Jivoo\Data\Database\Common\SqlDatabase')
            ->getMock();
        $db->method('getTypeAdapter')
            ->willReturn($typeAdapter);
        $db->method('getDefinition')
            ->willReturn($def);
        $db->method('sqlLimitOffset')
            ->willReturnCallback(function ($limit, $offset) {
                if (isset($offset)) {
                    return 'LIMIT ' . $limit . ' OFFSET ' . $offset;
                }
                return 'LIMIT ' . $limit;
            });
        $db->method('tableName')
            ->willReturnCallback(function ($table) {
                return \Jivoo\Utilities::camelCaseToUnderscores($table);
            });
        $db->method('quoteModel')
            ->willReturnCallback(function ($model) {
                return '{' . $model . '}';
            });
        $db->method('quoteField')
            ->willReturnCallback(function ($field) {
                return $field;
            });
        $db->method('quoteLiteral')
            ->willReturnCallback(function ($type, $value) {
                return '"' . $value . '"';
            });
        $db->method('quoteString')
            ->willReturnCallback(function ($value) {
                return '"' . $value . '"';
            });
        return $db;
    }
    
    protected function getResultSet(array $rows)
    {
        $set = $this->getMockBuilder('Jivoo\Data\Database\ResultSet')->getMock();
        $set->method('hasRows')
            ->willReturnCallback(function () use (&$rows) {
                return count($rows) > 0;
            });
        $set->method('fetchRow')
            ->willReturnCallback(function () use (&$rows) {
                if (count($rows)) {
                    return array_values(array_shift($rows));
                }
                return false;
            });
        $set->method('fetchAssoc')
            ->willReturnCallback(function () use (&$rows) {
                if (count($rows)) {
                    return array_shift($rows);
                }
                return false;
            });
        return $set;
    }
}
