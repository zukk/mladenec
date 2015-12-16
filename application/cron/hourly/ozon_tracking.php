<?php
/*
 * Получаем все актуальные заказы с доставкой Озон и трекаем их
 */
require('../../../www/preload.php');

$orders = ORM::factory('order')
        ->with('data')
        ->where('delivery_type', '=', Model_Order::SHIP_SERVICE)
        ->where('ship_code', '=', Model_Order::SERVICE_OZON)
        ->where('ship_barcode', '!=', '')
        ->where('ship_status', '>', '0')
        ->where('ship_status', 'NOT IN', [50,120]) //2 крайних положения 50 - выдано, 120 - возврат на склад
        ->find_all()
        ->as_array('id');

if(count($orders)>0) {
    $ozon = new OzonDelivery();

    foreach ($orders as $o) {
        $tracking = $ozon->get_tracking($o->data->ship_barcode);
        //если треккинг изменился, меняем его и оповещаем 1с
        if($o->data->ship_status != $tracking['event_id']) {
            $order_data = new Model_Order_Data($o->id);
            $order_data->ship_status  = $tracking['event_id'];
            $order_data->save();
            $order = new Model_Order($o->id);
            $order->in1c = 0;
            $order->save();
        }
    }
}
