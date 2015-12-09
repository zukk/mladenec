<?php
class Model_Pampers extends ORM {

    protected $_table_name = 'pampers';

    protected $_table_columns = [
        'id' => '', 'name' => '', 'weight' => '', 'age' => '', 'index' => '', 'address' => '', 'phone' => '', 'email' => '', 'site' => ''
    ];


    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                ['not_empty'],
            ],
            'weight' => [
                ['not_empty'],
            ],
            'age' => [
                ['not_empty'],
            ],
            'index' => [
                ['not_empty'],
                ['exact_length', [':value', 6]],
            ],
            'address' => [
                ['not_empty'],
            ],
            'phone' => [
                ['not_empty'],
                ['phone', [':value']],
            ],
            'email' => [
                ['not_empty'],
                ['email'],
            ],
            /*
            'site' => [
                ['not_empty'],
            ],
            */
        ];
    }
}
