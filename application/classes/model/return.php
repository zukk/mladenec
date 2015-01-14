<?php
class Model_Return extends ORM {

    protected $_table_name = 'z_return';

    protected $_table_columns = array(
        'id' => '', 'user_id' => '', 'name' => '', 'email' => '', 'text' => '','img' => '',
        'answer' => '', 'answer_sent'=>'', 'created' => '', 'fixed' => ''
    );

    protected $_belongs_to = array(
        'user' => array('model' => 'user', 'foreign_key' => 'user_id'),
        'image' => array('model' => 'file', 'foreign_key' => 'img')
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
            'email' => array(
                array('not_empty'),
                array('email'),
            ),
            'text' => array(
                array('not_empty'),
            ),
        );
    }

    public function flag()
    {
        return array('fixed');
    }
    
    public function admin_save()
    {
        $messages = array('errors' => array(),'messages' => array());
        
        $name_length = strlen(trim(strip_tags($this->name)));
        if ($name_length < 3) {
            $messages['errors'][] = Kohana::message('admin/return', 'name.default');;
        }
        $answer_length = mb_strlen(trim(strip_tags($this->answer)));

        if ($answer_length < 5) {
            /* empty answer */
            $messages['errors'][] = Kohana::message('admin/return', 'answer.default');;
        }

		$post_send = Request::current()->post('answer_sent');
		
        if (($name_length > 3) && ($answer_length > 5) && !empty( $post_send)) {
            /* answer not empty, if marked as fixed do sending email */
            
            Log::instance()->add(Log::INFO,'Отправляю письмо с ответом на претензию #'.$this->id.' пользователю #'.$this->user_id);
            
            Mail::htmlsend('return_answer', array('r' => $this), $this->email, 'Ответ на претензию: '.$this->name);
            $this->answer_sent = time();
            $this->save();
            Model_History::log('return', $this->id, 'answer', $this->answer);
        }
        
        return $messages;
    }
}