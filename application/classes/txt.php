<?php
/**
 * Исключение выбрасываемое при неверных данных
 */
class Txt_Exception extends Kohana_Exception { }

/**
 * Collection of functions to deal with some texts
 *
 * @author pks
 */
class Txt {

    const ESCAPE_STRING = '___TXT_ESCAPED___';
    
     /**
     * Коды мобильных операторов для телефонных функций, строго 3 цифры
     * +7 подставится автоматически
     * 
     * @var array
     */
    private static $mobile_prefix = [
        '901', '902', '903', '904', '905', '906', '908', '909', 
        '910', '911', '912', '913', '914', '915', '916', '917', '918', '919',  
        '920', '921', '922', '923', '924', '925', '926', '927', '928', '929',  
        '930', '931', '932', '933', '934', '936', '937', '938', 
        '950', '951', '952', '953', 
        '960', '961', '962', '963', '964', '965', '967', '968', 
        '980', '981', '982', '983', '984', '985', '987', '988',
        '989', '997'
    ];

    private static $months_short = array('янв','фев','мар','апр','май','июнь','июль','авг','сен.','окт','нояб', 'дек');
    private static $months_short_genitiv = array('янв','фев','мар','апр','мая','июня','июля','авг','сен.','окт','нояб', 'дек');
    private static $months = array('январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь', 'декабрь');
    private static $months_genitiv = array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября', 'декабря');
    
    
    /**
     * @unused
     * 
     * @param type $from_hour
     * @param type $from_minutes
     * @param type $to_hour
     * @param type $to_minutes
     * @param type $time_range_string
     */
    public static function parse_time_range(&$from_hour,&$from_minutes,&$to_hour,&$to_minutes,$time_range_string)
    {
        $time_range_string = str_replace(' ', '', $time_range_string);
        list($from,$to) = array_map('trim',explode('-',$time_range_string));
        list($from_hour,$from_minutes) = array_map('trim',explode(':',$from));
        list($to_hour,$to_minutes) = array_map('trim',explode(':',$to));
    }
    
    public static function milliseconds_to_time($milliseconds)
    {
        $seconds = round ($milliseconds / 1000, 0);
        
        $s = $seconds % 60;
        $h = floor($seconds / 3600);
        $m = floor($seconds / 60) - $h * 60;
        
        return sprintf ('%1$02d:%2$02d:%3$02d', $h, $m, $s);
    }
    
    /**
     * 
     * @param string $time
     * @return int
     */
    public static function time_to_seconds($time, $default_time = FALSE)
    {   
        $seconds = 0;
        
        if ( is_string($time) AND 2 == substr_count($time, ':'))
        {
            list($hours,$minutes,$seconds) = explode(':',$time);
            $seconds += $minutes * 60 + $hours * 3600;
        }

        if ($default_time AND 0 == $seconds) {
            $seconds = self::time_to_seconds($default_time);
        }
        return $seconds;
    }
    
    public static function ru_month($month,$short = FALSE)
    {
        if ( ! ($month <=12 AND $month > 0)) return NULL;
        return $short?(self::$months_short[$month-1]):(self::$months[$month-1]);
    }
    
    public static function ru_date($time, $short = FALSE)
    {
        $timestamp = strtotime($time);
        
        $month_n = date('n', $timestamp);
        if($month_n < 1 OR $month_n > 12) return 'error';
        if ($short) {
            $month_name = self::$months_short_genitiv[$month_n-1];
        } else {
            $month_name = self::$months_genitiv[$month_n-1];
        }
        $date = date('j ',$timestamp) . $month_name . date(' Y',$timestamp);
        return $date;
    }
    
    public static function array_to_xls($array)
    {
        $xls = pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);

        $row_n = 0;
        $col_n = 0;
        foreach($array as $rn => $row) {
            foreach($row as $cn => $val) {
                $l = strlen(iconv('utf8', 'cp1251', $val));
                $xls .= pack('ssssss', 0x204, 8 + $l, $row_n, $col_n, 0x0, $l);
                $xls .= iconv('utf8', 'cp1251', $val);
                $col_n ++;
            }
            $col_n = 0;
            $row_n ++;
        }
        $xls .= pack('ss', 0x0A, 0x00);

        return $xls;
    }
    
    /**
     * Функция транслитерации для урлов
     * @param $str
     * @return mixed
     */
    public static function translit($str)
    {
        static $replace = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' =>  'yo', 'ж' => 'g', 'з' => 'z', 'и' => 'i', 'й' => 'y',
            'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' =>  'r',  'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f',
            'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',

            '&' => 'i', ' ' => '-', '—' => '-',
        );
        $str = strtr(mb_strtolower($str), $replace); // replace chars in lowercased string
        $str = preg_replace('~[^a-z0-9-]+~iu', '', $str); // delete all not allowed chars

        return preg_replace('~-+~u', '-', $str); // replace many `-` to one
    }

    /**
     * Вывод красивого телефона
     * @param $phone
     * @return mixed
     */
    public static function phone_format($phone)
    {
        $phone = trim($phone);
        $phone = preg_replace('/[^0-9]+/u', '', $phone);

        if (('8' == $phone[0]) OR ('7' == $phone[0])) {
            /* we don`t need a 8 or +7 before number, we'll add them later */
            $phone = substr($phone, 1);
        }

        $phone = preg_replace('/(\d\d\d)(\d\d\d)(\d\d)(\d\d)/u', '+7 ($1) $2-$3-$4', $phone);
        return $phone;
    }

    /**
     * Проверяет корректность телефонного номера
     * 
     * @param string $phone
     * @return boolean
     */
    public static function phone_is_correct($phone)
    {
        $phone = trim($phone);
        $phone = preg_replace('/[^0-9\+]+/','',$phone);
        if (preg_match("/^[+][7][0-9]{10,10}+$/", $phone)) return TRUE;
        else return FALSE;
    }

    /**
     * проверяет что номер похож на номер из россии (по первой цифре после +7)
     * Урал, Западная и Восточная Сибирь — 300-399 (Пермь 342, Иркутск 3952)
     * Центральные регионы, Дальний Восток — 400-499 (Владимир 492, Калуга 484)
     * Северо-Западные регионы, Северный Кавказ, Поволжье — 800-899 (Санкт-Петербург 812, Петрозаводск 8142)
     * Мобильная связь — 900-999 (МТС 910, Мегафон 926)
     * @param $phone
     * @return bool
     */
    public static function phone_is_ru($phone)
    {
        return self::phone_is_correct($phone) && in_array($phone[2], [3,4,8,9]);
    }
    
    /**
     * Приводит телефон к виду: +79991233456, 
     * если невозможно возвращает ''
     * 
     * @param string
     * @return string
     */
    public static function phone_clear($phone)
    {
        if (empty($phone)) return '';
        $phone = trim($phone);
        $phone = preg_replace('/\D+/u', '', $phone);
        if ('8' == $phone[0]) {
            $phone[0] = '7';
        }
        
        $phone = '+' . substr($phone,0,11);
        
        if (TRUE === self::phone_is_correct($phone)) return $phone;
        else return '';
    }
    
    /**
     * @param string $phone
     * @return bool
     */
    public static function phone_is_mobile($phone)
    {
        if ( ! $phone = self::phone_clear($phone)) return FALSE;
        
        $code = substr($phone,2,3);
        if (FALSE !== array_search($code, self::$mobile_prefix)) return TRUE;

        return FALSE;
    }

    /**
     * @unused
     * @deprecated
     * 
     * Заменить все теги таким образом, чтобы сохранилось деление на параграфы
     * используя br для перевода строки
     * 
     * @param string $html
     * @return string
     */
    public static function clean_html_for_ozon($html)
    {
        $convert_entities = array(
            '«' => '&laquo;',
            '»' => '&raquo;',
            '—' => '&mdash;',
            '‘' => '&lsquo;',
            '’' => '&rsquo;',
            '“' => '&ldquo;',
            '”' => '&rdquo;',
            '®' => '&reg;',
            'α' => '&alpha;',
            'º' => '&deg;',
            '°' => '&deg;',
            'é' => '&eacute;',
        );
        
        $html = str_replace('&nbsp;', " ", $html);
        $html = str_replace(array_keys($convert_entities), $convert_entities, $html);
        
        $html = preg_replace('|<\s*br\s*>|u', "<br />", $html);
        $html = strip_tags($html, '<br>,<br />,<address>,<blockquote>,<div>,<dl>,<dt>,<h1>,<h2>,<h3>,<li>,<ol>,<p>,<pre>,<table>,<tr>,<th>,<td>,<tr>,<ul>');
        $html = preg_replace('|<\s*/\s*\w+[^<>]*[<>]|u', "<br />", $html);
        $html = preg_replace('|[^a-zA-Zа-яА-Я0-9 \&\.\,\;\%\*\@\!\-\(\)\<\>\/]+|u', " ", $html);
        $html = strip_tags($html, '<br>');
        $html = preg_replace('|<br\s*/>\s*<br\s*/>|u', "<br />", $html);
        $html = preg_replace('|\s+|u', " ", $html);
        return $html;
    }
    
    /**
     * Удаляет ASCII символы с кодами 01-31, кроме 9, 10, 13
     * 
     * @param string $string
     * @return string
     */
    public static function clean_rude_symbols($string)
    {
        $chr = array('','','','','','','','','','','','','','','','','','','','','','','','','','','','');
        return str_replace($chr, '', $string);
    }
    
    /** 
     * Проверить, есть ли значащая информация в html фрагменте
     * Например, для валидации формы с визуальным редактором
     * @param string
     * @param int $min_length
     * @return string
     */
    public static function is_html_text_filled($html, $min_length = 3)
    {
        $text_length = strlen(trim(strip_tags($html)));

        return $text_length > $min_length;
    }

    /**
     * Проставляет ссылку на отписку от расслылки в текст рассылки
     */
    public static function spam($text, $to)
    {
        return str_replace(
            'http://mladenec-shop.ru/account',
            'http://www.mladenec-shop.ru/unsubscribe?mail=' . $to . '&check='.md5(Cookie::$salt.$to) . '&utm_source=mspam&utm_medium=email&utm_campaign=email',
            $text
        );
    }

    /**
     * Добавляет к ссылке параметры просмотра товаров
     * @param $href
     * @param $settings
     * @return string
     */
    public static function view_params($href, $settings = array('s' => 'rating', 'pp' => 48, 'x' => 1, 'm' => 1))
    {
        $href .= '?'.http_build_query($settings, null, '&');
        
        return $href;
    }

    public static function link_params($link)
    {
        $param_markers = array('#!', ';', '?');
        
        $replaced_link = trim(str_replace($param_markers, '&', $link), '&');
        
        $param_str = explode('&', $replaced_link);
        
        $params['path'] = array_shift($param_str);
        
        foreach ($param_str as $ps)
        {
            if (FALSE === strpos($ps,'=')) continue;
            
            list($name, $val) = explode('=',$ps);
            
            if ('c' == $name OR 'b' == $name) $params[$name] = explode('_', $val);
            elseif ('f' == $name[0]) $params['f'][substr($name, 1)] = explode('_', $val);
            else $params[$name] = $val;
        }
        
        return $params;
    }
    
    /**
     * получить элементы страницы из массива, если зациклить массив = после последнего элемента снова идёт первый
     * @param $page - номер страницы или FALSE
     * @param $per_page - число элементов на странице
     * @param $array - массив
     * @return array
     */
    public static function cycle_page(&$page, $per_page, $array)
    {
        $total = count($array);
        if ($page === FALSE) $page = rand(0, round($total / $per_page)); // random page

        $offset = ($page * $per_page) % $total;
        if ($offset < 0) $offset = $total + $offset;

        $page_ids = array_slice($array, $offset, $per_page);
        $found = count($page_ids);
        if ($found < $per_page) $page_ids = array_merge($page_ids, array_slice($array, 0, $per_page - $found));
        return $page_ids;
    }
    
    /**
     * Разбирает строку вида ааа©bbb©ccc в массив [0=>aaa, 1=>bbb, 2=>ccc]
     * 
     * @param string $delimiter - разделитель элементов
     * @param string $string - строка
     * @param int $valid_count - ожидаемое количество полей
     * @param int $required_fields - нумерация начинается с 0!!!
     * @return array
     * @throws Txt_Exception
     */
    public static function parse_explode($delimiter, $string, $valid_count = 0, $required_fields = array())
    {
        $escaped_string = str_replace('\\'.$delimiter, self::ESCAPE_STRING, $string);
        
        if ( $valid_count > 0 AND substr_count($escaped_string, $delimiter) + 1 != $valid_count) // +1 т.к. разделителей на 1 меньше, чем полей)
        {
            throw new Txt_Exception('Неправильный формат в строке: ' . $string . ', ожидаемое количество полей ' . $valid_count);
        }
        
        $array =  explode($delimiter, $escaped_string);
        
        foreach($required_fields as $rf)
        {
            if (empty($array[$rf]))
            {
                throw new Txt_Exception('Отсутствует обязательное поле ' . $rf . ' в строке : ' . $string); 
            }
        }

        return str_replace(self::ESCAPE_STRING, $delimiter, $array);
    }
}
