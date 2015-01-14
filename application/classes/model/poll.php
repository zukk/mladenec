<?php
class Model_Poll extends ORM {
    /*
     * @property id
     * @property name
     * @property active
     * @property closed
     * @property type
     */
    
    const TYPE_DEFAULT          = 0;
    const TYPE_ORDER_COMPLETE   = 1;
    const TYPE_REGISTER         = 2;
    
    protected $_table_name = 'z_poll';

    protected $_has_many = array(
        'questions' => array('model' => 'poll_question', 'foreign_key' => 'poll_id')
    );

    protected $_table_columns = array(
        'id' => '', 'name' => '', 'active' => '', 'closed' => '', 'new_user' => '','type'=>''
    );

    /**
     * @return array
     */
    public function rules()
    {
        return array(
            'name' => array(
                array('not_empty'),
            ),
        );
    }

    public function get_type_name() {
        $name = '';
        switch ($this->type) {
            case self::TYPE_ORDER_COMPLETE:
                $name = 'При завершении заказа';
                break;
            case self::TYPE_REGISTER:
                $name = 'При регистрации';
                break;
            default:
                $name = 'Обычный опрос';
                break;
        }
        return $name;
    }
    
    /**
     * возвращает массив опросов, в которых участвовал юзер, с указанием варианта
     * @param $user_id
     * @return array
     */
    public static function votes($user_id)
    {
        $user_votes = DB::select('poll_id', 'var_id')
            ->from('z_poll_vote')
            ->where('user_id', '=', $user_id)
            ->execute()
            ->as_array('poll_id', 'var_id');

        return $user_votes;
    }

    /**
     * Список чекбоксов
     * @return array
     */
    public function flag()
    {
        return array('active', 'closed');
    }

    public function vote_handler($user_id) {
        Log::instance()->add(Log::INFO, 'User ' . $user_id . ' votes for poll ' . $this->id);
        
        if (empty($user_id)) {
            Log::instance()->add(Log::INFO, 'Empty user trying to vote');
            return FALSE;
        }
        
        $already_vote = DB::select('poll_id')
            ->from('z_poll_vote')
            ->where('user_id', '=', $user_id)
            ->where('poll_id', '=', $this->id)
            ->execute()
            ->get('poll_id');
        if ($already_vote) {
            Log::instance()->add(Log::INFO, 'User ' . $user_id . ' trying to vote twice, poll: ' . $this->id);
            return FALSE; 
        }
        
        $questions = $this->questions->find_all()->as_array('id');

        $text_answers   = Request::current()->post('q_text');
        $free           = Request::current()->post('free'); // свободные варианты

        $ok = FALSE;

        foreach ($questions as $qstn_id => $qstn) {
            /** 
             * @var $qstn Model_Poll_Question 
             */
            switch ($qstn->type) {
                case Model_Poll_Question::TYPE_RADIO:
                    $poll_var = Request::current()->post('poll_var'); // Вариант отмечен
                    /** 
                     * @var $var Model_Poll_Variant
                     */
                    $var = $qstn->variants->where('id', '=', $poll_var)->find();

                    if ( ! $var->loaded())      continue;

                    if ($var->free AND ! empty($free[$var->id])) $var->vote($user_id, $free[$var->id]);
                    else $var->vote($user_id);

                    unset ($var);
                    $ok = TRUE;
                    break;
                case Model_Poll_Question::TYPE_MULTI:
                    $variants = $qstn->variants->where('question_id', '=', $qstn_id)->find_all()->as_array('id');
                    /* @var $var Model_Poll_Variant */
                    foreach ($variants as $v_id => $var) {
                        if ( ! $var->loaded())      continue;
                        if (empty($user_id)) continue;

                        $poll_var = Request::current()->post('poll_var_' . $v_id); // Вариант отмечен
                        if ( ! empty($poll_var)) {
                            if ($var->free AND ! empty($free[$v_id])) $var->vote($user_id, $free[$v_id]);
                            else $var->vote($user_id);
                        }
                    }
                    unset ($variants);
                    unset ($var);
                    unset ($v_id);
                    $ok = TRUE;
                    break;
                case Model_Poll_Question::TYPE_TEXT:
                    if ( ! empty($text_answers[$qstn_id])) $qstn->vote_text($user_id, $text_answers[$qstn_id]);
                    $ok = TRUE;
                    break;
                case Model_Poll_Question::TYPE_PRIORITY:
                    $variants = $qstn->variants->where('question_id', '=', $qstn_id)->find_all()->as_array('id');
                    /* @var $var Model_Poll_Variant */
                    foreach ($variants as $v_id => $var) {

                        if ( ! $var->loaded())      continue;
                        if (empty($user_id)) continue;

                        $poll_var = Request::current()->post('poll_var_' . $v_id); // Вариант отмечен
                        if ( ! empty($poll_var)) {
                            if ($var->free AND ! empty($free[$var->id])) $var->vote_value($user_id, $poll_var, $free[$var->id]);
                            else $var->vote_value($user_id, $poll_var); 
                        }
                    }
                    unset ($variants); 
                    unset ($var);
                    unset ($v_id);
                    $ok = TRUE;
                    break;
            }
        }
        return $ok;
    }
    
    /**
     * сохранение опроса в админке - сохранение вариантов ответа
     */
    public function admin_save()
    {
        $request = Request::current();
        $misc = $request->post('misc');
        
        // Обновление вопросов {{{
        if ( ! empty($misc['questions']) AND is_array($misc['questions'])) $questions = $misc['questions'];
        $old_questions = $this->questions->find_all()->as_array('id');
        if ( ! empty($old_questions)) {
            $changes = FALSE;
            foreach($old_questions as $qstn_id => $qstn) {
                if (empty($questions[$qstn_id])) { 
                    $qstn->delete();
                    $changes = TRUE;
                } else {
                    $qstn->name = $questions[$qstn_id]['name'];
                    $qstn->sort = $questions[$qstn_id]['sort'];
                    $qstn->type = $questions[$qstn_id]['type'];
                    if ($qstn->changed()) {
                        $changes = TRUE;
                        $qstn->save();
                    }
                }
            }
            if ($changes) Model_History::log('poll', $this->id, 'questions changed', (empty($questions)?'delete all':$questions));
        }
        // Чистим, чтобы дальше не мучиться с названиями
        if (isset($questions)) unset($questions);
        if (isset($qstn)) unset($qstn);
        // }}}
        
        // Обновление вариантов {{{
        if ( ! empty($misc['variants']) AND is_array($misc['variants'])) $variants = $misc['variants'];
        $questions = $this->questions->find_all()->as_array('id'); // Надо перезагрузить вопросы, чтобы учесть все изменения
        $changes = FALSE;
        $changes_data = array();
        foreach ($questions as $qstn_id => $qstn) {
            $old_variants = $qstn->variants->find_all()->as_array('id');
            if ( ! empty($old_variants)) {
                foreach($old_variants as $var_id => $var) {
                    if (empty($variants[$var_id])) { 
                        $var->delete();
                        $changes = TRUE;
                    } else {
                        $var->name = $variants[$var_id]['name'];
                        $var->sort = $variants[$var_id]['sort'];
                        $var->free = ! empty($variants[$var_id]['free']) ? '1' : '0';
                        if ($var->changed()) {
                            $changes = TRUE;
                            $changes_data[] = $variants[$var_id];
                            $var->save();
                        }
                    }
                }
            }
        }
        if ($changes) Model_History::log('poll', $this->id, 'variants changed', (empty($variants)?'delete all':$changes_data));
        // Чистим, чтобы дальше не мучиться с названиями
        if (isset($questions)) unset($questions);
        if (isset($qstn)) unset($qstn);
        if (isset($variants)) unset($variants);
        if (isset($var)) unset($var);
        //}}}
                
        // Новые вопросы
        if ( ! empty($misc['new_q_name']) AND is_array($misc['new_q_name'])) {
            $new_q_name = array_map('trim',$misc['new_q_name']);
            $new_q_sort = array_map('trim',$misc['new_q_sort']);
            $new_q_type = array_map('trim',$misc['new_q_type']);
            
            $ins = DB::insert('z_poll_question')->columns(array('poll_id', 'name', 'sort','type'));
            $do_insert = FALSE;
            foreach($new_q_name as $k => $name) {
                if ( ! empty($name)) {
                    $do_insert = TRUE;
                    $ins->values(array($this->id, $name, $new_q_sort[$k],$new_q_type[$k]));
                }
            }
            if ($do_insert) {
                $ins->execute();
                Model_History::log('poll', $this->id, 'questions added', $new_q_name);
            }
        }
        
        // Новые варианты
        if ( ! empty($misc['new_var']) AND is_array($misc['new_var'])) {
            foreach($misc['new_var'] as $q_id => $nv) { // Перебрать по вопросам
                if ( empty($nv['name']) AND ! is_array($nv['name'])) continue;
                
                $ins = DB::insert('z_poll_variant')->columns(array('poll_id', 'question_id', 'name','free', 'sort'));
                $do_insert = FALSE;
                foreach($nv['name'] as $k=>$nv_name) {
                    if ( ! empty($nv_name)) {
                        $do_insert = TRUE;
                        $ins->values(array($this->id, $q_id, $nv_name, ( ! empty($nv['free'][$k]) ? 1 : 0), $nv['sort'][$k]));
                    }
                }
                if ($do_insert) {
                    $ins->execute();
                    Model_History::log('poll', $this->id, 'varints added', $misc['new_var']);
                }

            }
        }
    }
}
