<?php
class Model_Astra_Route extends ORM {
    
    /**
     *
     * @var Model_Astra_Route_Item
     */
    public $itmz = array();
    
    public function get_orders() {
        
    }
    
    protected $_table_name = 'z_astra_route';

    protected $_table_columns = array(
        'id'            => '', // * ID маршрута
        'resource_id'   => '', //   id ресурса (экипажа)
        'date'          => '', // * дата маршрута ГГГГ-ММ-ДД
        'start'         => '', // * начало маршрута ЧЧ:ММ:СС
        'finish'        => '', //   конец маршрута ЧЧ:ММ:СС
        'distance'      => '', //   Общая протяженность маршрута в метрах
        'to_1c_ts'      => 0,  //   timestamp экспорта в 1с
        'from_astra_ts' => 0,  //   Timestamp выгрузки из ASTRA
        );
    
    protected  $_has_one = array(
        'address' => array('model' => 'user_address', 'foreign_key' => 'address_id'),
    );
    
}
