<?php
class Model_Partner extends ORM {

    protected $_table_name = 'z_partner';

    protected $_table_columns = array('id' => '', 'user_id' => '', 'name' => '', 'address'=>'', 'contacts'=>'', 
        'dealers'=>'', 'positioning'=>'', 'logistics'=>'', 'price_monitoring'=>'', 'month_sales'=>'', 'payment'=>'', 
        'qty_remuneration'=>'', 'return'=>'', 'text' => '', 
        'email' => '', 'answer' => '', 'answer_sent' => '', 'created' => '');

    protected $_belongs_to = array(
        'user' => array('model' => 'user', 'foreign_key' => 'user_id'),
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
            'address' => array(
                array('not_empty'),
            ),
            'contacts' => array(
                array('not_empty'),
            ),
            'dealers' => array(
                array('not_empty'),
            ),
            'positioning' => array(
                array('not_empty'),
            ), 
            'logistics' => array(
                array('not_empty'),
            ),
            'price_monitoring' => array(
                array('not_empty'),
            ),
            'month_sales' => array(
                array('not_empty'),
            ),
            'payment' => array(
                array('not_empty'),
            ),
            'qty_remuneration' => array(
                array('not_empty'),
            ),
            'return' => array(
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
            $errors[] = Kohana::message('admin/partner', 'answer.default');
            $this->answer = '';
            $this->save();
        }

        if (empty($this->answer_sent) && $answer_valid) {

            Log::instance()->add(Log::INFO, 'Отправляю письмо с ответом на заявку #'.$this->id.' на почту '.$this->email);
            Mail::htmlsend('partner_answer', array('r' => $this), $this->email, 'Re: '.$this->name);
            Model_History::log('partner', $this->id, 'answer', $this->answer);

            $this->answer_sent = time(); // отметим время отправки ответа и в модели
            $this->save();
        }

        if ( ! empty($errors)) return array('errors'=>$errors);
    }

}