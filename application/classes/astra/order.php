<?php
/**
 *
 */
class Astra_Order extends Astra_Client {
    
    /**
     * Ключи - имена в Астра, поля - имена в БД
     */
    private $field_map = array(
        'id'               => 'id',
        'number'           => 'check_number',
        'orderKind'        => 'kind_id',
        'typeName'         => 'display_type_name',
        'weight'           => 'weight',
        'volume'           => 'volume',
        'volumeA'          => 'volume_a',
        'demandMarker'     => 'demand_marker',     // (string) DemandMarker метки - разделитель ","
        'comment'          => 'goods',             // (string) Товары через "|" - временно, пока тестируем
        'deliveryPointId'  => 'delivery_point_id',
        'pickupPointId'    => 'pickup_point_id',
    );
    
    protected function model_to_array($model, $map) {
        $array = parent::model_to_array($model, $map);
        if (empty($array['number'])) $array['number'] = 0;
        
        $array['deliveryFrom'] = Txt::time_to_seconds($model->delivery_from)           * 1000; // milliseconds, nobody knows what for...
        $array['deliveryTo']   = Txt::time_to_seconds($model->delivery_to)             * 1000;
        $array['pickupFrom']   = Txt::time_to_seconds($model->pickup_from, '09:00:00') * 1000;
        $array['pickupTo']     = Txt::time_to_seconds($model->pickup_to,   '18:00:00') * 1000;
        
        $array['deliveryDuration']  = $model->delivery_duration * 60 * 1000;
        $array['pickupDuration']    = $model->pickup_duration   * 60 * 1000;
        
        $array['customer'] = 'Имя клиента: ' . $model->name 
                . '; контакты: ' . $model->contacts
                . '; адрес: '    . $model->full_address
                . '; сумма: '    . $model->sum
                . '; оплата: '   . $model->payment
                . '; сдача с: '  . $model->short_change
                . ( $model->water        ? '; вода! '            : '')
                . ( $model->milk         ? '; молочка! '         : '')
                . ( $model->big          ? '; крупногабаритка! ' : '')
                . ( $model->floor_pickup ? '; подъем на этаж! '  : '');
        
        list($year,$month,$day) = explode('-',$model->date);
        $array['date'] = array('day' => $day, 'month' => $month, 'year' => $year);
       
        $array['cost']    = str_replace('.', ',', (string) round($model->sum,2));
        
        return $array;
    }

    /**
     * 
     * @param Model_Astra_Order $order_obj
     * @param bool $skip_existing
     */
    public function add_order($order_obj, $skip_existing = FALSE) {
        
        $method_name = 'addOrder';
        $params = array('order'=>$this->model_to_array($order_obj, $this->field_map));
        
        if ($skip_existing) {
            $method_name = 'addOrderExt';
            $params['skipExisting'] = true;
        }
        
        return $this->c($method_name,$params);
    }
    
    /**
     * 
     * @param Model_Astra_Order $orders_obj
     * @param bool $skip_existing
     */
    public function add_orders($orders_obj, $skip_existing = FALSE) {
        
        $method_name = 'addOrders';
        $params = array('orderList' => array());
        
        foreach($orders_obj as $or_o) { $params['orderList'][] = $this->model_to_array($or_o, $this->field_map); }
        
        if ( empty($params['orderList'])) return FALSE;
        
        if ($skip_existing) {
            $method_name = 'addOrdersExt';
            $params['skipExisting'] = true;
        }
        
        return $this->c($method_name, $params);
    }
    
    public function get_order_list($day,$month,$year) {
        
    }
    
    public function delete_order($id) {
        return $this->c('deleteOrder', array('orderId'=>$id));
    }
    
    public function delete_all_orders() {
        return $this->c('deleteAllOrders',array());
    }
    
    public function __construct() {
        parent::__construct('order');
    }
}
