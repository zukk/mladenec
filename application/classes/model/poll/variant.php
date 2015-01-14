<?php
class Model_Poll_Variant extends ORM {

    protected $_table_name = 'z_poll_variant';

    protected $_table_columns = array(
        'id' => '', 'poll_id'=>'', 'question_id'=>'', 'name' => '', 'sort' => '', 'free' => '', 'votes' => '',
    );
    protected $_belongs_to = array(
        'question' => array('model' => 'poll_question', 'foreign_key' => 'question_id'),
    );

    public function get_votes_by_months() {
        
        $question = $this->question;
        $votes = FALSE;
        
        switch($question->type) {
            case Model_Poll_Question::TYPE_MULTI:
            case Model_Poll_Question::TYPE_RADIO:
                $votes = DB::select(
                        array(DB::expr('DATE_FORMAT(`ts`,\'%Y %m\')'),'ym'),
                        array(DB::expr('count(*)'),'cnt'),
                        array(DB::expr('GROUP_CONCAT(DISTINCT `var_text` SEPARATOR \'|||\')'),'texts')
                        )
                        ->from('z_poll_vote')
                        ->where('var_id', '=', $this->id)
                        ->group_by('ym')
                        ->execute()
                        ->as_array('ym');
                break;
            case Model_Poll_Question::TYPE_PRIORITY:
                $votes = DB::select(
                        array(DB::expr('DATE_FORMAT(`ts`,\'%Y %m\')'),'ym'),
                        array(DB::expr('CONCAT(avg(`value`),\' из \', count(*))'),'cnt'),
                        array(DB::expr('GROUP_CONCAT(DISTINCT `var_text` SEPARATOR \'|||\')'),'texts')
                        )
                        ->from('z_poll_vote')
                        ->where('var_id', '=', $this->id)
                        ->group_by('ym')
                        ->execute()
                        ->as_array('ym');
                break;
        }
        
        return $votes;
    }
    
    /**
     * Добавить голос юзера
     * @param $user_id int
     * @param $free string
     * @return \Database_Query
     */
    public function vote($user_id, $free = '')
    {
        $ins = DB::insert('z_poll_vote', array('user_id', 'poll_id', 'question_id', 'var_id', 'var_text'))
            ->values(array($user_id, $this->poll_id, $this->question_id, $this->id, $free));

        list($tmp, $row_count)= DB::query(Database::INSERT, str_replace('INSERT', 'INSERT IGNORE', $ins))->execute();

        if (empty($row_count)) return FALSE;

        // инсерт случился!
        $this->votes++;
        $this->save();
        return TRUE;
    }
    /**
     * Добавить голос юзера - приоритет
     * @param $user_id int
     * @param $free string
     * @return \Database_Query
     */
    public function vote_value($user_id, $value, $free = '')
    {
        $ins = DB::insert('z_poll_vote', array('user_id', 'poll_id', 'question_id', 'var_id', 'value', 'var_text'))
            ->values(array($user_id, $this->poll_id, $this->question_id, $this->id, $value, $free));

        list($tmp, $row_count)= DB::query(Database::INSERT, str_replace('INSERT', 'INSERT IGNORE', $ins))->execute();

        if (empty($row_count)) return FALSE;

        // инсерт случился!
        $this->votes++;
        $this->save();
        return TRUE;
    }

}
