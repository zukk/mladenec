<?php
/**
 *  
 */
class Astra_Planning extends Astra_Client {
    
    /**
     * Распланирует заказ, т.е уберет его из плана
     * @return Astra_Client
     */
    public function unplan_order($order_id) {
        
        return $this->c('unplanOrder', array('orderId'=>$order_id,'optimizeAfter'=>true));
    }
    
    public function __construct() {
        parent::__construct('planning');
    }
}