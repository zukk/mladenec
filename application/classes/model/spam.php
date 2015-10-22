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
     * Расставляем хвосты всем ссылкам
     * расставляет если нету параметры в урл типа utm_source
     * а также готовит место для RR_SETEMAIL если включен RR
     * http://www.mladenec-shop.ru/?utm_source=Mspam60&utm_medium=cpc&utm_content=1&utm_campaign=23February
     */
    private function _tail($matches)
    {
        $url = $matches[1];
        $this->_url_counter++;
        $parsed = parse_url($url);
        $qs = [];
        if ( ! empty($parsed['query'])) {
            parse_str($parsed['query'], $qs);
        }
        if ( empty($qs['utm_source'])) $qs['utm_source'] = 'letter'.$this->id;
        if ( empty($qs['utm_medium'])) $qs['utm_medium'] = 'email';
        if ( empty($qs['utm_content'])) $qs['utm_content'] = $this->_url_counter;
        if ( empty($qs['utm_campaign'])) $qs['utm_campaign'] = '';
        $qs = array_filter($qs);
        if (Conf::instance()->rr_enabled) $qs['rr_setemail'] = 'RR_SETEMAIL';

        return 'href="'.
              ( ! empty($parsed['scheme']) ? $parsed['scheme'] .'://' : '')
            . ( ! empty($parsed['host']) ? $parsed['host'] : '')
            . ( ! empty($parsed['port']) ? ':'.$parsed['port'] : '')
            . ( ! empty($parsed['path']) ? $parsed['path'] : '/')
            . ( '?' . http_build_query($qs))
            . '"';
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
        $messages = [];

        if (($this->status < self::STATUS_PROCEED) AND ( ! empty($_FILES['zip']))
            AND Upload::valid($_FILES['zip']) AND Upload::not_empty($_FILES['zip']))
        {
            $zip = new ZipArchive;
            if ($zip->open($_FILES['zip']['tmp_name']) === TRUE) { // загрузка папки с рассылкой
                $mail_dir = Upload::$default_directory.'/mail/'.$this->id;
                if ( ! file_exists($mail_dir)) {
                    mkdir($mail_dir, 0777, TRUE);
                }
                $zip->extractTo($mail_dir);
                $zip->close();
                $this->status = Model_Spam::STATUS_NEW;

                $this->save();

                $txt = file_get_contents($mail_dir.'/index.html');
                $tailed = preg_replace_callback('~href="([^"]+)"~isu', [$this, '_tail'], $txt);
                file_put_contents($mail_dir.'/index.html', $tailed);

                Model_History::log('spam', $this->id, 'zip uploaded');
            }
        }
        if (($this->status < self::STATUS_PROCEED) AND ( ! empty($_FILES['excel']))
            AND Upload::valid($_FILES['excel']) AND Upload::not_empty($_FILES['excel'])) {

            include(APPPATH.'classes/PHPExcel/IOFactory.php');

            $excel = PHPExcel_IOFactory::load($_FILES['excel']['tmp_name']);
            $sheet = $excel->getActiveSheet();
            $column = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
            $row = $sheet->getHighestRow();

            $mails = [];
            for($x = 0; $x <= $column; $x++) {
                for($y = 1; $y <= $row; $y++) {
                    $data = strval($sheet->getCellByColumnAndRow($x, $y)->getValue());
                    if (Valid::email($data)) {
                        $mails[$data] = $data;
                    }
                }
            }
            $total = count($mails);

            // отсеем почты тех кто отписан от рассылок
            $no_spam = DB::select("email")
                ->from("z_user")
                ->where('sub', '=', 0)
                ->where('email', 'IN', $mails)
                ->execute()
                ->as_array('email', 'email');

            $to_add = array_diff($mails, $no_spam);

            $rejected = count($no_spam);

            $ins = DB::insert('z_spam_user', ['spam_id', 'mail']);
            foreach($to_add as $mail) {
                $ins->values([$this->id, $mail]);
            }
            list($ids, $added) = DB::query(Database::INSERT, 'INSERT IGNORE '.substr($ins, 6))->execute();

            $messages['messages'][] = "Загружен Excel. Адресов всего $total, добавлено $added, отклонено $rejected";

            Model_History::log('spam', $this->id, 'recipients added '.$added);
        }

        if (($this->status < self::STATUS_PROCEED) AND ($request->post('clear_list') == 'do')) {

            DB::delete("z_spam_user")
                ->where('spam_id', '=', $this->id)
                ->execute();

            Model_History::log('spam', $this->id, 'reset recipients list');
        }

        if ($request->post('mail') AND ($this->status >= self::STATUS_NEW) AND ($this->status < self::STATUS_PROCEED)) { // тестовое письмо
            $mail = new Mail();
            $mail_dir = Upload::$default_directory.'/mail/'.$this->id.'/';
            $text = file_get_contents($mail_dir.'index.html');
            $tos = explode(',', $request->post('mail')); // кому
            foreach($tos as $to) {
                $to = trim($to);
                $mail->setHTML(Txt::spam($text, $to), FALSE, $mail_dir); // разные ссылки на отписку
                $mail->send($to, $this->name);
                $messages['messages'][] = 'Тестовое письмо выслано на адрес '.$to;
            }

            $this->status = self::STATUS_READY;
            $this->save();

            Model_History::log('spam', $this->id, 'test letter sent', $tos);
        }

        if ($request->post('spamit') AND ($this->status == self::STATUS_READY)) { // запуск рассылки

            $recipients = $this->recipients();

            if (empty($recipients)) {
                // проставим получателями всех подписчиков
                DB::query(Database::INSERT,
                    "INSERT IGNORE INTO z_spam_user (spam_id, mail) "
                    . "SELECT " . $this->id . ", email FROM z_user WHERE sub = 1"
                )->execute();
            }

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

    /**
     * Список получателей
     */
    public function recipients()
    {
        return DB::select()
            ->from('z_spam_user')
            ->where('spam_id', '=', $this->id)
            ->execute()
            ->as_array('mail', 'status');
    }
}
