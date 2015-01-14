<?php
/**
 * Description of cart_test
 *
 * @author mit08
 */
    /**
class Cart_Test extends PHPUnit_Extensions_Database_TestCase {
    // ID остановленных на время тестов акций
    protected $stopped_actions = array();
    
    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO( $GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
        }

        return $this->conn;
    }
    
    public function getDataSet()
    {
        return $this->createXMLDataSet('cart_dataset.xml');
    }
    
    public function test_instance()
    {
        $cart = Cart::instance();
        $this->assertInstanceOf('Cart', $cart);
        
        return $cart;
    }
    
     * Простой тест, кладет в корзину несколько товаров,
     * проверяет расчет qty и total
     * чистит корзину, и проверяет что почистилось
     * 
     * @param Cart $cart Корзина
     * @depends test_instance
    
    public function test_add($cart)
    {
        // Stopping actions
        $this->stop_actions();
        
        $goods_tmp = DB::select('id','qty')
                ->from('z_good')
                ->where('show','=',1)
                ->where('qty','>',0)
                ->limit(rand(1,10))
                ->execute()
                ->as_array('id','qty');
        
        $goods = array();
        $target_qty = 0;
        
        $comment_prefix = 'Комментарий к товару ';
        $comments = array();
        
        foreach($goods_tmp as $gid=>$gqty)
        {
            $tq = rand(1,$gqty);
            $goods[$gid] = $tq;
            $target_qty += $tq;
            
            if (count($comments) < 3) $comments[$gid] = $comment_prefix . $gid;
        }
        // Добавляем
        $this->assertInstanceOf('Cart', $cart->add($goods));
        
        // Подсчитаем количество
        $this->assertEquals($target_qty, $cart->get_qty());
        
        // Проставилась ли сумма
        $this->assertTrue($cart->get_total() > 0);
        
        // Чистим корзину
        $this->assertInstanceOf('Cart', $cart->clean());
        
        $this->assertEquals(0, $cart->get_qty());
        $this->assertEquals(0, $cart->get_total());
        
        // Running actions back again
        $this->run_actions_back();
        
        return $cart;
    }
    
    
    
    protected function stop_actions()
    {
        $this->stopped_actions = DB::select('id')
                ->from('z_action')
                ->where('active','=',1)
                ->where('type','>',0)
                ->execute()
                ->as_array('id','id');
        if ( ! empty($this->stopped_actions))
        {
            DB::update('z_action')
                ->set(array('active'=>'0'))
                ->where('id','IN',$this->stopped_actions)
                ->execute();
        }
    }
    
    protected function run_actions_back()
    {
        if ( ! empty($this->stopped_actions))
        {
            DB::update('z_action')
                ->set(array('active'=>'1'))
                ->where('id','IN',$this->stopped_actions)
                ->execute();
        }
    }
}

     */