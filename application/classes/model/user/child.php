<?php

class Model_User_Child extends ORM
{
    const SEX_FEMALE = 0;
    const SEX_MALE   = 1;

    public static $SEX  = [self::SEX_FEMALE, self::SEX_MALE];
    public static $SEX_CAPTION = ['Девочка', 'Мальчик'];

    protected $_table_name = 'z_user_child';

    protected $_belongs_to = [
        'user' => ['model' => 'user', 'foreign_key' => 'user_id'],
    ];

    protected $_table_columns = [
        'id' => '',
        'name' => '',
        'sex' => '',
        'user_id' => '',
        'birth' => '',
    ];

    public function rules()
    {
        return [
            'name' => [['not_empty']],
            'sex' => [
                ['Model_User_Child::sex_ok']
            ],
            'birth' => [
                ['not_empty'],
                ['Model_User_Child::birth_ok'],
            ],
        ];
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
