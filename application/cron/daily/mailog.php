<?php

require('../../../www/preload.php');

Model_User::i_robot();

/**
 * Рассылка сообщений, накопленных для майлога, о различных событиях
 */

$lock_file = APPPATH.'cache/mailog_report_on';

if (file_exists($lock_file)) exit('Already running, lock file found at '.$lock_file);

touch($lock_file);

$mailogs = ORM::factory('mailog')
        ->where('date', '<=', date('Y-m-d'))
        ->where('sent', '=', 0)
        ->find_all()
        ->as_array();

$mailogs_by_code = array();
foreach ($mailogs as $ml) $mailogs_by_code[$ml->code][] = $ml;

// куда о чем посылать письма
$to = array(
    'good_change' => Conf::instance()->mail_good,
    'good_change_fransh' => Conf::instance()->mail_fransh

);

foreach ($mailogs_by_code as $code => $mbc) {
    if (empty($to[$code])) continue;

    $template = 'mail/mailog/' . $code;
    if (file_exists(APPPATH . '/views/' . $template . '.tpl' )) {

        $letter = new Mail();
        $letter->setHTML(View::factory('smarty:' . $template, array('logs' => $mbc))->render());


        $arr = array();
        $arr[] = array(
                0 => 'Дата',
                1 => 'Время',
                2 => 'Название',
                3 => 'Артикул',
                4 => 'Штрихкод',
                5 => 'Было',
                6 => 'Стало',
                7 => '-',
                8 => 'Комментарий',
            );

        foreach($mbc as $ml_obj) {
            $a = array(
                'date' => $ml_obj->date,
                'time' => $ml_obj->time
            );
            $tmp_ml = $ml_obj->get_data();
            foreach($tmp_ml as $key => $tmp_data) {
                $a[$key] = $tmp_data;
            }
            $arr[] = $a;
        }

        $xls = Txt::array_to_xls($arr);

        $letter->addAttachment($xls, 'good_change.xls', 'application/octet-stream');
        $letter->send($to[$code], 'Изменения цен и новые товары!');

    } else {
        Mail::htmlsend('mailog/default', array('logs' => $mbc), $to[$code], 'Отчет ' . $code . '!');
    }
    foreach ($mbc as $mbci) {
        $mbci->sent = 1;
        $mbci->save();
    }
    Log::instance()->add(Log::INFO, 'Sent log for code ' . $code.' to '.$to);
}

unlink($lock_file);