<?php

class Model_Comment_Theme extends  ORM {

    protected $_table_name = 'z_comment_theme';

    protected $_has_one = array(
        'comment' => array('model' => 'comment', 'foreign_key' => 'theme_id'),
    );

	public static $salt = '(%85Hhi%$*gjwh)(';
	
    protected $_table_columns = array(
        'id' => '',  'user_id' => '', 'user_name' => '', 'to' => '', 'name' => '', 'email' => '', 'active' => '', 'phone' => '', 'internal_rating' => '', 'check' => ''
    );

    static $to = array(
        103	=> 'Служба доставки',
        104	=> 'Прием заказов (менеджеры)',
        105	=> 'Комплектация заказов (склад)',
        106	=> 'Администратор сайта',
        107	=> 'Руководство',
        108	=> 'Оставить сообщение в книге отзывов',
    );

	/**
	 * Дает хеш для возможности отвечать гостям
	 * @return string
	 */
	public function getHash(){
		return md5( $this->id . self::$salt );
	}
	
    /**
     * @return array
     */
    public function rules()
    {
		return array();
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
     * @param bool $html
     * @return string
     */
    public function get_link($html = true)
    {
        $link = sprintf('/about/review#!id%d', $this->id);
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
     * Список чекбоксов
     * @return array
     */
    public function flag()
    {
        return array('active');
    }

    /**
     * Последние темы
     * @param int $limit
     * @return ORM
     */
    public static function last($limit = 1)
    {
        return ORM::factory('comment_theme')
            ->where('active', '=', 1)
            ->order_by('id', 'DESC')
            ->limit($limit)
            ->find_all();
    }
	
	public static function getLast($limit = 5, $offset = 0, $user_id = 0){
		
        $c = ORM::factory('comment_theme')->where('active', '=', 1);

		if( !empty( $user_id ) ){
			$c->where('user_id', '=', $user_id);
		}
		
		$c->reset(false);
		
        $themes = $c
            ->order_by('id', 'desc')
            ->limit($limit)
            ->offset($offset)->find_all()->as_array('id');
		
		$themesQuestions = array();
		$themesQuestionsAnswers = array();
		
		$themesIds = array_keys( $themes );
		
		if( !empty( $themesIds ) ){
			
			$questions = ORM::factory('comment')->where('theme_id','IN', $themesIds)->and_where('active', '=', '1')->find_all()->as_array('id');

			foreach( $questions as $id => &$q ){
				
				$themesQuestions[$q->theme_id][] = $q;
			}
			
			$qIds = array_keys( $questions );
			
			if( !empty( $qIds ) ){
				
				$answers = ORM::factory('comment_answer')->where('q_id','IN', $qIds)->and_where('active', '=', '1')->find_all()->as_array('id');
				
				foreach( $answers as $id => &$a ){
					
					$themesQuestionsAnswers[$a->q_id][] = $a;
				}
			}
		}

		return array(
			'themes' => $themes,
			'questions' => $themesQuestions,
			'answers' => $themesQuestionsAnswers
		);
	}

	/**
	 * Получает комменты и ответы на них
	 */
	public function getData($visible = false)
    {
		
		$_ = ORM::factory('comment')->where('theme_id','=',$this->id);
		
		if( $visible ){
			$_->where('active', '=', true);
		}
		
		$_ = $_->order_by('id','asc')->find_all()->as_array();		
		
		$comments = array();
		foreach( $_ as &$comment ){
			
			$comments[$comment->id] = array('comment' => $comment, 'answers' => array());
		}
		
		$q_ids = array_keys( $comments );
		
		// чтобы не напороться на fatal
		if( !empty( $q_ids ) ){
			
			$_ = ORM::factory('comment_answer')->where('q_id','IN', $q_ids );
		
			if( $visible ){
				$_->where('active', '=', true);
			}
			
			$_ = $_->order_by('id', 'asc')->find_all()->as_array();
			
			foreach( $_ as &$answer ){
				$comments[$answer->q_id]['answers'][] = $answer;
			}
		}
		
		return $comments;
	}
}


