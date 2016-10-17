<?php
/*
 * Получаем все актуальные заказы с доставкой Озон и трекаем их
 */
require('../../../www/preload.php');

$orders = ORM::factory('order')
        ->with('data')
        ->where('ozon_barcode', '<>', '')
        ->where('ozon_status', '>', '0')
        ->where('ozon_status', 'NOT IN', [50,120]) //2 крайних положения 50 - выдано, 120 - возврат на склад
        ->find_all()
        ->as_array('id');
if(count($orders)>0) {
    $ozon = new OzonDelivery();

    foreach ($orders as $o) {
        $tracking = $ozon->get_tracking($o->data->ozon_barcode);
        //если треккинг изменился, меняем его и оповещаем 1с
        if($o->data->ozon_status != $tracking['event_id']) {
            $order_data = new Model_Order_Data($o->id);
            $order_data->ozon_status  = $tracking['event_id'];
            $order_data->save();
            $order = new Model_Order($o->id);
            $order->in1c = 0;
            $order->save();
        }
    }
}
