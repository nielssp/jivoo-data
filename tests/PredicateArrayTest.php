<?php

namespace Jivoo\Data;

class PredicateArayTest extends \Jivoo\TestCase {

  public function testExists() {
    $a = new PredicateArray(array(1, 2, 3, 4, 5), function($x) { return $x > 3; });
    $this->assertFalse(isset($a[0]));
    $this->assertFalse(isset($a[1]));
    $this->assertFalse(isset($a[2]));
    $this->assertTrue(isset($a[3]));
    $this->assertTrue(isset($a[4]));
    $this->assertFalse(isset($a[5]));
    $this->assertTrue(isset($a[4]));
  }

  public function testGetAndSet() {
    $a = new PredicateArray(array(1, 2, 3, 4, 5), function($x) { return $x > 3; });
    $this->assertNull($a[0]);
    $this->assertEquals(4, $a[3]);
    $a[0] = 'foo';
    $this->assertEquals('foo', $a[0]);
    $this->assertTrue(isset($a[0]));
    $this->assertFalse(isset($a[1]));
  }

  public function testUnset() {
    $a = new PredicateArray(array(1, 2, 3, 4, 5), function($x) { return $x > 3; });
    $this->assertFalse(isset($a[0]));
    unset($a[0]);
    $this->assertFalse(isset($a[0]));
    $this->assertTrue(isset($a[3]));
    unset($a[3]);
    $this->assertFalse(isset($a[3]));
  }
}
