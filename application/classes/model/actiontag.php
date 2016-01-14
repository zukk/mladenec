<?php

/**
 * Теги для акций
 * Class Model_Actiontag
 */
class Model_Actiontag extends ORM
{
    protected $_table_name = 'z_actiontag';

    public $active = FALSE; // флаг активности тега, вычисляется

    protected $_table_columns = [
        'id' => 0,
        'title' => '',
        'url' => ''
    ];

    protected $_has_many = [
        'action' => [
            'model' => 'action',
            'through' => 'z_action_ids',
            'foreign_key' => 'actiontag_id',
            'far_key' => 'action_id'
        ]
    ];

    public function actiontags()
    {
        $id = Request::current()->post('id');

        $return['items'] = DB::select(DB::expr('z_actiontag.id as id'), DB::expr('z_actiontag.title as name'))
            ->from('z_actiontag')
            ->execute()
            ->as_array();

        $return['ids'] = DB::select('z_actiontag.id')
            ->from('z_actiontag')
            ->join('z_actiontag_ids')
                ->on('z_actiontag.id', '=', 'z_actiontag_ids.actiontag_id')
            ->join('z_action')
                ->on('z_action.id', '=', 'z_actiontag_ids.action_id')
            ->where('z_action.id', '=', $id)
            ->execute()
            ->as_array();

        return $return;
    }

    /**
     * получить все возможные теги для акций
     * @return array
     * @throws Kohana_Exception
     */
    public function get_actiontags()
    {
        return ORM::factory('actiontag')
            ->where('actiontag.id', 'IN',
                DB::select('actiontag_id')->distinct(TRUE)->from('z_actiontag_ids')
            )
            ->join('z_actiontag_ids')
                ->on('actiontag.id', '=', 'z_actiontag_ids.actiontag_id')
            ->join('z_action')
                ->on('z_action.id', '=', 'z_actiontag_ids.action_id')
            ->where('z_action.active', '=', 1)
            ->where('z_action.show', '=', 1)
            ->where_open()
                ->where('z_action.vitrina_show', '=', 'all')
                ->or_where('z_action.vitrina_show', '=', Kohana::$server_name)
            ->where_close()
            ->find_all()
            ->as_array('id');
    }

    public function get_actiontag($id){
        $query = ORM::factory('actiontag')
            ->where('id', '=', $id);

        $res = $query->find()->as_array();
        return $res;
    }

    public function edit_actiontag($edit_id, $data){
        $title = Request::current()->post('title');
        $url = Request::current()->post('url');

        $query = DB::update('z_actiontag')
            ->set(['title' => $title, 'url' => $url])
            ->where('id','=', $edit_id)
            ->execute();

        $res = $this->get_actiontag($edit_id);

        return $res;
    }

}