<?php
class Model_Config extends ORM {

    const SMS_ACQUIROPAY = 1; //'aquiropay';
    const SMS_MTS = 2; //'mts';
	
    protected $_table_name = 'z_config';

    protected $_belongs_to = array(
        'logo' => array('model' => 'file', 'foreign_key' => 'logo_id'),
        'image' => array('model' => 'file', 'foreign_key' => 'image_id')
    );

    protected $_table_columns = array(
        'id' => '', 'phone' => '', 'menu' => '', 'logo_id' => '', 'seo_index' => '', 'accept_cards'=>'',
        'mail_return' => '', 'mail_comment' => '', 'mail_order' => '', 'mail_review' => '', 'mail_partner' => '', 
        'mail_good'=>'','mail_action'=>'', 
        'mail_present' => '', // Уведомления об окончании подарков
        'mail_error'          => '', 
        'mail_payment'        => '',
        'sms_present'         => '', // Уведомления об окончании подарков по СМС
        'sms_method'         => '', // метод отсылки СМС
        'mail_fransh'         => '', 
        'mail_feedback'       => '', 
        'mail_contest'        => '',
        'mail_sms_warning'    => '',
        'mail_empty_section' => '',
        'mail_emptytag' => '',
        'actions_header'      =>'',
        'actions_subheader' =>'',
        'instant_search' => '',
        'rr_enabled' => '',
        'use_ozon_delivery' => '',
        'emails' => '',
        'link_left' => '',
        'link_right' => '',
        'image_id' => '',
    );

    /**
     * Список картинок
     * @return array
     */
    public function img()
    {
        return array('logo_id' => array(270, 90), 'image_id' => array());
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
