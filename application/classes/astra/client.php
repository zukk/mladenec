<?php
/**
 * Соап-клиент для интеграции с Астра-экспресс
 */
class Astra_Client {
    
    /* //WAR!!!! 
    private static $wsdl = array(
        'generic'  => 'http://ips-logistic.com:9002/astrapro/api/generic?wsdl',
        'planning' => 'http://ips-logistic.com:9002/astrapro/api/planning?wsdl',
        'garage'   => 'http://ips-logistic.com:9002/astrapro/api/garage2?wsdl',
        'order'    => 'http://ips-logistic.com:9002/astrapro/api/order?wsdl',
        'point'    => 'http://ips-logistic.com:9002/astrapro/api/point?wsdl',
        'resource' => 'http://ips-logistic.com:9002/astrapro/api/resource?wsdl',
        'route'    => 'http://ips-logistic.com:9002/astrapro/api/route?wsdl',
    );
    */
    private static $wsdl = array(
        'generic'  => '/api/generic?wsdl',
        'planning' => '/api/planning?wsdl',
        'garage'   => '/api/garage2?wsdl',
        'order'    => '/api/order?wsdl',
        'point'    => '/api/point?wsdl',
        'resource' => '/api/resource?wsdl',
        'route'    => '/api/route?wsdl',
    );
    
    
    
    protected $errors  = array(); // [$err_code =>$err_text]
    /**
     *
     * @var int
     */
    protected $result_code;  // Код результата. 0 = все ок.
    /**
     * @var Astra_Result
     */
    protected $result;  // Ответ на последний запрос
    /**
     * @var Astra_Response
     */
    protected $response;
    /**
     *
     * @var SoapClient
     */
    private $client;
    
    //private $apikey      = '81edd896-5312-4db9-9943-318b85715ca5';
    private static $domain = '213.247.143.81';
    private static $apikey = 'ApiKey';
    private static $name   = 'astra-test';
    private static $port   = '9090';
    /*
    private $login         = 'foxtrot';
    private $login_test    = 'mladweb';
    private $password      = 'Gr2UjKjEbGB0e0k4';
    private $password_test = 'mladenec';
    */
    private $connect_params = array(
                'trace'         => true,
                'classmap'      => array(
                    'addPointResponse'      => 'Astra_Response',
                    'addPointExtResponse'   => 'Astra_Response',
                    'addPointsResponse'     => 'Astra_Response',
                    'addPointsExtResponse'  => 'Astra_Response',
                    'addOrderResponse'      => 'Astra_Response',
                    'addOrderExtResponse'   => 'Astra_Response',
                    'addOrdersResponse'     => 'Astra_Response',
                    'addOrdersExtResponse'  => 'Astra_Response',
                    'getRouteListResponse'  => 'Astra_Response',
                    'routeItem'             => 'Astra_Route_Item',
                    'routeOrder'            => 'Astra_Route_Order',
                    'response'              => 'Astra_Response',
                    'result'                => 'Astra_Result',
                    'route'                 => 'Astra_Result_Route',
                    'day'                   => 'Astra_Date',
                    'error'                 => 'Astra_Error'
                    )
                );
    
    private static $test = FALSE;
    
    
    
    public static function params($name, $port, $key, $domain = '') {
        self::$name   = $name;
        self::$port   = $port;
        self::$apikey = $key;
        if ( ! empty($domain)) self::$domain = $domain;
    }
    
    public function __construct($type) {
        if ( ! empty(self::$wsdl[$type])) {
            $wsdl = 'http://' . self::$domain . ':' . self::$port . '/' . self::$name . self::$wsdl[$type];
        }
        
        /*
        $wsdl = self::$test ? $this->wsdl_test : $this->wsdl;
        if ( self::$test) {
            $this->connect_params['login']    = $this->login_test;
            $this->connect_params['password'] = $this->password_test;
        } else {
            $this->connect_params['login']    = $this->login;
            $this->connect_params['password'] = $this->password;
        }
        */
        echo("\nWSDL: " . $wsdl);
        
        if ( ! empty($wsdl)) {
            $this->client = new SoapClient($wsdl, $this->connect_params);
        } else {
            throw new Exception;
        }
    }
    
    public function c($function_name,$parameters = array())
    {
        $parameters['key'] = self::$apikey;
       
        $this->response = $this->client->__soapCall($function_name, array('parameters'=>$parameters));
        
        $this->log();

        if ( ! ($this->response instanceof Astra_Response))  // Error!
        {
            Log::instance()->add(Log::ERROR, 'Wrong SOAP response, function: ' . $function_name);
            return FALSE;
        }
        return $this->response->get_result();
    }
    
    protected function log($file_suffix = FALSE) {
        
        $now = new DateTime();
        $log_dir = APPPATH.'logs/' . date_format($now, 'Y/m/d') . '/astra_soap';
        if ( ! file_exists($log_dir)) mkdir($log_dir, 0777, TRUE);

        $file_prefix = $log_dir.'/' . date_format($now, 'H_i_s') . ($file_suffix ? '_'.$file_suffix : '').'.xml';
        $file_name = $file_prefix;
        $i = 0;
        while(file_exists($file_name)) {
            $file_name = $file_prefix.(++$i);
        }
        file_put_contents($file_name, $this->get_request());
        file_put_contents($file_name, $this->get_response(),FILE_APPEND);
        
    }
    
    public function get_request() {
        return $this->client->__getLastRequest();
    }
    
    public function get_response() {
        return $this->client->__getLastResponse();
    }
    public function get_types() {
        return $this->client->__getTypes();
    }
    /**
     * 
     * @param Model $model 
     * @param array $map [$array_key=>$model_property,...]
     */
    protected function model_to_array($model,$map) {
        $array = array();
        foreach($map as $array_key=>$model_property) {
            $array[$array_key] = $model->$model_property;
        }
        return $array;
    }
    /**
     * 
     * @param array $array
     * @param Model $model 
     * @param array $map [$array_key=>$model_property,...]
     */
    protected function array_to_model($array,$model,$map) {
        
        foreach($map as $array_key=>$model_property) {
            if(isset($array[$array_key])) {
                $model->$model_property = $array[$array_key];
            }
        }
        return $model;
    }
}