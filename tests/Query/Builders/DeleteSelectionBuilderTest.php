<?php
namespace Jivoo\Data\Query\Builders;

class DeleteSelectionBuilderTest extends SelectionBaseTest
{
    
    protected function getInstance()
    {
        return new DeleteSelectionBuilder($this->dataSource);
    }
    
    public function testDelete()
    {
        $delete = $this->getInstance();
        
        $this->dataSource
            ->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($delete))
            ->wilLReturn(3);
        
        $this->assertEquals(3, $delete->delete());
    }
}
