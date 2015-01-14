<?php

/**
 * Рассылка писем из очереди
 */

require('../../../www/preload.php');

$lock_file = APPPATH.'cache/spam_on';

if (file_exists($lock_file)) exit('Already running, lock file found at '.$lock_file);

touch($lock_file);

$spam = ORM::factory('spam')
    ->where('status', '=', Model_Spam::STATUS_PROCEED)
    ->where_open()
    ->where('from', 'IS', NULL)
    ->or_where('from', '<=' ,date('Y-m-d G:i:00'))
    ->where_close()
    ->find(); // рассылка в статусе - рассылается

if ( ! empty($spam->id)) { // есть такая рассылка

    $mail = new Mail();
    $img_dir = Upload::$default_directory.'/mail/'.$spam->id.'/';
    $mail_dir = APPPATH.'../www/'.$img_dir; // откуда контент
    $text = file_get_contents($mail_dir.'index.html'); // текст для всех один, но разные ссылки на отписку


    do { // пока есть получатели, делаем

        $tos = DB::select('mail') // список получателей, по 100
            ->from('z_spam_user')
            ->where('spam_id', '=', $spam->id)
            ->where('status', '=', 0)
//            ->where('mail', 'LIKE', '%zukk%')
            ->limit(100)
            ->execute()
            ->as_array('mail');

        foreach($tos as $to) {
            $mail->setHTML(Txt::spam($text, $to['mail']), FALSE, $img_dir); // разные ссылки на отписку
            $mail->send($to['mail'], $spam->name);

            DB::update('z_spam_user')
                ->set(array('status' => 1))
                ->where('spam_id', '=', $spam->id)
                ->where('mail', '=', $to['mail'])
                ->where('status', '=', 0)
                ->limit(1)
                ->execute();
        }
        sleep(1); // подождём секунду

    } while(count($tos) > 0);

    $spam->status = Model_Spam::STATUS_DONE;
    $spam->save();
}

unlink($lock_file);


