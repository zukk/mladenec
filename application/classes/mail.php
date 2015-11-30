<?php

class Mail {

    const MAIL_PREFIX = 'Младенец.РУ';
    const IMAGE_DIR = '';
    const MAIL_FROM = 'no-reply@mladenec-shop.ru';
    const MAX_ATTACHMENT_SIZE = 2400000;

    protected $smtp = array(
        'host' => 'ssl://smtp.yandex.ru',
        'port' => 465,
        'login' => self::MAIL_FROM,
        'password' => 'fg6gr84gh',
        'from' => self::MAIL_FROM
    );

    public $smtp_error = FALSE;
    private $smtp_conn = FALSE;
    private $multipart = FALSE;

    var $boundary 	= ""; // разделитель
    var $message	= array(); // массив куда будем сообщение собирать
    var $charset    = "utf-8";
    private $header = "";

    private static $allowed_extensions = array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'rtf', 'txt', 'csv',
        'jpg', 'jpeg', 'gif', 'png', 'tiff', 'wbmp', 'zip', 'gz', 'gzip', 'rar');
    
    function __construct($charset = false)
    {
        if ($charset !== false) $this->charset = $charset;
        $this->boundary = md5(microtime());
    }

    public static function site() {
        $site = Kohana::$environment === Kohana::PRODUCTION ? 'http://www.mladenec-shop.ru' : 'http://test.mladenecshop.ru'; // NO LAST / here!
        if ( ! empty($_SERVER['SERVER_NAME'])) {
            $site = 'http://'.$_SERVER['SERVER_NAME'];
        }
        return $site;
    }

    public function setText($text)
    {
        $this->message['text'] = chunk_split(base64_encode($text));;
    }

    /**
     * Выставить письмо html-контент
     * @param $html
     * @param bool $use_layout - заворачивать всё в общий шаблон?
     * @param string $img_dir
     */
    public function setHTML($html, $use_layout = TRUE, $img_dir = '')
    {
        $site = self::site();
        if ($use_layout === TRUE) {
            $html = View::factory('smarty:mail', array('mail' => $html, 'site' => $site))->render();
        }

        if (preg_match_all('~src="(.*?)"~', $html, $matches)) {
            $srcs = array_unique(array_filter($matches[1]));

            foreach($srcs as $src) { // проставим полный урл для картинок
                $file = (strpos($src, 'http://') !== FALSE) ? $src : $site.'/'.$img_dir.$src;
                $html = str_replace('src="'.$src.'"', 'src="'.$file.'"', $html);
            }
        }

        $this->message['html'] = chunk_split(base64_encode($html));

        $txt = preg_replace('~</?[a-z]+.*?>~isu', '', $html);
        $txt = preg_replace("~\n\s+~isu", "\n", html_entity_decode($txt));
        $txt = preg_replace("~\n\n+~", "\n", $txt);

        $this->setText($txt);
    }

    public static function htmlsend($tmpl, $params, $to, $subject)
    {
        $mail = new self();
        $mail->setHTML(View::factory('smarty:mail/'.$tmpl, $params + array('site' => self::site()))->render());

        if ($tmpl == 'order') { // шлём копии писем через smtp - а то яндекс нас забанил за слишком много писем
            if ( ! $mail->send_smtp('zakaz@mladenec-shop.ru,1creport@mladenec-shop.ru', 'Младенец.РУ: '.$subject)) {
                mail('m.zukk@ya.ru', 'SMTP ERROR sending copy', $mail->smtp_error);
            }
        }

        return $mail->send_smtp($to, 'Младенец.РУ: '.$subject); // через smtp - поменять как нас уберут из спам-листов
    }

    public function attachUploaded($file)
    {
        if (empty($_FILES[$file]) || ! Upload::valid($_FILES[$file])) return FALSE;
        
        if ( ! empty($_FILES[$file]['size']) AND $_FILES[$file]['size'] > self::MAX_ATTACHMENT_SIZE ) {
            Log::instance()->add(Log::INFO, 'Trying to send too big file');
            return FALSE;
        }
        
        $filename = $_FILES[$file]['name'];
        $pathinfo = pathinfo($filename);
        
        $extension = '';
        
        if ( ! empty($pathinfo['extension'])) {
            $extension = strtolower ($pathinfo['extension']);
        }
        
        if ( FALSE === array_search($extension, self::$allowed_extensions)) {
            Log::instance()->add(Log::INFO, 'Trying to send file with wrong extension');
            return FALSE;
        }
        
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $mime = 'image/jpeg';
                break;
            case 'gif':
                $mime = 'image/gif';
                break;
            case 'png':
                $mime = 'image/png';
                break;
            case 'txt':
            case 'csv':
                $mime = 'text/plain';
                break;
            default:
                $mime = 'application/octet-stream';
        }
        
        $tmp_filename = $_FILES[$file]['tmp_name'];
        
        if (file_exists($tmp_filename)) {
            $content = file_get_contents($tmp_filename);
        } else return FALSE;
        
        $this->addAttachment($content, $filename, $mime);
        
    }
    
    public function addAttachment($content, $filename, $mime_type = "application/octet-stream", $inline = FALSE)
    {
        $this->message['attach'][] = array(	'content'	=> chunk_split(base64_encode($content)),
            'filename'  => $filename,
            'mime'      => $mime_type,
            'inline'    => $inline
        );
    }

    public function getMessage()
    {
        $parts = array();

        // соберём текстовую часть
        if ( ! empty($this->message['text'])) {
            $parts['text'] = array(
                'header' => "Content-type: text/plain; charset=".$this->charset."\r\n"
                            ."Content-Transfer-Encoding: base64\r\n",
                'content' => $this->message['text'],
            );
        }

        // соберём html часть
        if ( ! empty($this->message['html'])) {
            $parts['html'] = array(
                'header' => "Content-type: text/html; charset=".$this->charset."\r\n"
                            ."Content-Transfer-Encoding: base64\r\n",
                'content' => $this->message['html'],
            );
        }

        // соберём аттачменты (сразу с заголовками и разделителем)
        if ( ! empty($this->message['attach'])) {
            foreach ($this->message['attach'] as $a) {
                $parts[] =  array(
                    'header' => "Content-type: ".$a['mime']."; name=".$a['filename']."\r\n"
                                ."Content-Transfer-Encoding: base64\r\n"
                                .($a['inline'] ? "Content-ID: <".$a['filename'].">\r\n" : "")
                                ."Content-Disposition: ".($a['inline'] ? 'inline' : 'attachment')."; filename=".$a['filename']."\r\n",
                    'content' => $a['content'],
                );
            }
        }

        if (count($parts) > 1)  {
            $this->multipart = TRUE;
            $this->header = "Content-type: multipart/related; boundary=".$this->boundary."\r\n";
            $msg = "This is a multipart message in MIME format.\r\n\r\n";

            if ($parts['html'] AND $parts['text']) { // альтернатива - объединим в кусок
                $bound = md5($this->boundary);
                $parts['html'] = array(
                    'header' => "Content-type: multipart/alternative; boundary=".$bound."\r\n",
                    'content' => '--'.$bound."\r\n".
                                implode("\r\n", $parts['text'])."\r\n\r\n".
                                '--'.$bound."\r\n".
                                implode("\r\n", $parts['html'])."\r\n\r\n".
                                '--'.$bound."--\r\n"
                );
                unset($parts['text']);
            }

            foreach($parts as $part) {
                $msg .= '--'.$this->boundary."\r\n".
                    implode("\r\n", $part)."\r\n\r\n";
            }
            $msg .= '--'.$this->boundary."--\r\n"; // last bound

        } else { // письмо из одного элемента
            $part = next($parts);
            $this->header = $part['header'];
            $msg = $part['content'];
        }

        return $msg;
    }

    public function getSubject($subject)
    {
        return '=?'.$this->charset.'?B?'.base64_encode($subject).'?=';
    }

    public function send($to, $subject, $add_header = NULL)
    {
        $message = $this->getMessage();

        $add_header = ($add_header ? trim($add_header)."\r\n" : '')
            .'From:'.$this->getSubject(self::MAIL_PREFIX).' <'.self::MAIL_FROM.'>'."\r\n"
            .$this->header."\r\n";

        if (Kohana::$environment === Kohana::DEVELOPMENT)
        {
            $subject = 'Test: ' . $subject . ', to: ' . $to;
            $to = 'm.zukk@ya.ru, v.vinnikov@toogarin.ru';
        }
        
        return mail($to, $this->getSubject($subject), $message, $add_header, '-f'.self::MAIL_FROM.' -F'.self::MAIL_PREFIX);
    }

    /* read smtpl response */
    private function get_smtp_response()
    {
        $data = "";
        while ($str = fgets($this->smtp_conn, 515)) {
            $data .= $str;
            if (substr($str,3,1) == " ") { break; }
        }
        //echo $data."\n";
        return $data;
    }

    /* send via smtp server */
    public function send_smtp($to, $subject)
    {
        $msg = $this->getMessage(); // to count multipart or not

        $header="Date: ".date("D, j M Y G:i:s")." +0700\r\n";
        $header.="From: <".$this->smtp['from'].">\r\n";
        $header.="X-Mailer: The Bat! (v3.99.3) Professional\r\n";
        $header.="Reply-To: <".$this->smtp['from'].">\r\n";
        $header.="X-Priority: 3 (Normal)\r\n";
        $header.="Message-Id: <".mt_rand(1000000, 9999999).'.'.date("YmjHis")."@gmail.com>\r\n";
        $header.="To: <".$to.">\r\n";
        $header.="Subject: ".$this->getSubject($subject)."\r\n";
        $header.="MIME-Version: 1.0\r\n";
        if ($this->multipart) {
            $header .= "Content-type: multipart/related; boundary=" . $this->boundary . "\r\n";
        }

        $this->smtp_error = FALSE;
        $this->smtp_conn = fsockopen($this->smtp['host'], $this->smtp['port'], $errno, $errstr, 10);
        if ( ! $this->smtp_conn) { $this->smtp_error = "соединение с серверов не прошло "; goto err;}
        $resp = $this->get_smtp_response();

        fputs($this->smtp_conn,"HELO ".(Kohana::$environment == Kohana::DEVELOPMENT ? 'test.mladenecshop.ru' : 'mladenec.ru')."\r\n");
        $resp = $this->get_smtp_response();
        $code = substr($resp,0,3);
        if ($code != 250) { $this->smtp_error = "ошибка приветствия HELO\n".$resp; goto err;}

        fputs($this->smtp_conn,"AUTH LOGIN\r\n");
        $resp = $this->get_smtp_response();
        $code = substr($resp,0,3);
        if ($code != 334) {$this->smtp_error = "сервер не разрешил начать авторизацию\n".$resp; goto err;}

        fputs($this->smtp_conn,base64_encode($this->smtp['login'])."\r\n");
        $resp = $this->get_smtp_response();
        $code = substr($resp,0,3);
        if ($code != 334) {$this->smtp_error = "ошибка доступа к такому юзеру\n".$resp; goto err;}

        fputs($this->smtp_conn,base64_encode($this->smtp['password'])."\r\n");
        $resp = $this->get_smtp_response();
        $code = substr($resp,0,3);
        if ($code != 235) {$this->smtp_error = "не правильный пароль\n".$resp; goto err;}

        fputs($this->smtp_conn,"MAIL FROM: <".$this->smtp['from'].">\r\n");
        $resp = $this->get_smtp_response();
        $code = substr($resp,0,3);
        if ($code != 250) {$this->smtp_error = "сервер отказал в команде MAIL FROM\n".$resp; goto err;}

        $to_addr = explode(',', $to);
        foreach($to_addr as $a) {
            fputs($this->smtp_conn,"RCPT TO: <".trim($a).">\r\n");
            $resp = $this->get_smtp_response();
            $code = substr($resp,0,3);
            if ($code != 250 AND $code != 251) {$this->smtp_error = "Сервер не принял команду RCPT TO\n".$resp; goto err;}
        }

        fputs($this->smtp_conn,"DATA\r\n");
        $resp = $this->get_smtp_response();
        $code = substr($resp,0,3);
        if ($code != 354) { $this->smtp_error = "сервер не принял DATA\n".$resp; goto err;}

        $data = $header.($this->multipart ? "This is a multipart message in MIME format.\r\n\r\n" : "").$msg."\r\n.\r\n";
        fputs($this->smtp_conn, $data);
//		echo $data;
        $resp = $this->get_smtp_response();
        $code = substr($resp,0,3);
        if ($code != 250) { $this->smtp_error = "ошибка отправки письма\n".$resp;  goto err;}

        fputs($this->smtp_conn,"QUIT\r\n");

        err:	fclose($this->smtp_conn);
        if ($this->smtp_error !== FALSE) $this->smtp_error = $this->smtp_error."\n".$subject."\n".$to;

        return $this->smtp_error === FALSE;
    }
}