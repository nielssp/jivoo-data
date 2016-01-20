<?php

namespace Jivoo\Data;

class DataTypeTest extends \Jivoo\TestCase {

  protected function _before() {}

  protected function _after() {}

  public function testDetectType() {
    $this->assertTrue(DataType::detectType(true)->isBoolean());
    $this->assertTrue(DataType::detectType(false)->isBoolean());
    $this->assertTrue(DataType::detectType(132)->isInteger());
    $this->assertTrue(DataType::detectType(132.2)->isFloat());
    $this->assertTrue(DataType::detectType(array(1))->isObject());
    $this->assertTrue(DataType::detectType((object)array('a' => 2))->isObject());
    $this->assertTrue(DataType::detectType('foo')->isText());
  }

  public function testToString() {
    $this->assertEquals('Signed Integer', DataType::integer());
    $this->assertEquals('Unsigned Integer', DataType::integer(DataType::UNSIGNED));
    $this->assertEquals('Unsigned Big Integer', DataType::integer(DataType::UNSIGNED | DataType::BIG));
    $this->assertEquals('Serial Signed Tiny Integer', DataType::integer(DataType::SERIAL | DataType::TINY));
    $this->assertEquals('Signed Small Integer', DataType::integer(DataType::SMALL));
    $this->assertEquals('String(0)', DataType::string(0));
    $this->assertEquals('String(100)', DataType::string(100));
    $this->assertEquals('Text', DataType::text());
    $this->assertEquals('Boolean', DataType::boolean());
    $this->assertEquals('Float', DataType::float());
    $this->assertEquals('Date', DataType::date());
    $this->assertEquals('Date/Time', DataType::dateTime());
    $this->assertEquals('Object', DataType::object());
  }

  public function testIsValid() {
    $this->assertTrue(DataType::text(true)->isValid(null));
    $this->assertFalse(DataType::text(false)->isValid(null));

    $this->assertFalse(DataType::integer()->isValid('2'));
    $this->assertFalse(DataType::integer()->isValid(2147483648));
    $this->assertTrue(DataType::integer()->isValid(2147483647));
    $this->assertTrue(DataType::integer()->isValid(-2147483648));

    $this->assertTrue(DataType::string(5)->isValid('test1'));
    $this->assertFalse(DataType::string(5)->isValid('test12'));
  }
}
