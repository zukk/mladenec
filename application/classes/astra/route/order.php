<?php
class Astra_Route_Order {
    /**
     *
     * @var string
     */
    private $id;
    
    /**
     * @var int
     */
    protected $start;
    
    /**
     * @var int
     */
    protected $finish;
    
    /**
     * @var string
     */
    private $comment;
    
    /**
     * @var string
     */
    private $operation;
    
    public function save($route_id, $resource_id, $number, $arrival,  $departure) {
        $astra_order = new Model_Astra_Order($this->id);
                        
        if ($astra_order->loaded()) {
            echo('Order #' . $this->id . " loaded\n");

            $astra_order->route_id     = $route_id;
            $astra_order->resource_id  = $resource_id;
            $astra_order->route_number = $number;
            $astra_order->arrival      = Txt::milliseconds_to_time($this->start);
            $astra_order->departure    = Txt::milliseconds_to_time($this->finish);

            $astra_order->save();
            echo('Order #' . $astra_order->pk() . " saved\n");
        } else {
            echo('Order #' . $astra_order->pk() . " NOT loaded\n");
        }
    }
}