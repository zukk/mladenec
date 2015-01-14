<?php
/**
 * Результат SOAP запроса к Астре
 */
class Astra_Result {
    /**
     * @var int
     */
    protected $code;
    /**
     * @var Astra_Error
     */
    protected $errorList;
    
    /**
     * @return int
     */
    public function get_code() {
        return $this->code;
    }
    
    /**
     * @return Astra_Error
     */
    public function get_errors() {
        return $this->errorList;
    }
}