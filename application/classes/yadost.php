<?php

/**
 * Class для работы с Яндекс-доставкой
 */
class yadost {

    private static $href = 'https://delivery.yandex.ru/api/last'; // 'http://private-anon-b67e2c272-yandexdelivery.apiary-mock.com'; //
    
    private static $keys = [
        'getPaymentMethods'     => '817ab21e4101d80eac95b080c1af13df49e4a522198338e633f0163e244235d6',
        'getSenderOrders'       => '817ab21e4101d80eac95b080c1af13df20c983323f7da6dcedf1050026ed5645',
        'getSenderOrderLabel'   => '817ab21e4101d80eac95b080c1af13df986cbb9072715569d22ec4a94cda631e',
        'getSenderParcelDocs'   => '817ab21e4101d80eac95b080c1af13df6adac8201c8e48de754e8a572e093ddb',
        'autocomplete'          => '817ab21e4101d80eac95b080c1af13df6e924771aad9548fa7e429d7c6dbef22',
        'getIndex'              => '817ab21e4101d80eac95b080c1af13df26e38fe17dd3d1107330c3c66eef6f49',
        'createOrder'           => '817ab21e4101d80eac95b080c1af13df26c03e6a40890d3651f48df45c031bb4',
        'updateOrder'           => '817ab21e4101d80eac95b080c1af13df4c9bb60e51f2cff36bd8d579c0483b04',
        'deleteOrder'           => '817ab21e4101d80eac95b080c1af13df4dd37db58607d7534a80837b26b2a589',
        'getSenderOrderStatus'  => '817ab21e4101d80eac95b080c1af13df85b86e4a4052b308689ced49de3fcedc',
        'getSenderOrderStatuses'=> '817ab21e4101d80eac95b080c1af13df517a060c25845917af7f117924976a97',
        'getSenderInfo'         => '817ab21e4101d80eac95b080c1af13df2884c33d8fd0c65526217c293e1ca05a',
        'getWarehouseInfo'      => '817ab21e4101d80eac95b080c1af13df3b80fc4dff135e801e3483a7d81a1fd1',
        'getRequisiteInfo'      => '817ab21e4101d80eac95b080c1af13dfbffb9d7f0370b44aabd01e29c2807827',
        'getIntervals'          => '817ab21e4101d80eac95b080c1af13df5a57ca0ca81a32c64eafb74d05a4a73d',
        'createWithdraw'        => '817ab21e4101d80eac95b080c1af13dfb9edb04ac6e8cd76dd6cdc4c60f8e981',
        'confirmSenderOrders'   => '817ab21e4101d80eac95b080c1af13dfb764b9e53016d0b3450e3696171c7a9c',
        'updateWithdraw'        => '817ab21e4101d80eac95b080c1af13dfff7e2ddd65af859d12f9c67aa0c8f867',
        'createImport'          => '817ab21e4101d80eac95b080c1af13df656c6bab984280f236848b3de802699d',
        'updateImport'          => '817ab21e4101d80eac95b080c1af13df341cc94104b4fe9f777249af3cbe5742',
        'getDeliveries'         => '817ab21e4101d80eac95b080c1af13df160e658359adeeff15639800298001d1',
        'getOrderInfo'          => '817ab21e4101d80eac95b080c1af13dfe285a611f5f8788c67ab03d2acd97991',
        'searchDeliveryList'    => '817ab21e4101d80eac95b080c1af13df5c50d6e56adebb835b1b20e7f864fad8',
        'confirmSenderParcels'  => '817ab21e4101d80eac95b080c1af13df1c05fb5260fe954497a1466fb00a1f35',
    ];
 
    private static $ids = [
        "client_id" => 3127,
        "sender_ids" => [1540],
        "warehouse_ids" => [1165],
        "requisite_ids" => [733]
    ];


    /**
     * Общая функция для запроса к APi - готовит запрос, подписывает, получает результат
     * если вернула FALSE - что-то пошло не так
     * @param $func
     * @param $data
     * @return bool|mixed
     */
    private static function _request($func, $data)
    {
        if (empty(self::$keys[$func])) return FALSE;

        $data['client_id'] =  self::$ids['client_id'];
        $data['sender_id'] =  self::$ids['sender_ids'][0];
        ksort($data); // по ключам
        $data['secret_key'] =  md5(implode('', $data).self::$keys[$func]);
        $curl = new Curl();
        $resp = $curl->get_url(self::$href.'/'.$func, $data);

        Log::instance()->add(Log::INFO, 'YaDelivery request '.$func.print_r($data, TRUE).' result '.print_r($resp, TRUE));

        $json = json_decode($resp);

        if ( ! $json) {
            Log::instance()->add(Log::INFO, 'YADOST fault on '.$func.': Cannot parse JSON ');
            return FALSE;
        }

        if ($json->status == 'ok') {
            return $json;
        } else {
            Log::instance()->add(Log::INFO, 'YADOST error on '.$func.': '.$json->error.' '.json_encode($json->data));
            return FALSE;
        }
    }

    /**
     * Получение возможных способов оплаты заказа получателем
     */
    static function getPaymentMethods()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Получение информации о магазине из аккаунта в сервисе
     */
    static function getSenderInfo()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Получение данных о складе из аккаунта в сервисе
	 */
    static function getWarehouseInfo()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Получение информации о реквизитах магазина из аккаунта в сервисе
	 */
    function getRequisiteInfo()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Автоматическое дополнение названий города, улицы и дома
     * @param $term string
     * @param $type string locality, street, house
     * @param $locality_name string
     * @param $street string
     * @return bool
     */
    static function autocomplete($term, $type = 'locality', $locality_name = NULL, $street = NULL)
    {
        $data['term'] = $term;
        $data['type'] = $type;
        if ($type == 'street' || $type == 'house') {
            $data['locality_name'] = $locality_name;
        }
        if ($type == 'house') {
            $data['street'] = $street;
        }
        $resp = self::_request(__FUNCTION__, $data);
        if ($resp !== FALSE) return $resp->data->suggestions;
        return FALSE;
    }

    /**
     * Определение индекса по указанному адресу
	 */
    function getIndex()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Получение наименований доступных служб доставки
	 */
    function getDeliveries()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Получение доступных вариантов доставки
     * Город, из которого осуществляется доставка.
     * string, required
     * example: Москва
     * city_to
     * Город, в который осуществляется доставка.
     * string, required
     * example: Киров, Кировская область
     * weight
     * Вес посылки, в кг.
     * float, required
     * height
     * Высота посылки, в см.
     * int, required
     * width
     * Ширина посылки, в см.
     * int, required
     * length
     * Длина посылки, в см.
     * int, required
     * delivery_type
     * Тип доставки: курьером до двери либо в пункт выдачи заказов. Если тип не указан, будут загружены все варианты.
     * string, optional
     * Possible values:
     * todoor — до двери;
     * pickup — пункт выдачи заказов.
     * total_cost
     * Общая стоимость отправления, в руб. При передаче будут автоматически рассчитаны страховка и кассовое обслуживание, а также применены правила редактора тарифов.
     * float, optional
     * index_city
     * Индекс получателя. Будут отфильтрованы службы, которые не осуществляют доставку по данному индексу.
     * number, optional
     * create_date
     * Дата отгрузки посылки. Ориентировочная дата доставки будет рассчитана в соответствии с датой отгрузки.
     */
    static function searchDeliveryList($city_to, $weight, $height, $width, $length, $total_cost = 0, $door = TRUE)
    {
        $data['city_from'] = 'Москва';
        $data['city_to'] = $city_to;
        $data['weight'] = $weight;
        $data['width'] = $width;
        $data['height'] = $height;
        $data['length'] = $length;
        $data['delivery_type'] = $door ? 'todoor' : 'pickup';

        if ( ! empty($total_cost)) $data['total_cost'] = $total_cost;
        $resp = self::_request(__FUNCTION__, $data);
        //var_dump($resp);
        if ($resp !== FALSE) return $resp->data;
        return FALSE;
    }

    /**
     * Создание заказа
	 */
    function createOrder()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Редактирование заказа
	 */
    function updateOrder()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Отправка заказов в службу доставки
	 */
    function confirmSenderOrders()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Удаление заказа
	 */
    function deleteOrder()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Получение доступных временных интервалов для заказа забора или самопривоза
	 */
    function getIntervals()
    {

    }

    /**
     * Создание заявки на забор заказов у магазина
	 */
    function createWithdraw()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Создание заявки на самостоятельный привоз заказов магазином
	 */
    function createImport()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Создание реестра заказов
	 */
    function confirmSenderParcels()
    {

    }

    /**
     * Получение ярлыка для заказа
	 */
    function getSenderOrderLabel()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Получение сопроводительных документов
	 */
    function getSenderParcelDocs()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Получение информации по созданному заказу
	 */
    function getOrderInfo()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Получение данных о заказах определенного магазина
	 */
    function getSenderOrders()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Получение текущего статуса заказа
	*/

    function getSenderOrderStatus()
    {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }

    /**
     * Получение истории статусов заказа
     */
    function getSenderOrderStatuses() {
        echo __FUNCTION__;
        self::_request(__FUNCTION__);
    }
}