<?php
/**
 * 
 *
 * @author mit08
 */
class Controller_Authorised extends Controller_Smarty {
    
    /**
     * Массив для хранения сообщений, которые позже будут переданы в View
     * @var array
     */
    protected $messages = [
        'errors' => [],
        'messages' => []
    ];
    
    protected $send_messages = TRUE;    // Позволить отправлять сообщения в вид
    protected $send_errors = TRUE;      // Позволить отправлять ошибки в вид
    
    /**
     *
     * @var ORM
     */
    protected $model = NULL;
    
    protected $allow;
    
    /**
     * Current user
     * @var Model_User
     */
    protected $user;
    
    public function before()
    {
        parent::before();
        
        if (empty($this->user)) throw new HTTP_Exception_403;
        
        $this->allow = $this->user->allow();
        if (empty($this->allow)) throw new HTTP_Exception_404; // нет доступа в админку вообще
        
        $model_name = $this->request->param('model');

        if ($model_name && ! $this->user->allow($model_name)) { // доступа к модулю нет
            $this->request->redirect(Route::url('admin')); // отправим обратно в корень админки
        }
    }
    
    public function after()
    {
        parent::after();
    }
    
    /**
     * Анализ и создание модели для построения формы
     * @param $name
     * @param mixed $id
     * 
     * @return ORM 
     */
    public function model($name, $id = FALSE)
    {
        if ($id === FALSE) $this->model = ORM::factory($name);
        $this->model = ORM::factory($name, $id);
    }
    
    protected function save_form_images($model)
    {
        $fields = $model->img();
        
        $sources = array();
        foreach ($fields as $master_key => $master_resize) {
            
            /* Пропускаем незагруженные изображения */
            if (empty($_FILES[$master_key]['size']))                continue;
            
            /* Пропускаем изображения, которые формируются из других полей */
            if ( ! empty($master_resize[2])) continue;
            
            $file = Model_File::image($master_key);
            
            $sources[$master_key] = $file;
            if ( ! empty($master_resize[0]) AND ! empty($master_resize[1])) {
                $width = $master_resize[0];
                $height = $master_resize[1];
                
                $model->{$master_key} = $file->resizeWH($width, $height);
            } else {
                $model->{$master_key}  = $file;
            }
        }
        
        foreach ($fields as $slave_key => $slave_resize) {
            
            /* Пропускаем основные изображения */
            if (empty($slave_resize[2])) continue;
            
            $master_key = $slave_resize[2];
            
            if (empty($sources[$master_key]))                continue;
            
            if( ! ($sources[$master_key] instanceof Model_File)) {
                /* Нет основного изображения, из которого можно было бы сформировать подчиненное */
                Log::instance()->add(Log::INFO, 
                        'Unable to make image for ' 
                        . $slave_key . ' from ' . $master_key 
                        . ' in ' . $model->object_name() 
                        . ' - no source!'
                        );
                continue;
            }
            
            $width = $slave_resize[0];
            $height = $slave_resize[1];
            
            $model->{$slave_key} = $sources[$master_key]->resizeWH($width, $height);
        }
        
        return $model;
    }
    
    /**
     * 
     * @param Model $model
     * @param array $form_data
     * @param array $ignore_fields
     *
     * @return bool
     * @throws Exception
     */
    protected function save_form($model, $form_data, $ignore_fields = array('_misc'))
    {
        $is_okey = FALSE;
        /* По умолчанию - обновление */
        $add = FALSE;
        
        if ( ! is_array($form_data)) {
            $this->msg('Cannot save - wrong data','errors');
            return FALSE;
        }

        if ( ! ($model instanceof ORM)) {
            $this->msg('Невозможно сохранить несуществующий объект','errors');
            return FALSE;
        }
        if ( ! $model->loaded()) {
            /* Создание объекта, вместо его обновления */
            $add = TRUE;
        }
            
        if (method_exists($model, 'flag')) {  // reset checkboxes if no value only
            foreach($model->flag() as $f) {
                $model->{$f} = empty($form_data[$f]) ? '0' : '1';
            }
        }
        
        $misc = null;
        if ( ! empty($form_data['misc'])) $misc = $form_data['misc'];
        
        foreach($ignore_fields as $field) {
            if(isset($form_data[$field])) unset($form_data[$field]);
        }
        
        foreach($form_data as $n => $v) {
            if (is_array($v) AND isset($v['Date_Day'])) {
                $form_data[$n] = $this->read_date($v); // если это дата - прочитать как дату
            }
        }
        
        $model->values($form_data, array_keys($form_data));

        if ($model->validation()->check()) {
            
            $changed = $model->changed(); // это массив изменённых полей
            $model->save(); // сохраним основные данные
            
            /* Устанавливаем связи */
            if ( ! empty($misc['bind']) AND is_array($misc['bind'])) {
                foreach ($misc['bind'] as $rel_alias=>$rel_ids) {
                    foreach ($rel_ids as $rid) {
                        if ($rid > 0 AND ! $model->has($rel_alias,$rid)) {
                            $model->add($rel_alias, $rid);
                        }
                    }
                }
            }
            
            if ($add) {
                Model_History::log($model->object_name(), $model->id, 'add', $model->as_array());
            } elseif ($changed) { // запишем новое в историю
                Model_History::log($model->object_name(), $model->id, 'edit', $model->as_array());
            }

            if (method_exists($model, 'admin_save')) {
                /* дополнительные действия над моделью 
                 * если надо что-то внутри добавлять в model_history - то это надо сделать внутри admin_save
                 * admin_save может вернуть массив сообщений, для отображения пользователю
                 */
                $messages = $model->admin_save();
                $this->messages_add($messages);
                $is_okey = empty($messages['errors']);
            } else {
                $is_okey = TRUE;
            }
        } else {
            $errors = $model->validation()->errors('admin/'.$model->object_name());
            $this->messages_add(['errors' => $errors]);
        }
        
        return $is_okey;
    }
    /**
     * Получить сообщения по типу
     * 
     * @param string $type
     * @return boolean
     */
    protected function get_messages($type = 'messages')
    {
        if (is_array($this->messages[$type]) AND ! empty($this->messages[$type])) {
            return ($this->messages[$type]);
        } else return FALSE;
    }
    
    /**
     * Add new message to common message heap
     *
     * @param $message 
     * @param string $type
     */
    protected function msg($message, $type = 'messages')
    {
        $message = trim($message);
        if ( ! empty($message)) {
            if (isset($this->messages[$type]) AND is_array($this->messages[$type])) {
                $this->messages[$type][] = $message;
            } else {
                Log::instance()->add(LOG_INFO, __METHOD__.': Unknown message type - '.$type);
            }
        }
    }

    /**
     * Carefully adds array of new messages to comon message heap
     *
     * @param array $messages
     * @return bool
     */
    protected function messages_add($messages)
    {
        foreach($this->messages as $message_type => $message_list) {
            if ( isset($messages[$message_type]) AND ( ! is_array($messages[$message_type]))) {
                $this->messages[$message_type] = $messages[$message_type];
            }
            if ( ! empty($messages[$message_type])) {
                $this->messages[$message_type] = array_merge($message_list, $messages[$message_type]);
            }
        }
    }
}
