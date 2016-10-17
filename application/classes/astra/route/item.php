<?php
/**
 * Результат SOAP запроса к Астре
 */
class Astra_Route_Item {
    /**
     * @var string
     */
    private $pointId;
    /**
     * @var int
     */
    protected $secNo;
    /**
     * @var int
     */
    protected $arrival;
    /**
     * @var int
     */
    protected $departure;
    /**
     * @var float
     */
    protected $weight;
    /**
     * @var float
     */
    protected $volume;
    /**
     * @var float
     */
    protected $volumeA;
    /**
     * Расстояние от предыдущего пункта
     * 
     * @var int
     */
    protected $prevDistance;
    
    /**
     * @var Astra_Route_Order
     */
    protected $orderList;
    
    public function save($route_id, $resource_id) {
        
        if (empty($this->orderList)) {
            echo("empty orderList\n");
            return;
        }
        
        if(is_array($this->orderList)) {
            echo("orderList is array\n");
            foreach($this->orderList as $o) {
                if ( ! ($o instanceof Astra_Route_Order)) {
                    echo("NOT Astra_Route_Order\n");
                    continue;
                }
                $o->save($route_id, $resource_id, $this->secNo, $this->arrival,  $this->departure);
            }
        } elseif($this->orderList instanceof Astra_Route_Order) {
            echo("orderList is NOT array\n");
            if ( ! ($this->orderList instanceof Astra_Route_Order)) {
                echo("NOT Astra_Route_Order\n");
                return;
            }
            $this->orderList->save($route_id, $resource_id, $this->secNo, $this->arrival,  $this->departure);
        } else {
            echo("Strange orderList: " . gettype($this->orderList) . "\n");
        }
                 
    }
    
}