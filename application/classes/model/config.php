<?php
class Model_Config extends ORM {

    protected $_table_name = 'z_config';

    protected $_belongs_to = array(
        'logo' => array('model' => 'file', 'foreign_key' => 'logo_id')
    );

    protected $_table_columns = array(
        'id' => '', 'phone' => '', 'menu' => '', 'logo_id' => '', 'seo_index' => '', 'accept_cards'=>'',
        'mail_return' => '', 'mail_comment' => '', 'mail_order' => '', 'mail_review' => '', 'mail_partner' => '', 
        'mail_good'=>'','mail_action'=>'', 'mail_fransh' => '',
        'mail_present' => '', // Уведомления об окончании подарков
        'mail_error' => '', 'mail_payment' => '',
        'sms_present' => '', // Уведомления об окончании подарков по СМС
        'mail_fransh' => '', 
        'mail_feedback' => '', 
        'mail_contest' => '',
        'mail_sms_warning' => '',
        'actions_header'=>'','actions_subheader'=>''
    );

    /**
     * Список картинок
     * @return array
     */
    public function img()
    {
        return array('logo_id' => array(270, 90));
    }

    /**
     * @param null|\Validation $validation
     * @return ORM|void
     */
    public function save(Validation $validation = NULL)
    {
        parent::save($validation);
        Conf::clear_cache();
    }
}
