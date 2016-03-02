<?php
class Model_Blocklinks extends ORM {

    protected $_table_name = 'blocklinks';

    protected $_primary_key = 'id';

    protected $_table_columns = [
        'id' => 0,
        'link' => ''
    ];

    protected $_belongs_to = array(
        'blocklinksanchor'  => array(
            'model'       => 'blocklinksanchor',
            'foreign_key' => 'id',
        )
    );


    public function get_blocklink($id){
        $query = ORM::factory('blocklinks')
            ->with('blocklinksanchor')
            ->where('id', '=', $id);

        $res = $query->find_all();
        return $res;
    }

    public function edit_blocklink($edit_id){
        $link = Request::current()->post('link');
        $title = Request::current()->post('title');
        $url = Request::current()->post('url');

        DB::update('blocklinks')->set(array('link' => $link))->where('id', '=', $edit_id)->execute();

        DB::delete('blocklinksanchor')->where('url_id', '=', $edit_id)->execute();

        $query = DB::insert('blocklinksanchor')
            ->columns(array('url_id', 'title', 'url'));
        for($i=0; $i < count($title); $i++){
            if(!empty($title) && !empty($url)){
                $query->values( array($edit_id, $title[$i], $url[$i]));
            }
        }
        $query->execute();
    }

    public function get_blocklink_url($blocklink_url){
        $query = ORM::factory('blocklinks')
            ->with('blocklinksanchor')
            ->where('link', 'LIKE', '%'.$blocklink_url.'%')
            ->find_all();

        return $query;
    }

}