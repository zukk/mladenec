<?php
class Pager_Test extends PHPUnit_Framework_TestCase
{
    public function factoryProvider()
    {
        return array(
          array(1, NULL, array(), 0, 1),
          array(9, 1, array('asd'=>111), 0, 1),
          array(500, 3, array('asd'=>111), 60, 250),
        );
    }
    
    /**
     * @dataProvider factoryProvider
     */
    public function test_factory($total, $page, $query_params, $expected_offset, $expected_pages)
    {
        /*
        Request::current()->query(Pager::PARAM, $page);
        
        foreach($query_params as $qp_name => $qp_val)
        {
            Request::current()->query($qp_name, $qp_val);
        }
        
        $pager = Pager::factory($total);
        
        $this->assertInstanceOf('Pager', $pager);
        
        $this->assertEquals($expected_offset, $pager->offset);
        
        $this->assertEquals($expected_pages, $pager->pages);
        
        return $pager;
         * 
         */
    }
}


