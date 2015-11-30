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
        '900',  '901',  '902',  '903',  '904',  '905',  '906',  '908',  '909',
        '910',  '911',  '912',  '913',  '914',  '915',  '916',  '917',  '918',  '919',  
        '920',  '921',  '922',  '923',  '924',  '925',  '926',  '927',  '928',  '929',  
        '930',  '931',  '932',  '933',  '934',  '936',  '937',  '938',  '939',  
        '941',  
        '950',  '951',  '952',  '953',  '954',  '955',  '956',  '958',  
        '960',  '961',  '962',  '963',  '964',  '965',  '966',  '967',  '968',  '969',  
        '970',  '971',  
        '980',  '981',  '982',  '983',  '984',  '985',  '987',  '988',  '989',  
        '991',  '992',  '993',  '994',  '995',  '996',  '997',  '999', 
	];

	private static $months_short = array('янв','фев','мар','апр','май','июнь','июль','авг','сен.','окт','нояб', 'дек');
	private static $months_short_genitiv = array('янв','фев','мар','апр','мая','июня','июля','авг','сен.','окт','нояб', 'дек');
	private static $months = array('январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь', 'декабрь');
	private static $months_genitiv = array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');

	/**
	 * Исправление раскадок
	 * @var array
	 */
	private static $keyboard = [
        '`'	=>	'ё',
        'q'	=>	'й',
        'w'	=>	'ц',
        'e'	=>	'у',
        'r'	=>	'к',
        't'	=>	'е',
        'y'	=>	'н',
        'u'	=>	'г',
        'i'	=>	'ш',
        'o'	=>	'щ',
        'p'	=>	'з',
        '['	=>	'х',
        ']'	=>	'ъ',
        'a'	=>	'ф',
        's'	=>	'ы',
        'd'	=>	'в',
        'f'	=>	'а',
        'g'	=>	'п',
        'h'	=>	'р',
        'j'	=>	'о',
        'k'	=>	'л',
        'l'	=>	'д',
        ';'	=>	'ж',
        '\''=>	'э',
        'z'	=>	'я',
        'x'	=>	'ч',
        'c'	=>	'с',
        'v'	=>	'м',
        'b'	=>	'и',
        'n'	=>	'т',
        'm'	=>	'ь',
        ','	=>	'б',
        '.'	=>  'ю',
    ];

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
	public static function translit($str, $space = '-')
	{
		$replace = array(
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' =>  'yo', 'ж' => 'g', 'з' => 'z', 'и' => 'i', 'й' => 'y',
			'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' =>  'r',  'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f',
			'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
			'&' => 'i', ' ' => $space, '—' => '-',
		);
		$str = strtr(mb_strtolower($str), $replace); // replace chars in lowercased string
                
                if($space != '-') {
                    $preg = '~[^a-z0-9-'.$space.']+~iu';
                } else $preg = '~[^a-z0-9-]+~iu';
		$str = preg_replace($preg, '', $str); // delete all not allowed chars

		return preg_replace('~'.$space.'+~u', $space, $str); // replace many `-` to one
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
	 * проверяет что номер похож на номер из россии (по первой цифре после +7)
	 * Урал, Западная и Восточная Сибирь — 300-399 (Пермь 342, Иркутск 3952)
	 * Центральные регионы, Дальний Восток — 400-499 (Владимир 492, Калуга 484)
	 * Северо-Западные регионы, Северный Кавказ, Поволжье — 800-899 (Санкт-Петербург 812, Петрозаводск 8142)
	 * Мобильная связь — 900-999 (МТС 910, Мегафон 926)
	 * @param string $phone
	 * @return boolean
	 */
	public static function phone_is_correct($phone)
	{
		$phone = trim($phone);
		$phone = preg_replace('~[^0-9\+]+~', '', $phone);
		if (preg_match('/^[+]7(3|4|8|9)\d{9}$/', $phone)) return TRUE;
		else return FALSE;
	}

	/**
	 * Приводит телефон к виду: +79991233456, 
	 * если невозможно или телефон некорректен - возвращает ''
     * нельзя чтобы возвращал false, т.к. тогда преобразуется в строку '0'
	 * 
	 * @param string
	 * @return string
	 */
	public static function phone_clear($phone)
	{
		if (empty($phone)) return '';
        $phone = preg_replace('~\D+~u', '', $phone);
        if (strlen($phone) < 10) return '';
        if (strlen($phone) == 10) $phone = '7'.$phone;
        if ('8' == $phone{0}) $phone{0} = '7';
		$phone = '+' . substr($phone, 0, 11);
        if (TRUE != self::phone_is_correct($phone)) return '';
        return $phone;
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
	 * Проставляет в текст параметры для автологина, RetailRocket, и ссылку на отписку от рассылки
	 * Использовать только для рассылок
     */

	public static function spam($text, $to)
	{
		if (strpos($text, 'AUTOLOGIN')) { //заполняем autologin для получателя
            $autologin = DB::select('autologin')
				->from('z_user')
				->where('email', '=', $to)
				->order_by('sum', 'DESC')
				->order_by('id', 'DESC')
				->limit(1)
				->execute()
				->get('autologin');
			if ( ! empty($autologin)) {
				$text = str_replace('AUTOLOGIN', $autologin, $text);
			}
		}
		if (Conf::instance()->rr_enabled) { // инфа для RR
            $text = str_replace('RR_SETEMAIL', urlencode($to), $text);
        }

		return str_replace(
			'http://mladenec-shop.ru/account',
			'http://www.mladenec-shop.ru/unsubscribe?mail='.urlencode($to).'&check='.md5(Cookie::$salt.$to) . '&utm_source=mspam&utm_medium=email&utm_campaign=email',
			$text
		);
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

	/**
	 * Варианты исправления раскладки для ввода
	 * @param $q string
	 * @return array массив слов в разных раскладках
	 */
	public static function en_ru($q)
	{
        $words = array_unique([$q, strtr($q, Txt::$keyboard), strtr($q, array_flip(Txt::$keyboard))]);
        return $words;
	}

    /**
     * Строит триграммы из вводимых слов для поиска опечаток
     * @param $keywords
     * @return array
     */
    public static function trigrams($keywords)
    {
        if ( ! is_array($keywords)) $keywords = [$keywords];

        $return = [];
        foreach($keywords as $keyword) {
            $t = '__' . $keyword . '__';

            $trigrams = [];
            for ($i = 0; $i < mb_strlen($t) - 2; $i++) $trigrams[] = mb_substr($t, $i, 3);

            $return[$keyword] = implode(' ', $trigrams);
        }

        return $return;
    }

    /**
     * Делим фразу на слова
     * @param $q
     * @return array
     */
    public static function words($q)
    {
        return array_filter(array_map('trim', explode(' ', $q))); // все слова
    }

	/**
	 * Парсим текст временного интервала
	 * @param $time
	 * @return array
	 */
	public static function extract_time($time)
	{
		$return = [];
		if (preg_match_all('~(\d\d):(\d\d)~', $time, $matches))
		{
			$return['from'] = intval($matches[1][0]) + intval($matches[2][0]) / 60 * 100;
			$return['to'] = intval($matches[1][1]) + intval($matches[2][1]) / 60 * 100;
		}
		return $return;
	}

	/**
     * Получить из строки с временем доставки настройки градиента для часов
     */
    public static function grad($time)
    {
        $return = "linear-gradient(210deg, transparent 50%, #ccc 50%),linear-gradient(150deg, white 50%, transparent 50%)"; // 14-22

        $from_to = self::extract_time($time);

		if ($from_to) {
            $from = $from_to['from'];
            $to = $from_to['to'];

            if (($to - $from) > 6) { // больше 180 градусов - серый и белый градиент
                $return = "linear-gradient(".round(($to - 15)*30)."deg, transparent 50%, #ccc 50%),linear-gradient(".round(($from - 9)*30)."deg, #fff 50%, transparent 50%)"; // 14-22
            } else { // меньше 180 градусов - белый  и белый градиент
                $return = "linear-gradient(".round(($to - 15)*30 - 180)."deg, transparent 50%, #fff 50%),linear-gradient(".round(($from - 9)*30)."deg, #fff 50%, transparent 50%)"; // 14-22
            }
            return $return;
        };

        return $return;
    }

    /**
     * Чистка строки для сфинкса
     * Убираем все символы кроме букв и цифр
     * А также спецслова sphinx-а
     */
    public static function escapeSphinx($q)
    {
        $return = ' '.preg_replace('~[^a-zа-я0-9]+~iu', ' ', $q).' ';
        $return = str_replace([' SENTENCE ', ' NEAR ', ' PARAGRAPH ', ' MAYBE ', ' ZONE ', ' ZONESPAN '], ' ', $return);
        return trim($return);
    }
    
    /**
     * форматирование возраста ребенка
     */
    public static function childAge($date) {
        if (strpos($date, '.') !== false) {
            $dob = explode('.', $date);
            $date = $dob[2] . '-' . $dob[1] . '-' . $dob[0];
        }
        $age = floor((time() - strtotime($date)) / (24 * 60 * 60 * 365));
        if ($age > 0) {
            $age.= ' ' . Txt::formatNum($age, ['год', 'года', 'лет']);
        }
        if ($age < 2) {
            $current_month = date('n');
            $birth_month = date('n', strtotime($date));
            $month = ($birth_month > $current_month) ? 12 - $birth_month + $current_month : $current_month - $birth_month;
            if (date('d', strtotime($date)) > date('d'))
                $month--;

            if ($month > 0) {
                if ($age == 0) {
                    $age = $month . ' ' . Txt::formatNum($month, ['месяц', 'месяца', 'месяцев']);
                } else
                    $age .= ' ' . $month . ' ' . Txt::formatNum($month, ['месяц', 'месяца', 'месяцев']);
            } elseif ($age == 0) {
                $age = '11 месяцев';
            }
        }
        return $age;
    }

    /**
     * Склонение числительных
     */
    public static function formatNum($number, $endingArray) {
        $number = $number % 100;
        if ($number >= 11 && $number <= 19) {
            $ending = $endingArray[2];
        } else {
            $i = $number % 10;
            switch ($i) {
                case (1): $ending = $endingArray[0];
                    break;
                case (2):
                case (3):
                case (4): $ending = $endingArray[1];
                    break;
                default: $ending = $endingArray[2];
            }
        }
        return $ending;
    }

	/**
	 * Делает из даты dd-mm-yyyy дату yyyy-mm-dd и наоборот
	 * @param $txt
	 * @return string
	 */
	public static function date_reverse($txt)
	{
		return implode('-', array_reverse(explode('-', $txt)));
	}

	/**
	 * Делает из даты yyyy-mm-dd дату 1.10.2015 (вт)
	 * @param $date '2015-12-31'
	 * @return string
	 */
	public static function ship_date($date)
	{
		$weekdays = [
			0 => 'вс',
			1 => 'пн',
			2 => 'вт',
			3 => 'ср',
			4 => 'чт',
			5 => 'пт',
			6 => 'сб',
		];
		list($y, $m, $d, $w) = explode('-', date('Y-m-d-w', strtotime($date)));
		return $d.'.'.$m.'.'.$y.' ('.$weekdays[$w].')';
	}

	/**
	 * получить строку для краткого отображения адреса
	 * @param Model_User_Address $a
	 * @return string
	 */
	public static function addr(Model_User_Address $a)
	{
		return trim($a->city.', '.$a->street.', '.$a->house.($a->kv ? ', кв./оф. '.$a->kv : '').($a->correct_addr || $a->approved ? '.' : ''));
	}

    /**
     * получить варианты сумм на сдачу
     * (ближайшие суммы кратные банкнотам и больше данной)
     */
    public static function change($sum)
    {
        $banknotes = [100, 500, 1000, 5000]; // номиналы банкнот

        $return = []; //

        foreach($banknotes as $b) { // ищем
            $return[] = ceil($sum / $b) * $b;
        }
        return array_unique($return);
    }

    /**
     * Вывести список в excel согласно массиву полей
     */
    public static function as_excel($columns, $list, $fname, $callbacks = [])
    {
        include(APPPATH.'classes/PHPExcel.php');
        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();

        $x = 0;
        foreach($columns as $name => $title) {
            $sheet->setCellValueByColumnAndRow($x++, 1, $title);
        }
        $y = 2;
        foreach($list as $row) {
            $i = 0;
            foreach($columns as $name => $title) {
                $value = ! empty($callbacks[$name]) ? $callbacks[$name]($row) : $row->{$name};
                $sheet->setCellValueByColumnAndRow($i++, $y, $value);
            }
            $y++;
        }

        $fname .= '.xlsx';
        $io = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $io->save('/tmp/'.$fname);

        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename='.$fname);
        echo file_get_contents('/tmp/'.$fname);

        exit();
    }
}
