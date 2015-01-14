<?php

class DPDException extends Kohana_Exception {}


class DpdSoap {

    private $_auth = ['clientKey' => '6EF61351E761F28838D54B8F3E3191879D3412EF', 'clientNumber' => '1001034120']; //7F3655FB1E5170D70DE9D1C402CB8FE3AC09FD95

    private $_url = ['test' => 'http://wstest.dpd.ru/services/geography?wsdl', 'real' => 'http://ws.dpd.ru/services/geography?wsdl'];

    private $_client = NULL;

    /**
     * конструктор создаёт соап-клиента
     * @throws DPDException
     */
    function __construct()
    {
        try {

            $this->_client = new SoapClient($this->_url['real'], ['exceptions' => TRUE, 'trace' => FALSE]);

        } catch (SoapFault $fault) {

            throw new DPDException('Soap request failed: '.$fault->faultstring, NULL, $fault->faultcode);
        }
    }

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

            $result = $this->_client->{$function}($params);

        } catch (SoapFault $fault) {

            throw new DPDException('Soap request failed: '.$fault->faultstring, NULL, $fault->faultcode);
        }
        return $result;
    }

    /**
     * Заполняет таблицу dpd_cities и dpd_regions
     */
    public function fill_cities()
    {
        $result = $this->_request('getCitiesCashPay', ['auth' => $this->_auth]);

        $city_ins = DB::insert('dpd_city', ['id', 'name', 'region_id']);
        $region_ins = DB::insert('dpd_region', ['id', 'name']);

        foreach($result->return as $c) {
            $city_ins->values([
                'id'        => $c->cityId,
                'name'      => $c->cityName,
                'region_id' => $c->regionCode,
            ]);
            $region_ins->values([
                'id'    => $c->regionCode,
                'name'  => $c->regionName,
            ]);
        }

        DB::query(Database::INSERT, $city_ins.' ON DUPLICATE KEY UPDATE name = VALUES(name), region_id = VALUES(region_id)')->execute();
        DB::query(Database::INSERT, $region_ins.' ON DUPLICATE KEY UPDATE name = VALUES(name)')->execute();
    }
}