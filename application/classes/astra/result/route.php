<?php
/**
 * Результат SOAP запроса к Астре
 */
class Astra_Result_Route {
    protected $resourceId;
    protected $date;
    protected $start;
    protected $stop;
    protected $distance;
    /**
     *
     * @var Astra_Route_Item
     */
    protected $itemList;
    
    public function save() {
        
        if ($this->date instanceof Astra_Date) {
            $date = (string) $this->date;
        } else {
            $date = date('Y-m-d');
        }
        
        
        $route = new Model_Astra_Route();
        
        $route->resource_id   = intval($this->resourceId);
        $route->date          = $date;
        $route->start         = Txt::milliseconds_to_time($this->start);
        $route->finish        = Txt::milliseconds_to_time($this->stop);
        $route->distance      = $this->distance;
        $route->from_astra_ts = time();
        
        $route->save();
        
        echo('Route #' . $route->pk() . " saved\n");
        
        if ( ! empty($this->itemList) AND is_array($this->itemList)) {
            foreach($this->itemList as $item) {
                
                if ( ! ($item instanceof Astra_Route_Item)) {
                    echo("NOT Astra_Route_Item\n");
                    continue;
                }
                echo("Saving Astra_Route_Items:\n");
                $item->save($route->pk(), $this->resourceId);
                
            }
        } else {
            //var_dump($route->itemList);
            echo("items not array\n");
        }
        
        //echo($route->id);
    }
}