<?php
class Model_Ozontype extends ORM {

    protected $_table_name = 'ozon_types';

    protected $_table_columns = ['id' => '', 'name' => '', 'path_name' => '', 'template_id' => ''];

    /**
     * Товары в категории
     * @return Database_Result
     * @throws Kohana_Exception
     */
    function get_goods()
    {
        return ORM::factory('good')
            ->where('ozon_type_id', '=', $this->id)
            ->find_all();
    }

    /**
     * Сохранение привяки товаров к озон-категории
     */
    function admin_save()
    {
        $request = Request::current();

        $goods_ids = $request->post('goods'); // тут может быть пусто - значит стереть все
        $ozon_type_id = $request->post('id');

        $return = [];

        if ( ! empty($ozon_type_id)) {
            $return = Model_Good::save_ozon($ozon_type_id, $goods_ids);
        }
        return array($return);
    }
}