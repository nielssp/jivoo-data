<?php
namespace Jivoo\Data\Query\Builders;

class UpdateSelectionBuilderTest extends SelectionBaseTest
{
    
    protected function getInstance()
    {
        return new UpdateSelectionBuilder($this->dataSource);
    }
    
    public function testUpdate()
    {
        $update = $this->getInstance()->set('foo', 'bar');
        
        $this->assertEquals(['foo' => 'bar'], $update->getData());
        
        $update = $update->set(['foobar' => 5, 'baz' => 2]);
        
        $this->assertEquals(['foo' => 'bar', 'foobar' => 5, 'baz' => 2], $update->getData());
        
        $this->dataSource
            ->expects($this->once())
            ->method('update')
            ->with($this->equalTo($update))
            ->willReturn(3);
        
        $this->assertEquals(3, $update->update());
    }
}
