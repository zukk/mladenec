<?php
/**
 *
 */
class Astra_Route extends Astra_Client {
    const WSDL = 'http://ips-logistic.com:9000/astra-test/api/route?wsdl';

    public function get_route_list($year,$month,$day) {
        echo ("\nGetting routes \n");
        return $this->c('getRouteList', array(
            'date' => array(
                'year' => $year,
                'month' => $month,
                'day' => $day
                )));
    }

    public function delete_all_routes() {
        return $this->c('deleteAllRoutes',array());
    }

    public function __construct() {
        parent::__construct('route');
    }
}
