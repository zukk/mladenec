<?php
/**
 * Created by PhpStorm.
 * User: mit06
 * Date: 07.04.2015
 * Time: 14:15
 */

require('../www/preload.php');

$sms = new Model_Sms(
    [
        'user_id'=>'1',
        'order_id'=>'1',
        'phone' => '+79262330800',
        'text' => 'Это тест',
    ]
);
$sms->send();