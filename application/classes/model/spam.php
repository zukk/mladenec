<?php
class Model_Spam extends ORM {

    const STATUS_EMPTY  = 0;
    const STATUS_NEW    = 1;
    const STATUS_READY  = 2;
    const STATUS_PROCEED  = 3;
    const STATUS_DONE  = 4;

    private $_url_counter = 0;

    protected $_table_name = 'z_spam';

    protected $_table_columns = array(
        'id' => '', 'name' => '', 
        'from' => 0, // Время когда начинать рассылать
        'status' => '',
    );

    /**
     * @return array
     */
    public function rules()
    {
        return array(
            'name' => array(
                array('not_empty'),
            ),
        );
    }

    /**
     * Расставляем хвосты гугла всем ссылкам
     * http://www.mladenec-shop.ru/?utm_source=Mspam60&utm_medium=cpc&utm_content=1&utm_campaign=23February
     */
    private function _tail($url)
    {
        $this->_url_counter++;
        $parsed = parse_url($url);
        $qs = [];
        if ( ! empty($parsed['query'])) {
            parse_str($parsed['query'], $qs);
        }
        if ( empty($qs['utm_source'])) $qs['utm_source'] = 'Mspam'.$this->id;
        if ( empty($qs['utm_medium'])) $qs['utm_medium'] = 'email';
        if ( empty($qs['utm_content'])) $qs['utm_content'] = $this->_url_counter;
        if ( empty($qs['utm_campaign'])) $qs['utm_campaign'] = '';

        return ($parsed['scheme'] ? $parsed['scheme'] .'://' : '')
            . ($parsed['host'] ? $parsed['host'] : '')
            . ($parsed['port'] ? ':'.$parsed['port'] : '')
            . ($parsed['path'] ? ':'.$parsed['path'] : '')
            . ( '?' . http_build_query($qs));
    }

    /**
     * Получить статус рассылки
     * @return mixed
     */
    public function status()
    {
        if (empty($this->status)) $this->status = 0;
        $status = array(
            self::STATUS_EMPTY => 'Пустая',
            self::STATUS_NEW => 'Новая',
            self::STATUS_READY => 'Готовность',
            self::STATUS_PROCEED => 'Рассылается',
            self::STATUS_DONE => 'Разослано',
        );
        return $status[$this->status];
    }

    /**
     * сохранение рассылки в админке - сохранение вариантов ответа
     */
    public function admin_save()
    {
        $request = Request::current();
        $messages = array();
        if (($this->status < self::STATUS_PROCEED) AND ( ! empty($_FILES['zip']))
            AND Upload::valid($_FILES['zip']) AND Upload::not_empty($_FILES['zip']))
        {
            $zip = new ZipArchive;
            if ($zip->open($_FILES['zip']['tmp_name']) === TRUE) { // загрузка папки с рассылкой
                $mail_dir = Upload::$default_directory.'/mail/'.$this->id;
                if ( ! file_exists($mail_dir)) {
                    mkdir($mail_dir, 0777, true);
                }
                $zip->extractTo($mail_dir);
                $zip->close();
                $this->status = Model_Spam::STATUS_NEW;

                $this->save();

                $txt = file_get_contents($mail_dir.'index.html');
                $tailed = preg_replace_callback('~href="([^"]+)"~isu', $this->_tail('$1'), $txt);
                file_put_contents($mail_dir.'index.html', $tailed);

                Model_History::log('spam', $this->id, 'zip uploaded');
            }
        }
        if ($request->post('mail') AND ($this->status >= self::STATUS_NEW) AND ($this->status < self::STATUS_PROCEED)) { // тестовое письмо
            $mail = new Mail();
            $mail_dir = Upload::$default_directory.'/mail/'.$this->id.'/';
            $text = file_get_contents($mail_dir.'index.html');
            $tos = explode(',', $request->post('mail')); // кому
            foreach($tos as $to) {
                $mail->setHTML(Txt::spam($text, $to), FALSE, $mail_dir); // разные ссылки на отписку
                $mail->send($to, $this->name);
                $messages['messages'][] = 'Тестовое письмо выслано на адрес '.$to;
            }

            $this->status = self::STATUS_READY;
            $this->save();

            Model_History::log('spam', $this->id, 'test letter sent', $tos);
        }

        if ($request->post('spamit') AND ($this->status == self::STATUS_READY)) { // запуск рассылки

            // проставим получателей
            DB::query(Database::INSERT,
                "INSERT IGNORE INTO z_spam_user (spam_id, mail) "
                ."SELECT ".$this->id.", email FROM z_user WHERE sub = 1"
            )->execute();

            $this->status = self::STATUS_PROCEED;
            $this->save();

            Model_History::log('spam', $this->id, 'spam started');
        }
        return $messages;
    }

    /**
     * Добавим причину отписки
     * @param $reason
     */
    public static function why($reason)
    {
        $ins = DB::insert('z_spam_why', array('why'))->values(array($reason)).' ON DUPLICATE KEY UPDATE qty = qty + 1';
        DB::query(Database::INSERT, $ins)->execute();
    }
}
