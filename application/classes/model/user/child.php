<?php

class Model_User_Child extends ORM
{
    const SEX_FEMALE = 0;
    const SEX_MALE   = 1;

    public static $SEX  = array(self::SEX_FEMALE, self::SEX_MALE);
    public static $SEX_CAPTION = array('Девочка', 'Мальчик');

    protected $_table_name = 'z_user_child';

    protected $_belongs_to = array(
        'user' => array( 'model' => 'user', 'foreign_key' => 'user_id' ),
    );

    public function rules()
    {
        return array(
            'name' => array(array('not_empty')),
            'sex' => array(
                array('Model_User_Child::sex_ok')
            ),
            'birth' => array(
                array('not_empty'),
                array('Model_User_Child::birth_ok'),
            )
        );
    }

    public static function sex_ok($value)
    {
        return in_array($value, self::$SEX);
    }

    public static function birth_ok($value)
    {
        if ( ! preg_match('~^(20\d\d)-(\d\d)-(\d\d)$~', $value, $matches)) return FALSE;
        if ( ! $time = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1])) return FALSE;
        return date('Y-m-d', $time) == $value;
    }
}
