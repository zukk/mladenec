<?php
/**
 *  addGarages, addGaragesExt, addGarage, deleteGarage, getGarage, deleteAllGarages
 */
class Astra_Generic extends Astra_Client {
    
    /**
     * Блокирует UI в Астре
     * @return Astra_Client
     */
    public function block_client()
    {
        return $this->c('blockClient');
    }
    /**
     * Разблокирует UI в Астре
     * @return Astra_Client
     */
    public function release_client()
    {   
        return $this->c('releaseClient');
    }
    
    public function __construct() {
        parent::__construct('generic');
    }
}
