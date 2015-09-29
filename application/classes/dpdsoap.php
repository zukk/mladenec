<?php

class DPDException extends Kohana_Exception {}


class DpdSoap {

    private $_auth = ['clientKey' => '6EF61351E761F28838D54B8F3E3191879D3412EF', 'clientNumber' => '1001034120']; //7F3655FB1E5170D70DE9D1C402CB8FE3AC09FD95

    private $_url = ['test' => 'http://wstest.dpd.ru/services/', 'real' => 'http://ws.dpd.ru/services/'];

    private $_client = NULL;

    private $_service = 'geography'; //, calculator2, order

    /**
     * Делает запрос к soap-сервису DPD
     * @param $function
     * @param $params
     * @return mixed
     * @throws DPDException
     */
    private function _request($function, $params)
    {
        try {
            $this->_client = new SoapClient($this->_url['real'].$this->_service.'?wsdl', ['exceptions' => TRUE, 'trace' => FALSE]);

            $result = $this->_client->{$function}($params);

        } catch (SoapFault $fault) {

            Log::instance()->add(Log::INFO, 'DPD SOAP fault on '.$function.': '.$fault->faultstring.' '.json_encode($fault->detail));
            return FALSE;

            //throw new DPDException('Soap request failed: '.$fault->faultstring.' '.$fault->faultcode, $params, $fault->faultcode);
        }
        return $result;
    }

    /**
     * Заполняет таблицу dpd_cities и dpd_regions
     */
    public function fill_cities()
    {
        $this->_service = 'geography';

        $result = $this->_request('getCitiesCashPay', ['auth' => $this->_auth]);

        $region_ins = DB::insert('dpd_region', ['id', 'name']); // собираем в один запрос
        $city_ins = DB::insert('dpd_city', ['id', 'name', 'region_id']);
        foreach($result->return as $c) {
            $city_ins->values([
                'id' => $c->cityId,
                'name' => $c->cityName,
                'region_id' => $c->regionCode,
            ]);

            $region_ins->values([
                'id'    => $c->regionCode,
                'name'  => $c->regionName,
            ]);
        }
        DB::query(Database::INSERT, $region_ins.' ON DUPLICATE KEY UPDATE name = VALUES(name)')->execute();
        DB::query(Database::INSERT, $city_ins.' ON DUPLICATE KEY UPDATE name = VALUES(name), region_id = VALUES(region_id)')->execute();
    }

    public function fill_terminals()
    {
        $this->_service = 'geography';

        $result = $this->_request('getTerminalsSelfDelivery2', ['auth' => $this->_auth]);

        $ins = DB::insert('dpd_terminal', ['name', 'code', 'city_id', 'address', 'latlong', 'worktime']);

        foreach($result->return as $t) {
            $c = $t->city;
            $t = $t->terminal;
            $ins->values([
                'name'      => $t->terminalName,
                'code'      => $t->terminalCode,
                'city_id'   => $c->cityId,
                'address'   => $t->terminalAddress,
                'latlong'   => ! empty($t->geoCoordinates) ? $t->geoCoordinates->geoX.','.$t->geoCoordinates->geoY : '',
                'worktime'  => json_encode($t->workingTime),
            ]);
        }
        DB::query(Database::INSERT, str_replace('INSERT', 'INSERT IGNORE', $ins))->execute();

    }

    /**
     * @param int|string $city
     * @param int $price
     * @param int $weight
     * @param float $volume
     * @param bool $door - дверь или терминал
     * @return StdClass[] массив вариантов доставки {serviceCode, serviceName, cost, days}
     * @throws DPDException
     */
    public function ship_price($city = 195851507, $price = 1000, $weight = 10, $volume = 0.2, $door = TRUE)
    {
        $this->_service = 'calculator2';

        $cache_key = $city.'|'.$price.'|'.$weight.'|'.$volume.'|'.intval($door);

        if ($cached = Cache::instance()->get($cache_key)) {

            $result = json_decode($cached);

        } else {

            $delivery = ['countryCode' => 'RU'];
            if (ctype_digit($city)) {
                $delivery['cityId'] = $city;
            } else {
                $delivery['cityName'] = $city;
            }

            Log::instance()->add(Log::INFO, 'DPD - запрос стоимости доставки '.$cache_key);

            $result = $this->_request('getServiceCost2', ['request' => [
                'auth' => $this->_auth,
                'pickup' => [
                    'cityId' => 195622915, // мытищи
                ],
                'delivery' => $delivery,
                'serviceCode' => 'PCL',
                'selfPickup' => FALSE,
                'selfDelivery' => ! $door,
                'weight' => $weight,
                'volume' => $volume,
                'declaredValue' => $price,
            ]]);
            if (floatval($result->cost) > 0) {
                $result->cost += $price * 0.03; // добавляем в цену доставки 3% агентских
            } else {
                $result->cost = FALSE;
            }

            Cache::instance()->set($cache_key, json_encode($result));
        }

        if (empty($result)) return FALSE;

        if ( ! is_array($result->return)) $result->return = [$result->return];

        return $result->return;
    }
}