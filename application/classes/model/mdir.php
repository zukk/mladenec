<?php
/**
 * Description of mdir
 *
 * @author mit08
 */
class Model_Mdir extends ORM {

    protected $_table_name = 'z_mdir';

    protected $_table_columns = array(
        'id' => '', 'parent_id' => '', 'name' => '', 'comment' => '', 'created_ts'=>'','childs_count'=>0,'files_count'=>0
    );
    
    protected $_belongs_to = array(
        'parent' => array(
            'model' => 'mdir',
            'foreign_key' => 'parent_id'
            )
    );
    
    protected $_has_many = array(
        'files' => array('model' => 'file', 'foreign_key' => 'mdir_id')
    );
    
    public function delete() {
        $parent = $this->parent_id > 0 ? $this->parent : NULL;
        
        parent::delete();
        
        if($parent) {
            /* После удаления модели пересчитываем потомков родителя */
            $parent->childs_count = self::recount_childs($this->parent_id);
            $parent->save();
        }
    }
    
    /**
     * 
     * @param boolean $html
     */
    public function get_link_admin($html = TRUE) {
        $href = Route::url('admin_filemanager_dir',array('mdir_id' => $this->id));
        return $html ? HTML::anchor($href, $this->name) : $href;
    }
    
    public function get_children() {
        $children = ORM::factory($this->object_name())->where('parent_id','=',$this->pk())->order_by('name','asc')->find_all()->as_array();
        return $children;
    }
    
    public static function recount_childs($mdir_id) {
        $q = Db::query(Database::SELECT, 'SELECT count(`id`) as `count` FROM `z_mdir` WHERE `parent_id` = :id');
        $q->param(':id', $mdir_id);
        
        $count = $q->execute()->get('count', 0);
        return $count;
    }
    
    public static function list_array($parent_id = 0, $separator = '/') {
        $q = Db::query(Database::SELECT, 'SELECT * FROM `z_mdir` WHERE `parent_id` = :id');
        $q->param(':id', $parent_id);
        $dirs = $q->execute()->as_array();
        
        $result = array();
        foreach($dirs as $d) {
            $result[] = $d;
            if($d['childs_count'] > 0) {
                $childs = self::list_array($d['id']);
                foreach($childs as &$ch) {
                    $ch['name'] = $d['name'] . $separator . $ch['name'];
                    $result[] = $ch;
                }
            }
        }
        return $result;
    }
    
    public static function recount_files($mdir_id) {
        $q = Db::query(Database::SELECT, 'SELECT count(`id`) as `count` FROM `z_mfile` WHERE `mdir_id` = :id');
        $q->param(':id', $mdir_id);
        
        $count = $q->execute()->get('count', 0);
        return $count;
    }
    
    public function get_path() {
        if ( ! $this->loaded()) throw new Exception('Impossible to get a path for not loaded object');
        $pathway = $this->pathway();
        $path_arr = array();
        foreach($pathway as $pw) {
            $path_arr[] = $pw->name;
        }
        return implode('/', $path_arr);
    }
    
    public function get_pathway() {
        if ( ! $this->loaded()) throw new Exception('Impossible to get a pathway for not loaded object');
        
        $parent_id = $this->parent_id;
        $pathway = array();
        $pathway[] = ORM::factory('mdir', $this->pk());
        $i = 0;
        while ($parent_id > 0) {
            $parent = ORM::factory('mdir', $parent_id);
            if($parent->loaded()) {
                $parent_id = $parent->parent_id;
                $pathway[] = $parent;
            } else {
                throw new Exception('Non-existent parent ' + $parent_id + ' when pathway loading for a mdir #'.$this->id);
            }
            if ($i > 100) throw new Exception('Endless cycle when pathway loading for a mdir #'.$this->id);
        }
        $pathway = array_reverse($pathway);
        return $pathway;
    }
    
}

?>
