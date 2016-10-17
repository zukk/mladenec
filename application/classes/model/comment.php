<?php

class Model_Comment extends  ORM {

    protected $_table_name = 'z_comment';

    protected $_table_columns = array(
        'id' => '',  'user_id' => '', 'user_name' => '', 'to' => '', 'name' => '', 'date' => '', 'text' => '', 'active' => '', 'theme_id' => '', 'answer' => ''
    );

    static $to = array(
        103	=> 'Служба доставки',
        104	=> 'Прием заказов (менеджеры)',
        105	=> 'Комплектация заказов (склад)',
        106	=> 'Администратор сайта',
        107	=> 'Руководство',
        108	=> 'Оставить сообщение в книге отзывов',
    );

    static $answer_by = array(
        12261 => 'Розничный отдел',
        12263 => 'Отдел закупок',
        12262 => 'Оптовый отдел',
        12264 => 'Администрация сайта',
        12268 => 'Руководство',
    );

    /**
     * @return array
     */
    public function rules()
    {
        return array(
			'text' => array(array('not_empty'),)
		);
    }
        
    /**
     * @static Проверка на существование to
     * @param $to
     * @return bool
     */
    public static function to($to) {
        return ( ! empty(self::$to[$to]));
    }

    /**
     * @param bool $to
     * @return array|bool
     */
    public function get_to($to = false)
    {
        if ($to === false) return self::$to;

        if ( ! empty(self::$to[$to])) return self::$to[$to];

        return false;
    }

    /**
     * @param bool $answer_by
     * @return array|bool
     */
    public function get_answer_by($answer_by = false)
    {
        if ($answer_by === false) return self::$answer_by;

        if ( ! empty(self::$answer_by[$answer_by])) return self::$answer_by[$answer_by];

        return false;
    }

    /**
     * @param bool $html
     * @return string
     */
    public function get_link($html = true)
    {
        $link = sprintf('/about/review/%d/', $this->id);
        return $html ? HTML::anchor($link, $this->name) : $link;
    }

    /**
     * @static
     * @return string
     */
    static function get_list_link()
    {
        return Route::url('comments');
    }

    /**
     * Порядок для списка в админке
     * @return $this
     */
    public function admin_order()
    {
		$orderField = 'id';
		$desc = 'DESC';
		
		$order = Request::current()->query('order');

		if( $order == 'internalRatingDesc' ){
			$orderField = 'internal_rating';
			$desc = 'DESC';
		}
		else if( $order == 'internalRatingAsc' ){
			$orderField = 'internal_rating';
			$desc = 'ASC';
		}
		
        return $this->order_by($orderField, $desc);
    }

    /**
     * Список чекбоксов
     * @return array
     */
    public function flag()
    {
        return array('active');
    }

    /**
     * Последние комменты
     * @param int $limit
     * @return ORM
     */
    public static function last($limit = 1)
    {
        return ORM::factory('comment')
            ->where('active', '=', 1)
            ->order_by('id', 'DESC')
            ->limit($limit)
            ->find_all();
    }

    /**
     * После сохранения комментария в админке - сохранить его свойства, отправить мыло, если надо
     * @return array
     */
    public function admin_save()
    {
        $errors = array();

        $answer_valid = FALSE;
        $answer_length = strlen(trim(strip_tags($this->answer)));

        if ($answer_length > 3) {
            $answer_valid = TRUE;
        } else { // пустой текст ответа
            $errors[] = Kohana::message('admin/comment', 'answer.default');
            $this->active = 0;
            $this->answer = '';
            $this->save();
        }
        
        if (empty($this->email_sent) && $this->active && $answer_valid) {
            
            Log::instance()->add(Log::INFO,'Отправляю письмо с ответом на отзыв #'.$this->id.' пользователю #'.$this->user_id);
            Mail::htmlsend('answer', array('i' => $this), $this->email, 'Re: '.$this->name);
            Model_History::log('comment', $this->id, 'answer', $this->answer);
            
            $this->email_sent = time(); // отметим время отправки ответа и в модели
            $this->save();
        }
        
        if ( ! empty($errors)) return array('errors'=>$errors);
    }
}


