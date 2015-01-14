<?php
/**
 * Класс для работы с апи robo-media https://robo-media.ru/index.php?route=api_help/products
 */

class Robomedia {

    const URL = 'https://robo-media.ru/index.php?route=api/';
    const LOGIN = 'mladenec-group';
    const PWD = '60c19fe399d5203033725ededb25c6fc9e2b2a7eade27c5f99a1cbeef1db3480';

    // запросы по REST API
    public static function request($name, $type = 'GET', $data = NULL)
    {
        $ch = curl_init();
        $qs = self::URL.$name;
        $body = '';

        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_USERPWD, self::LOGIN.':'.self::PWD);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        switch($type) {
            case 'POST': // create
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body = json_encode($data));
                break;
            case 'GET': // read
                $qs .= '&'.http_build_query($data);
                break;
            case 'PUT': // update
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
                $qs .= '&'.key($data).'='.current($data); // first field must be id field
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body = json_encode(array_slice($data, 1, NULL, TRUE)));
                break;
            case 'DELETE': // delete
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
                $qs .= '&'.http_build_query($data); // first field must be id field
                break;
        }
        $headers[] = 'Content-Length: '.strlen($body);
        curl_setopt($ch, CURLOPT_URL, $qs);

        if( ! $result = curl_exec($ch))
        {
            trigger_error(curl_error($ch));
        }
        return $result;
    }
}