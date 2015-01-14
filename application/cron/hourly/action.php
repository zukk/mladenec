<?php
/**
 * Включение, выключение и уведомления об акциях.
 */
require('../../../www/preload.php');
$lock_file = APPPATH.'cache/actions_report_on';
if (file_exists($lock_file)) exit('Already running, lock file found at '.$lock_file);
touch($lock_file);

$current_user = Model_User::i_robot();

Model_Action::activator($current_user);

// отключим просроченные акции от проверки
DB::update('z_action')
    ->set(['allowed' => 0])
    ->where('allowed', '=', 1)
    ->where('to', 'IS NOT', DB::expr('NULL'))
    ->where('to', '<', DB::expr('NOW()'))
    ->execute();

unlink($lock_file);