<?php
/**
 * Created by PhpStorm.
 * User: m.zukk
 * Date: 02.12.15
 * Time: 23:19
 */
class GetResponse {
    const CAMPAIGN_ID = 'j';
    const GR_API_KEY = '515371d4780de0ed844e0ddd5079929a';
    const GR_API_URL = 'http://api.getresponse360.pl/mladenec';
    private $_client;

    function __construct()
    {
        $this->_client = new jsonRPCClient(self::GR_API_URL);
    }

    /**
     * отписываем мыло
     */
    function unsubscribe($mail)
    {
        if ( ! Valid::email($mail)) return FALSE;
        try {
            $exist = $this->_client->get_contacts(
                self::GR_API_KEY,
                ['email' => ['EQUALS' => $mail]]
            );

            if ( ! empty($exist)) {
                $arr = array_keys($exist);
                $contact_id = array_shift($arr);

                $res = $this->_client->delete_contact(
                    self::GR_API_KEY,
                    ['contact' => $contact_id]
                );
                if ($res['deleted'] == 1) return TRUE;
            }
            return FALSE;

        } catch (RuntimeException $e) {
            Log::instance()->add(Log::ERROR, 'GetResponse communication error on: ' . $e->getLine() . ' line ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Update инфы по контакту/создание нового контакта
     * @param $name
     * @param $email
     * @param $customs - настраиваемые поля
     * @return bool
     */
    function upload($name, $email, $customs)
    {
        if ( ! Valid::email($email)) return FALSE;
        try {
            $exist = $this->_client->get_contacts(
                self::GR_API_KEY,
                [
                    'email'=> [ 'EQUALS' => $email ]
                ]
            );

            if ( ! empty($exist) ) {

                $arr = array_keys($exist);
                $contact_id = array_shift($arr);

                $this->_client->set_contact_customs(
                    self::GR_API_KEY,
                    [
                        'contact' => $contact_id,
                        'customs' => $customs
                    ]
                );
                return TRUE;

            } else {

                $result = $this->_client->add_contact(
                    self::GR_API_KEY,
                    [
                        'campaign'  => self::CAMPAIGN_ID,
                        'name'      => $name,
                        'email'     => $email,
                        'customs' => $customs
                    ]
                );
                print_r($result);
                return TRUE;
            }
        } catch (RuntimeException $e) {

            Log::instance()->add(Log::ERROR, 'GetResponse communication error on: ' . $e->getLine() . ' line ' . $e->getMessage());
            return FALSE;
        }
    }
}