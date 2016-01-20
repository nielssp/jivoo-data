<?php

namespace Jivoo\Data\Query;

class ETest extends \Jivoo\TestCase {

  public function testE() {
    $expr = E::e('test = %i', 5);
    $this->assertInstanceOf('Jivoo\Data\Query\Expression', $expr);
  }
  
  public function testEscapeLike() {
    $this->assertEquals('\%\_', E::escapeLike('%_'));
  }
  
  public function testInterpolate() {
    // TODO
  }
  
}
