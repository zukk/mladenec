<?php

class OzonDelivery {

    private $_runtime = 'test';
    private $_weight_limit = 20.0;
    private $_order_prefix = 1063;
    private $_auth = [
        'test' => [
            'login' => 'ApiUserTestPrincipal',
            'password' => 'j8k74zb9v',
            'contractId' => '4802172377000'
        ],
        'real' => [
            'login' => 'ApiUserMladenec2',
            'password' => 'w8asggrj',
            'contractId' => '4550083646000'
        ]
    ];
    private $_from_adddress = 'Московская область, г. Мытищи, Волковское шоссе, д.15В, строение 1';
    private $_url = [     
        'delivery' => [
            'test' => 'https://api.ocourier.ru/test/DeliveryService.asmx?wsdl',
            'real' => 'https://api.ocourier.ru/DeliveryService.asmx?wsdl'            
        ],
        'manifest' => [
            'test' => 'https://api.ocourier.ru/test/ManifestService.asmx?wsdl',
            'real' => 'https://api.ocourier.ru/ManifestService.asmx?wsdl'
        ],
        'tracking' => [
            'test' => 'https://api.ocourier.ru/test/TrackingService.asmx?wsdl',
            'real' => 'https://api.ocourier.ru/TrackingService.asmx?wsdl'
            
        ]
    ];
    private $_client_delivery = NULL;
    private $_client_manifest = NULL;
    private $_client_tracking = NULL;
    
    const DELIVERY_API = 'delivery';
    const MANIFEST_API = 'manifest';
    const TRACKING_API = 'tracking';
    
    private $_tracing_codes = [];

    /**
     * Запрос к сервису доставки Ozon
     * @param $function
     * @param $params
     * @param $api_type
     * @return mixed
     * @throws SoapFault
     */
    private function _request($function, $params, $api_type = OzonDelivery::DELIVERY_API)
    {
        try {
            $client_name = '_client_'.$api_type;
            if (!$this->$client_name) {                
                $this->$client_name = new SoapClient($this->_url[$api_type][$this->_runtime], ['exceptions' => TRUE, 'trace' => TRUE, 'keep_alive' => FALSE]);
            } 
            $client = $this->$client_name;
            
            $params = array_merge($this->_auth[$this->_runtime], $params); 

            if ($function == 'CalculateTariff')   {
                $params['contractID'] = $params['contractId'];
                unset($params['contractId']);
            } elseif($function == 'RemoveUnformattedPosting' || $function == 'GetUnprocessedPostingList') {
                $params['partnerContractId'] = $params['contractId'];
                unset($params['contractId']);
            }
            $result = $client->{$function}($params);     
        } catch (SoapFault $fault) {
            Log::instance()->add(Log::INFO, 'Ozon Delivery SOAP fault on ' . $function . ': ' . $fault->faultstring . ' ' . json_encode($fault->detail));
            return FALSE;
        }
        return $result;
    }

    private function check_products()
    {
        $milk_filter_id = 2061;
        $milk_filter_values = [17501, 17502, 17507, 17509]; //йогурт, кефир, смесь и творог
        $cart = Cart::instance();
        if ($cart->ship_wrong == FALSE && $cart->weight() > $this->_weight_limit) {
            return ['error' => 'Самовывоз доступен только для заказов не превышающих вес 20 кг'];
        }
        if(count($cart->goods) == 0) return false;
        $ids = array_keys($cart->goods);
        $goods = ORM::factory('good')
                ->where('id', 'IN', $ids)
                ->find_all()
                ->as_array('id');

        $goods_blist = DB::select('good_id')
                ->from('z_good_filter')
                ->where('value_id', 'in', $milk_filter_values)
                ->where('filter_id', '=', $milk_filter_id)
                ->where('good_id', 'in', $ids)
                ->execute()
                ->as_array('good_id');
        $goods_blist = array_keys($goods_blist);
        foreach ($goods as $g) {
            if ($g->big == 1) {
                return ['error' => true];
            }
            if ($g->section_id == Model_Section::MILK_ID && in_array($g->id, $goods_blist)) {
                return ['warning' => true];
            }
        }
        return true;
    }

    /*
     * Получение терминалов из БД
     */
    public function get_terminals() {
        $check = $this->check_products();
        
        //получаем терминалы из базы
        $terminals = ORM::factory('ozon_terminal')         
                ->where('is_active', '=', 1)       
                ->find_all()
                ->as_array('id');   
        
        $result = new stdClass();
        if (count($terminals)>0) {
            $result->items = $terminals;
            $result->center_lng = $result->center_lat = 0;
            $min_lng = 90;
            $max_lng = -90;
            $min_lat = 180;
            $max_lat = -180;
            
            foreach ($result->items as $k => $item) {                  
                if ($item->lat == 0) {
                    unset($result->items[$k]);
                    continue;
                }
                if ($max_lat < $item->lat)
                    $max_lat = $item->lat;
                if ($min_lat > $item->lat)
                    $min_lat = $item->lat;
                if ($max_lng < $item->lng)
                    $max_lng = $item->lng;
                if ($min_lng > $item->lng)
                    $min_lng = $item->lng;
            }
            $result->center_lng = $min_lng + ($max_lng - $min_lng) / 2;
            $result->center_lat = $min_lat + ($max_lat - $min_lat) / 2;      
            
            $result->error = isset($check['error']);
            $result->warning = isset($check['warning']);
            return $result;
        } else
            return false;
    }
    
    /*
     * Обновление терминалов в БД
     */
    public function update_terminals() {
        //получаем текущие терминалы
        $old_terminals = ORM::factory('ozon_terminal')                
                ->find_all()
                ->as_array('id');   
        
        //делаем все терминалы неактивными пока не получим их от апи
        DB::update('ozon_terminal')
            ->set(array('is_active' => 0))
            ->execute();
                
        $params = [ 
            'cityName' => 'Москва',
            'type' => 'Самовывоз'
            ];
        $function = 'GetDeliveryVariantList';
        $reply_name = 'GetDeliveryVariantReply';
        $result = $this->_request($function, $params);
        if (isset($result->$reply_name)) {
            $result = $result->$reply_name;
        }
        if (isset($result->Items->DeliveryVariant)) {
            //получаем от апи список терминалов
            $terminals = $result->Items->DeliveryVariant;            
            foreach ($terminals as $k => $t) {
                //если по терминалу уже есть данные и адрес не поменялся - просто активируем                
                if( in_array($t->Id, array_keys($old_terminals))
                   &&  $old_terminals[$t->Id]->address ==  $t->Address )
                {
                    DB::update('ozon_terminal')
                    ->set( ['is_active' => 1] )
                    ->where ('id', '=', $t->Id)
                    ->execute();
                } else {
                    $data = json_decode(file_get_contents('https://geocode-maps.yandex.ru/1.x/?format=json&geocode=' . urlencode($t->Address)));
                    $address = $data->response->GeoObjectCollection->featureMember;
                    $lat = 0;
                    $lng = 0;
                    foreach ($address as $value) {
                        $f = $value->GeoObject->metaDataProperty->GeocoderMetaData;
                        if ($f->precision == 'exact') {
                            $coord = explode(' ', $value->GeoObject->Point->pos);
                            $lat = $coord[0];
                            $lng = $coord[1];
                            break;
                        }
                    }
                    
                    if( in_array($t->Id, array_keys($old_terminals)) ) {
                        //адрес отделения поменялся - пытаемся обновить координаты
                        DB::update('ozon_terminal')
                            ->set( [
                                'address'   => $t->Address,
                                'lat'       => $lat, 
                                'lng'       => $lng,
                                'is_active' => 1] )
                            ->where ('id', '=', $t->Id)
                            ->execute();
                    } else {
                        //новое отделение - вычисляем координаты и добавляем
                        DB::insert('ozon_terminal', ['id' , 'address', 'lat', 'lng', 'is_active'])
                            ->values([$t->Id, $t->Address, $lat , $lng, 1])
                            ->execute();
                    }
                    
                }                
            }
        } elseif (isset($result->ErrorMessage)) {
            Log::instance()->add(Log::INFO, 'Ozon Delivery update terminals failed GetDeliveryVariantList: ' . $result->ErrorMessage->Type . ' ' . $result->ErrorMessage->Message);
        }      
    }

    /*
     * Получение стоимости долставки с учетом ограничений
     */
    public function calculate_price($delivery_variant_id, $weight) {           
        $check = $this->check_products();
        $params = [ 
            'deliveryVariantID' => $delivery_variant_id,
            'weight' => intval($weight*1000),
            'fromPlaceID' => ''
            ];
        $function = 'CalculateTariff';
        $reply_name = 'CalculateTariffReply';
        $result = $this->_request($function, $params);
        
        $cart = Cart::instance();
        $cache_key = md5('ozon_delivery_price'.$delivery_variant_id.$weight.serialize($cart->goods)) . Kohana::$server_name;
        $response = Cache::instance()->get($cache_key);        
        if($response) return $response;
        
        if (isset($result->$reply_name)) {
            if($result->$reply_name->ErrorMessage->Type == 'E_OK') {
                $response = ['price' => ceil($result->$reply_name->Ammount)];
            } else {
                $response = ['warning' => 'Стоимость доставки будет вычислена после оформления заказа'];
                Log::instance()->add(Log::INFO, 'Ozon Delivery '.$function.' no ammount: ' . print_r($result->$reply_name, true));
            }
            $response['status_error'] =  isset($check['error']);
            $response['status_warning'] =  isset($check['warning']);            
            Cache::instance()->set($cache_key, $response, 10*60); //кэш стоимости доставки на 10 минут
            return $response;
        } else
            return false;
    }
    
    /*
     * получение стоимости доставки по весу и терминалу
     */
    public function get_price($delivery_variant_id, $weight) {
        $params = [ 
            'deliveryVariantID' => $delivery_variant_id,
            'weight' => intval($weight*1000),
            'fromPlaceID' => ''
            ];
        $function = 'CalculateTariff';
        $reply_name = 'CalculateTariffReply';
        $result = $this->_request($function, $params);
        
        if (isset($result->$reply_name)) {
            if($result->$reply_name->ErrorMessage->Type == 'E_OK') {
                return ceil($result->$reply_name->Ammount);
            } else {                
                Log::instance()->add(Log::INFO, 'Ozon Delivery '.$function.' no ammount: ' . print_r($result->$reply_name, true));
                return false;
            }            
        } else
            return false;        
    }

    /*
     * Отправка/обновление заказа в Озон
     */
    public function upload_order($order, $order_data, $delivery_variant_id) {
        $manifest = $this->_create_manifest($order, $order_data, $delivery_variant_id);            
        
        $params = [ 
            'PartnerPosting' => $manifest
           ];
        $function = 'UploadClearingManifest';
        $reply_name = 'UploadManifestReply';
        $result = $this->_request($function, $params, OzonDelivery::MANIFEST_API);
        
        if (isset($result->$reply_name)) {
            Log::instance()->add(Log::INFO, 'Ozon Delivery '.$function.' RESULT: ' . print_r($result->UploadManifestReply, true) );
            if($result->$reply_name->ErrorMessage->Type == 'E_OK') {
                return true;
            } else {
                Log::instance()->add(Log::INFO, 'Ozon Delivery error '.$function.': ' . $result->$reply_name->ErrorMessage->Type . ' ' . $result->$reply_name->ErrorMessage->Message);
                return false;
            }
        } else
            return false;
    }
    
    /*
     * Формирование манифеста для создания/изменения заказа
     */
    private function _create_manifest($order, $order_data, $delivery_variant_id) {
        $cart  = Cart::instance();
        $goods = $order->get_goods();
        if(count($goods) == 0) return false;
        $manifest = [];
        $manifest['PartnerContractID'] = $this->_auth[$this->_runtime]['contractId'];
        $manifest['PostingList']['Posting']['PostingOutNumber'] = $this->_order_prefix.'-'.$order->id;
        $manifest['PostingList']['Posting']['DeliveryVariantID'] = $delivery_variant_id;      
        if($order->pay_type == Model_Order::PAY_CARD) {
            $manifest['PostingList']['Posting']['AmountRecipientPayment'] = 0;
            $manifest['PostingList']['Posting']['DeliveryPrice'] = 0;
        } else {
            $manifest['PostingList']['Posting']['AmountRecipientPayment'] = $order->price;
            $manifest['PostingList']['Posting']['DeliveryPrice'] = $order->price_ship;
        }
        $manifest['PostingList']['Posting']['PostingCost'] = $order->price;        
        $manifest['PostingList']['Posting']['Weight'] = intval($cart->weight()*1000);
        $manifest['PostingList']['Posting']['RecipientAddress'] = $order_data->address;
        $manifest['PostingList']['Posting']['RecipientFirstName'] = $order_data->last_name;
        $manifest['PostingList']['Posting']['RecipientLastName'] = $order_data->name;
        $manifest['PostingList']['Posting']['RecipientMidleName'] = $order_data->second_name;
        $manifest['PostingList']['Posting']['IsCompany'] = 0;
        $manifest['PostingList']['Posting']['RecipientComment'] = $order->description;
        $manifest['PostingList']['Posting']['RecipientEmail'] = $order_data->email;
        $manifest['PostingList']['Posting']['RecipientPhone'] = $order_data->phone;
        $manifest['PostingList']['Posting']['IsFf'] = 0;
        foreach($goods as $g) {
            //TODO: если ноль - нужно дать фидбек в 1с
            $nds = ($g->nds == 0)? 10.00 : $g->nds;
            $manifest['PostingList']['Posting']['PostingItemList']['Item'][] = [
                'ItemID'   => $g->id1c,
                'ItemName'  => ( ($g->group_name)? $g->group_name.' ':'' ). $g->name,
                'ItemPrice' => $g->price,
                'ItemQty'   => $g->quantity,
                'ItemNds'  => $nds,
                'ItemNdsSum'  => ($nds*$g->price/100)                    
            ];
        }    
        
        return $manifest;
    }

    /*
     * Получение штрихкода после отправки заказа в озон
     */
    public function get_barcode($order_id) {    
        $posting_id = $this->_order_prefix.'-'.$order_id;    
        $params = [ 
            'postingNumber' => $posting_id,
            'rowsPerPage'   => 1,
            'pageNumber'    => 1,
            'searchCondition' => $posting_id
           ];
        $function = 'GetUnprocessedPostingList';
        $reply_name = 'GetPostingListReply';
        $result = $this->_request($function, $params, OzonDelivery::MANIFEST_API);
        
        if (isset($result->$reply_name)) {            
            if(isset($result->$reply_name->Postings->UnprocessedPosting) && 
                    $result->$reply_name->ErrorMessage->Type == 'E_OK') {
                $barcode = $result->$reply_name->Postings->UnprocessedPosting->ClearingPosting->PostingOutBarCode;
                return $barcode;                
            } else {
                Log::instance()->add(Log::INFO, 'Ozon Delivery error '.$function.': ' . $result->$reply_name->ErrorMessage->Type . ' ' . $result->$reply_name->ErrorMessage->Message);
                return false;
            }
        } else return false;
    }

    /*
     * Отмена не оприходованного заказа
     */
    public function cancel_order($order_id) {
        $posting_id = $this->_order_prefix.'-'.$order_id;
        $params = [ 
            'postingNumber' => $posting_id
           ];
        $function = 'RemoveUnformattedPosting';
        $reply_name = 'RemoveUnformattedPostingReply';
        $result = $this->_request($function, $params, OzonDelivery::MANIFEST_API);
        
        if (isset($result->$reply_name)) {            
            if($result->$reply_name->ErrorMessage->Type == 'E_OK') {
                Log::instance()->add(Log::INFO, 'Ozon Delivery Posting deleted: ' .$posting_id );
                return true;                
            } else {
                Log::instance()->add(Log::INFO, 'Ozon Delivery error '.$function.': ' . $result->$reply_name->ErrorMessage->Type . ' ' . $result->$reply_name->ErrorMessage->Message);
                return false;
            }
        } else return false;
    }
    
    /*
     * Получение статуса отправки по штрихкоду
     */
    public function get_tracking($barcode) {
        $params = [ 
            'barcode' => $barcode
           ];
        $function = 'GetArticleTrackingByBarcode';
        $reply_name = 'GetArticleTrackingReply';
        $result = $this->_request($function, $params, OzonDelivery::TRACKING_API);
         
        if (isset($result->$reply_name)) {            
            if($result->$reply_name->ErrorMessage->Type == 'E_OK') {
                $reply = [];
                $reply['event_id'] = $result->$reply_name->Items->ArticleTrackingItem->EventID;
                $reply['event_name'] = $result->$reply_name->Items->ArticleTrackingItem->Action;
                $reply['update_time'] = $result->$reply_name->Items->ArticleTrackingItem->Moment;
                return $reply;                
            } else {
                Log::instance()->add(Log::INFO, 'Ozon Delivery error '.$function.': ' . $result->$reply_name->ErrorMessage->Type . ' ' . $result->$reply_name->ErrorMessage->Message);
                return false;
            }
        } else return false;
    }
}