<?php
/**
 * Уведомления об окончании подарков.
 */
require('../../../www/preload.php');
$lock_file = APPPATH.'cache/presents_report_on';
if (file_exists($lock_file)) exit('Already running, lock file found at ' . $lock_file);
touch($lock_file);

$current_user = Model_User::i_robot();

$actions = ORM::factory('action')
        ->where('type', 'IN', array(Model_Action::TYPE_GIFT_QTY, Model_Action::TYPE_GIFT_SUM))
        ->where('active', '=', 1)
        ->find_all()->as_array('id');

$present_ids = array();
foreach($actions as $a) {
    $p_ids = DB::select()->from('z_action_present')
            ->join('z_good')->on('z_action_present.good_id', '=', 'z_good.id')
            ->join('z_action')->on('z_action_present.action_id', '=', 'z_action.id')
            ->where('action_id', '=', $a->pk())
            ->where('z_good.qty', '<', DB::expr('`warn_on_qty`'))
            ->where('z_good.qty', '!=', DB::expr('`qty_reported`'))
            ->execute()->as_array('good_id', 'action_id');
    
    foreach ($p_ids as $good_id=>$action_id) {
        $present_ids[$good_id] = $action_id;
    }
}

if ( ! empty($present_ids)) {
    
    $goods = ORM::factory('good')->where('id', 'IN', array_keys($present_ids))->find_all()->as_array('id');
    
    $emails = Conf::instance()->mail_present;
    
    if ( ! empty($emails)) {
        Mail::htmlsend('ending_gifts', array(
            'presents' => $present_ids, 
            'actions'  => $actions, 
            'goods'    => $goods
                ), $emails, 'Заканчиваются подарки!');
    }

    $sms = Conf::instance()->sms_present;
    $sms_action_ids = array();
    foreach($goods as $g) {
        DB::update('z_action_present')
                ->set(array('qty_reported' => $g->qty))
                ->where('good_id', '=', $g->id)
                ->execute();
        if(0 == $g->qty) {
            $sms_action_ids[] = $present_ids[$g->pk()];
        }
    }

    if( ! empty($sms) AND ! empty($sms_action_ids)) {
        Model_Sms::to_queue($sms, 'Кончились подарки по акциям: ' . implode(',',$sms_action_ids));
    }
}
unlink($lock_file);