<?php

/**
 * Class Model_Deferred
 * Отложенные товары
 */
class Model_Deferred extends ORM
{
    /**
     * @var string
     *
     */
    protected $_table_name = 'z_deferred_good';

    /**
     * @var array
     */
    protected $_table_columns = [
        'id'            => '',
        'user_id'       => '',
        'good_id'       => '',
        'created'       => ''
    ];

    /**
     * @var array
     */
    protected $_belongs_to = [
        'good' => [
            'model'         => 'good',
            'foreign_key'   => 'good_id'
        ],
        'user' => [
            'model'         => 'user',
            'foreign_key'   => 'user_id'
        ],
    ];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => [
                ['not_empty'],
            ],
            'good_id' => [
                ['not_empty'],
            ],
        ];
    }

    /**
     * @param Validation $validation
     * @return object
     * @throws Kohana_Exception
     * Отложить товар
     */
    public function create(Validation $validation = NULL)
    {
        $insert_query = DB::insert('z_deferred_good', array('user_id', 'good_id'));
        $insert_query->values(array(
            'user_id' => $this->user_id,
            'good_id' => $this->good_id
        ));

       $result =  DB::query(Database::INSERT, $insert_query)
           ->execute();

        return $result;
    }

    /**
     * @return object
     * Удалить из отложенных
     */
    public function delete()
    {
        $result = DB::delete('z_deferred_good')
            ->where('user_id', '=', $this->user_id)
            ->and_where('good_id','=',$this->good_id)
            ->execute();

        return $result;
    }

    /**
     * @return bool
     * Является ли товар отложенным
     */
    public function  is_deferred()
    {
        $query = DB::select('id')
            ->from('z_deferred_good')
            ->where('good_id', '=', $this->good_id)
            ->and_where('user_id', '=', $this->user_id)
            ->execute()
            ->get('id', 0);

        if (empty($query)) {
            return false;
        } else {
            return true;
        }
    }
}
