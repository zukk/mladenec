<?php
/**
 * Включение, выключение и уведомления об акциях.
 */
require('../../../www/preload.php');

$current_user = Model_User::i_robot();

Model_Action::activator($current_user);

// отключим просроченные акции от проверки
DB::update('z_action')
    ->set(['allowed' => 0])
    ->where('allowed', '=', 1)
    ->where('to', 'IS NOT', DB::expr('NULL'))
    ->where('to', '<', DB::expr('NOW()'))
    ->execute();