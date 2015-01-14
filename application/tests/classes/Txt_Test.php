<?php
/**
 * Tests for Txt class
 *
 * @author PKS
 */
class TxtTest extends Unittest_TestCase
{
    /**
     * @test
     * @covers Txt::milliseconds_to_time
     */
    public function test_milliseconds_to_time()
    {
        $this->assertEquals('01:00:00', Txt::milliseconds_to_time(3600000));
        $this->assertEquals('00:01:00', Txt::milliseconds_to_time(60000));
        $this->assertEquals('00:00:01', Txt::milliseconds_to_time(1000));
        $this->assertEquals('00:00:00', Txt::milliseconds_to_time(100));
    }
    
    /**
     * @test
     * @covers Txt::time_to_seconds
     */
    public function test_time_to_seconds()
    {
        $this->assertEquals('3600',     Txt::time_to_seconds('01:00:00'));
        $this->assertEquals('60',       Txt::time_to_seconds('00:01:00'));
        $this->assertEquals('1',        Txt::time_to_seconds('00:00:01'));
        $this->assertEquals('60',       Txt::time_to_seconds('00:00:00',    '00:01:00'));
        $this->assertEquals('3600',     Txt::time_to_seconds(0,             '01:00:00'));
        $this->assertEquals('3600',     Txt::time_to_seconds('',            '01:00:00'));
        $this->assertEquals('3600',     Txt::time_to_seconds(null,          '01:00:00'));
    }
    /**
     * @test
     * @covers Txt::is_html_text_filled
     */
    public function test_is_html_text_filled()
    {
        $this->assertEquals(TRUE,   Txt::is_html_text_filled('Something', 4));
        $this->assertEquals(TRUE,   Txt::is_html_text_filled('<p>Something</p>  ', 4));
        $this->assertEquals(FALSE,  Txt::is_html_text_filled('  Som', 4));
        $this->assertEquals(FALSE,  Txt::is_html_text_filled('<p>  <b>Som</b>  </p>', 4));
        $this->assertEquals(FALSE,  Txt::is_html_text_filled('<p><b>Som</b></p>', 4));
        $this->assertEquals(TRUE,   Txt::is_html_text_filled('<p><b>Something is written here</b></p>'));
        $this->assertEquals(FALSE,  Txt::is_html_text_filled('<p><b></b></p>'));
    }
    
    /**
     * @test
     * @covers Txt::parse_explode
     */
    public function test_parse_explode()
    {
        $this->assertEquals(array('aaa','bbb','ccc'),   Txt::parse_explode('©','aaa©bbb©ccc', 3));
        
        // Как с кириллицей?
        $this->assertEquals(array('ффф','ццц','ыыы'),   Txt::parse_explode('©','ффф©ццц©ыыы', 3));
        
        // Как с экранированием?
        $this->assertEquals(array('ффф','ц©цц','ыыы'),   Txt::parse_explode('©','ффф©ц\©цц©ыыы', 3));
    }
    
    /**
     * @test
     * @covers Txt::parse_explode
     */
    public function test_parse_explode2()
    {
        try
        {
            // Неправильное количество полей - ловим эксепшен
            Txt::parse_explode('©','aaa©bbb©ccc', 4);
        }
        catch (Txt_Exception $expected)
        {
            return;
        }

        $this->fail('An Txt_Exception has not been raised.');
    }
    
    /**
     * @test
     * @covers Txt::link_params()
     */
    public function test_link_params()
    {
        $this->assertEquals(
                array(
                    'path' => 'catalog/pyure',
                    'b'=> array('51480'),
                    'f' => array('2072'=>array('17840')),
                    's'=>'rating'
                    ),
                Txt::link_params('catalog/pyure#!b=51480;f2072=17840;s=rating;')
                );
        
        $this->assertEquals(
                array(
                    'path' => 'catalog/pyure',
                    'b'=>   array('51480'),
                    'f' =>  array('2072'=>array('17840')),
                    's'=>'rating'
                    ),
                Txt::link_params('catalog/pyure#!b=51480;f2072=17840;s=rating')
                );
        
        $this->assertEquals(
                array(
                    'path' => 'catalog/kashi',
                    'utm_source'=>'yandex',
                    'utm_campaign'=>'detskoe_pitanie|kashi',
                    'b'=> array('51554'),
                    'f' => array('2059'=>array('17840','17841')),
                    's'=>'rating'
                    ),
                Txt::link_params('catalog/kashi?utm_source=yandex&utm_campaign=detskoe_pitanie|kashi#!b=51554;f2059=17840_17841;s=rating;')
                );
        
    }
}
