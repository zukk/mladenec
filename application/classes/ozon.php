<?php

/**
 * Class Ozon
 * Апи обмена с озоном ?
 */

class Ozon {

    private $_token = '';

    const X_ApplicationId = 'Mladenec';
    const Secret_Key = 'DEB0D523-3E10-4865-9D5F-AB651DC5CAE9';
    const URL = 'https://api.ozon.ru'; // '/auth/token/merchants';

//    const url = 'http://ows.ozon.ru/';

    /**
     * Запросы к API - через CURL
     * @param $url
     * @param bool|FALSE $post
     * @return bool|mixed
     */
    private function _query($url, $post = FALSE)
    {
        $curl = new Curl();

        $header = [
            'x-applicationid:'.self::X_ApplicationId,
            'x-ApiVersion: 0.1',
            'accept:application/json',
        ];

        if ($url == '/auth/token/merchants') { // получение токена
            array_push($header, 'x-sign:'.hash_hmac('sha1', $url, self::Secret_Key));
        } else { // все остальное
            array_push($header, 'x-token:'.$this->_token);
        }

        $resp = iconv('cp1251', 'utf8', $curl->get_url(self::URL.$url, $post, $header)); // озон работает в cp1251
        $decode = json_decode($resp, TRUE);
        if (empty($decode)) {
            Log::instance()->add(Log::WARNING, 'Cannot decode OZON response: '.var_dump($resp, TRUE));
            return FALSE;

        } elseif ( ! empty($decode['responseStatus']['errorCode'])) {
            Log::instance()->add(Log::WARNING, 'OZON api error: '.$decode['responseStatus']['errorCode'].$decode['responseStatus']['message']);
            return FALSE;
        }
        return $decode;
    }

    /**
     * Конструктор - получаем токен
     */
    function __construct()
    {
        $token = $this->_query('/auth/token/merchants');

        if (empty($token['token'])) {

            Log::instance()->add(Log::WARNING, 'Cannot get OZON token'.$token);

            return FALSE;

        } else {

            $this->_token = $token['token'];
        }
    }

    /**
     * Получить разрешенные типы товаров
     * @param bool|FALSE $all
     * @return bool|mixed
     */
    function get_types($all = FALSE)
    {
        $types = $this->_query('/merchants/products/types'.($all ? '/all' : ''));

        if ( ! empty($types['ProductTypes'])) {

            $ins = DB::insert('ozon_types')->columns(['id', 'name', 'path_name', 'template_id']);
            foreach($types['ProductTypes'] as $type) {

                $ins->values(
                    [
                        'id' => $type['ProductTypeId'],
                        'name' => $type['Name'],
                        'path_name' => $type['PathName'],
                        'template_id' => $type['TemplateId']
                    ]
                );
            }
            $ins .= ' ON DUPLICATE KEY UPDATE name = VALUES(name), path_name = VALUES(path_name), template_id = VALUES(template_id)';
            DB::query(Database::INSERT, $ins)->execute();
        }
        return TRUE;
    }
}
