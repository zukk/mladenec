<?php
// Уведомления о поставке
class Model_Good_Warn extends ORM {

    protected $_table_name = 'z_warn';

    /* protected $_table_columns = array(
        'id' => '', 'email' => '', 'good_id'=>'', 'user_id' => '', 'qty' => '',
    ); */
	
    protected $_belongs_to = array(
        'user' => array('model' => 'user', 'foreign_key' => 'user_id'),
        'good' => array('model' => 'good', 'foreign_key' => 'good_id'),
    );

    public function rules()
    {
        return array(
            'good_id' => array(
                array('not_empty'),
            ),
            'qty' => array(
                array('not_empty'),
            ),
            'email' => array(
                array('not_empty'),
                array('email'),
            ),
        ); 
    }

    /**
     * Выслать уведомление о поставке
     */
    public function warn()
    {
        Mail::htmlsend('warn', ['user' => $this->user, 'g' => $this->good], $this->email, 'Товар «'.$this->good->group_name.' '.$this->good->name.'» появился в наличии');
        $this->delete();

        return TRUE;
    }
	
    /**
     * Выслать предложение купить аналогичные товары
     */
    public function notify()
    {
		$analogy = array_values($this->good->analogy(6));

        if (empty($analogy)) return FALSE;

        Mail::htmlsend('warn_notify', ['user' => $this->user, 'g' => $this->good, 'goods' => $analogy, 'host' => 'mladenec-shop.ru'], $this->email, 'Товар «'.$this->good->group_name.' '.$this->good->name.'»: аналогичные товары');
        $this->notified += 1;

        // Иногда валидация email может провалиться
        try {
            $this->save();
        } catch (ORM_Validation_Exception $ex) {
            echo 'unable to validate' . "\n";
        }
		
        return TRUE;
    }
}