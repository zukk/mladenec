<?php
/**
 * Description of response
 */
class Astra_Response {
    /**
     * @var Astra_Result
     */
    protected $return;
    
    public function get_result() {
        return $this->return;
    }
}