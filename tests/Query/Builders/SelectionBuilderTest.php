<?php
namespace Jivoo\Data\Query\Builders;

class SelectionBuilderTest extends SelectionBaseTest
{
    
    protected function getInstance()
    {
        return new SelectionBuilder($this->dataSource);
    }
    
    public function testGetters()
    {
        $selection = $this->getInstance();
        $this->assertEquals([], $selection->getAdditionalFields());
        $this->assertEquals(null, $selection->getAlias());
        $this->assertEquals(null, $selection->getGroupPredicate());
        $this->assertEquals([], $selection->getGrouping());
        $this->assertEquals([], $selection->getJoins());
        $this->assertEquals(0, $selection->getOffset());
        $this->assertEquals([], $selection->getProjection());
        $this->assertEquals(false, $selection->isDistinct());
        $this->assertEquals([], $selection->getData());
    }
    
    public function testUpdatableAndDeletable()
    {
        $selection = $this->getInstance();
        
        $this->assertEquals(['foo' => 'bar'], $selection->set('foo', 'bar')->getData());
        
        $this->dataSource
            ->expects($this->once())
            ->method('updateSelection')
            ->with($this->isInstanceOf('Jivoo\Data\Query\UpdateSelection'))
            ->willReturn(3);
        
        $this->assertEquals(3, $selection->update());
        
        $this->dataSource
            ->expects($this->once())
            ->method('deleteSelection')
            ->with($this->isInstanceOf('Jivoo\Data\Query\Selection'))
            ->willReturn(3);
        
        $this->assertEquals(3, $selection->delete());
    }
}
