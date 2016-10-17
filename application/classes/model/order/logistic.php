<?php
/**
 * Модель для заказов с точки зрения логистики
 */
class Model_Order_Logistic extends ORM  {

    const KIND_DELIVERY     = 0;
    const KIND_DELIVERY_STR = 'DELIVERY';
    const KIND_PICKUP       = 1;
    const KIND_PICKUP_STR   = 'PICKUP';
    const KIND_PnD          = 2;
    const KIND_PnD_STR      = 'P&D';
    
    protected $_table_name = 'z_logistic_order';

                                    // *      = Обязательное поле в протоколе сайт-астра
                                    //  +     = Обязательное поле в протоколе 1с-сайт
                                    //   1    = номер поля в csv
                                    //     ID = наименование поля в протоколе сайт-астра
    protected $_table_columns = array(
        'id' => '',                 // * -  ID              - заказа != номеру
        'order_id' => '',           // *+1  Number          - номер заказа
        'kind' => '',               // * 2  Kind            - тип заказа - [PICKUP|DELIVERY|P&D] - доставка, получение, получение + доставка
        'kind_id' => '',            // * 18 -               - тип заказа - [1     |2       |3  ] - доставка, получение, получение + доставка - число
        'display_type_name' => '',  //   -  DisplayTypeName - имя типа заказа, по умолчанию пустая строка
        'date' => '',               // *    Date            - дата погрузки/доставки
        'pickup_address'=>'',       //   3                  - адрес грузоотправителя
        'pickup_latitude'=>'',      //   4                  - широта грузоотправителя
        'pickup_longitude'=>'',     //   5                  - долгота грузоотправителя
        'delivery_address'=>'',     // *+6                  - адрес грузополучателя
        'delivery_latitude'=>'',    //   7                  - широта грузополучателя
        'delivery_longitude'=>'',   //   8                  - долгота грузополучателя
        'delivery_point_id' => '',  // * -  deliveryPointID - точка доставки (ID существующей точки)
        'pickup_from',              //   9  pickupFrom      - временное окно грузоотправителя
        'pickup_to',                //   9  pickupTo        - временное окно грузоотправителя
        'pickup_point_id' => '',    // * -  pickupPointID   - Точка доставки ID существующей точки
        'delivery_from',            //   10 deliveryFrom временное окно грузополучателя
        'delivery_to',              //   10 deliveryTo   временное окно грузополучателя
        'weight'=>'',               //   11 Weight - вес
        'volume'=>'',               //   12 Volume - объем
        'pickup_duration'=>'',      //   13 pickupDuration - длительность погрузки в минутах
        'delivery_duration'=>'',    // *+14 длительность разгрузки в минутах
        'volume_a'=>'',             //   15 VolumeA - альтернативный объём, напр. в попугаях (палеты, контейнеры, клети)
        // 16 время фактического поступления заказа
        
        'labels'=>'',               //   17 DemandMarker метки в csv разделитель "+", в сервисе ","
        

        );
    
    protected  $_has_one = array(
        'order' => array('model' => 'order', 'foreign_key' => 'id')
    );
    
    public static function to_queue($phone, $text, $user_id = 0, $order_id = 0) {
        
        $phone = Txt::phone_clear($phone);
        if ( ! $phone) {
            Log::instance()->add(Log::INFO, 'Error when trying to add SMS to queue to user #' . $user_id . ' - wrong phone ' . $phone . ' format.');
            return FALSE;
        }
        
        $sms = ORM::factory('sms');
        $sms->values(array(
            'user_id'    => $user_id,
            'order_id'   => $order_id,
            'phone'      => Txt::phone_clear($phone),
            'text'       => $text,
            'created_ts' => time()
        ));
        $sms->save();
    }
}
