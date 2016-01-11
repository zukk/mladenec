<?php
class Model_Actiontag extends ORM
{
    use Seo;

    protected $_table_name = 'z_actiontag';

    public $title = ''; // название тега
    public $url = ''; // урл для тегов

    protected $_table_columns = array(
        'id' => 0,
        'title' => '',
        'url' => ''
    );

    protected $_has_many = array(
        'action' => array(
            'model' => 'action',
            'through' => 'z_action_ids',
            'foreign_key' => 'actiontag_id',
            'far_key' => 'action_id'
        )
    );

    public function save_actiontag(){

        $title = $this->title;
        $url = $this->url;

        $query = DB::insert('z_actiontag', array('title', 'url'))
            ->values(array($title, $url))
            ->execute();

        return $query;
    }

    public function actiontags(){

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

    public function get_actiontags(){

        $query  = ORM::factory('actiontag')
            ->where('actiontag.id', 'IN', DB::select( DB::expr('DISTINCT actiontag_id'))
                ->from('z_actiontag_ids') );


        $res = $query->find_all();

        return $res;
    }

    public function get_urltags($tag){

        $return = DB::select(DB::expr('z_action.id as id'))
            ->from('z_actiontag')
            ->join('z_actiontag_ids')
            ->on('z_actiontag.id', '=', 'z_actiontag_ids.actiontag_id')
            ->join('z_action')
            ->on('z_action.id', '=', 'z_actiontag_ids.action_id')
            ->where('z_actiontag.url', 'IN', $tag)
            ->execute()
            ->as_array();

        $arr = [];
        foreach($return as $ret){
            $arr[] = $ret['id'];
        }

        return $arr;

    }

}