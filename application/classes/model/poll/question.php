<?php
class Model_Poll_Question extends ORM {

    const TYPE_RADIO    = 0;
    const TYPE_MULTI    = 1;
    const TYPE_TEXT     = 2;
    const TYPE_PRIORITY = 3;
    
    protected $_table_name = 'z_poll_question';

    protected $_table_columns = array(
        'id' => '', 'poll_id' => '', 'name' => '', 'sort' => '', 'type' => 0
    );
    protected $_belongs_to = array(
        'poll' => array('model' => 'poll', 'foreign_key' => 'poll_id')
    );
    protected $_has_many = array(
        'variants' => array('model' => 'poll_variant', 'foreign_key' => 'question_id')
    );
    
    public function get_texts_by_months() {
        if ( self::TYPE_TEXT != $this->type) return FALSE;
        
        $votes = DB::select(
                array(DB::expr('DATE_FORMAT(`ts`,\'%Y %m\')'),'ym'),
                array(DB::expr('GROUP_CONCAT(DISTINCT `var_text` SEPARATOR \'|||\')'),'texts')
            )
            ->from('z_poll_vote')
            ->where('question_id', '=', $this->id)
            ->where('var_id', '=', 0)
            ->group_by('ym')
            ->execute()
            ->as_array('ym');
        $return = array();
        foreach($votes as $month=>$v) $return[$month] = explode('|||', $v['texts']);
        return $return;
    }
    
    public function vote_text($user_id,$text) {
        $text = trim($text);
        $ins = DB::insert('z_poll_vote', array('user_id', 'poll_id', 'question_id', 'var_text'))
            ->values(array($user_id, $this->poll_id, $this->id, $text));
        
        $ins->execute();
        return TRUE;
    }

}