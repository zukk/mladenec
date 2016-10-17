<?php
// проверка отправляются ли СМС

require('../../../www/preload.php');

$lock_file = APPPATH.'cache/sms_check_on';

touch($lock_file);

$ok = Model_Sms::sending_ok();

if ( ! $ok)
{
    if ($addr = Conf::instance()->mail_sms_warning)
    {
        echo('Sending!');
        Mail::htmlsend('simple', 
                array(
                    'header' => 'Внимание! Проверь отправку СМС!',
                    'message'=>'Обнаружена проблема в отправке СМС. <br />'
                    . '<a href="http://mladenec-shop.ru/od-men/sms" target="_blank">Проверить.</a>.'
                    ),
                $addr,
                'Ошибка отправки СМС'
                );
    }
}

unlink($lock_file);