<?php

class Model_Comment_Answer extends ORM {

	protected $_table_name = 'z_comment_answer';
	protected $_table_columns = array(
		'id' => '', 'answer' => '', 'answer_by' => '', 'active' => '',
		'email_sent' => '', 'q_id' => '', 'date' => ''
	);
	static $answer_by = array(
		0 => 'выберите',
		12261 => 'Розничный отдел',
		12263 => 'Отдел закупок',
		12262 => 'Оптовый отдел',
		12264 => 'Администрация сайта',
		12268 => 'Руководство',
	);

	/**
	 * @return array
	 */
	public function rules() {
		return array(
			'answer' => array(
				array('not_empty'),
			)
		);
	}

	/**
	 * Список чекбоксов
	 * @return array
	 */
	public function flag() {
		return array('active');
	}
	
	public function get_answer_by(){
		
		return self::$answer_by[$this->answer_by];
	}

	/**
	 * Последние темы
	 * @param int $limit
	 * @return ORM
	 */
	public static function last($limit = 1) {
		return ORM::factory('comment_answer')
						->where('active', '=', 1)
						->order_by('id', 'DESC')
						->limit($limit)
						->find_all();
	}

	
	public function send(){
		
		if( !empty( $this->id ) && !empty( $this->q_id)  ){
			
			$comment = ORM::factory('comment')->where('id', '=', $this->q_id)->find();
			
			if( !empty( $comment->id ) ){
				
				$theme = ORM::factory('comment_theme')->where('id', '=', $comment->theme_id)->find();

				Log::instance()->add(Log::INFO,'Отправляю письмо с ответом #' . $this->id . ' на отзыв #'.$this->q_id);
				Mail::htmlsend('answer', array('i' => $this, 'theme' => $theme, 'comment' => $comment, 'hash' => $theme->getHash() ), $theme->email, 'Re: '.$theme->name);
				Model_History::log('comment', $this->id, 'answer', $this->answer);

				$this->email_sent = time(); // отметим время отправки ответа и в модели
				$this->save();
			}
		}
	}
}
