<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Daemon extends Controller
{
    
    private $quest = FALSE; // Здесь объект текущей задачи
    
    public function before()
    {
 
        parent::before();
 
        //Эта проверка позволяет определить нам, был запущен этот скрипт из web
        //или из командной строки, для web вызова отправляем пользователя
        //на 404 страницу

        if( ! Kohana::$is_cli)
        {
            throw new HTTP_Exception_404('Запрашиваемая страница несуществует!');
        }
    }
    
    public function action_index()
    {
        DB::query(Database::SELECT, 'LOCK TABLES `z_daemon_quest` WRITE');
        
        $id_q = DB::select('id')->from('z_daemon_quest')
                ->where('status', '=', Model_Daemon_Quest::STATUS_NEW)
                ->or_where_open()
                    ->where('delay','>',0)
                    ->where(DB::expr('`delay` + `done_ts`'), '<', time())
                ->or_where_close()
                ->order_by('id', 'ASC')
                ->limit(1);
        
        $id = $id_q->execute()
                ->get('id');
        
        if ( ! empty($id))
        {
            DB::update('z_daemon_quest')
                    ->set(array('status' => Model_Daemon_Quest::STATUS_WORKING))
                    ->where('id' , '=' , $id)
                    ->execute();

            Log::instance()->add(Log::INFO, 'Task found!');
        }
        else 
        {
            Daemon::no_more_tasks();
            Log::instance()->add(Log::INFO, 'Tasks stack empty.');
            return;
        }
        
        DB::query(Database::SELECT, 'UNLOCK TABLES');
        
        $quest = ORM::factory('daemon_quest')
                ->where('id','=',$id)
                ->limit(1)
                ->find();
        
        $this->run_quest($quest);
    }
    
    /**
     * 
     * @param Model_Daemon_Quest $quest
     */
    public function run_quest($quest)
    {
        if (method_exists($this, 'quest_' . $quest->action) &&
            call_user_func(array($this, 'quest_' . $quest->action), $quest->params))
        {
            $quest->done_ts = time();
            $quest->status  = Model_Daemon_Quest::STATUS_DONE;
        }
        else
        {
            $quest->status = Model_Daemon_Quest::STATUS_ERROR;
            Log::instance()->add(Log::WARNING, 'Daemon: no method ' . $quest->action . ' for quest #' . $quest->id . '.');
        }
        $quest->save();
    }
  
    /************************************************************ задания **********************************************/

    /**
     * Задание: генерация электронного чека
     * @return bool
     */
    protected function quest_check($params)
    {
        $order = new Model_Order($params);
        if ( ! $order->loaded()) return FALSE;
        try {
            $order->get_check(TRUE);
        } catch (ErrorException $e) {
            Log::instance()->add(Log::WARNING, 'проблемы при создании чека для заказа '.$order->id.': '.$e->getMessage());
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Задание: Обновление данных в GR
     * @return bool
     */
    protected function quest_getresponse($params)
    {
        $decoded = json_decode($params, TRUE);
        $gr = new GetResponse();
        $gr->upload($decoded['user'], $decoded['customs']);

        return TRUE;
    }

    /**
     * Задание: Отправка смс
     * @return bool
     */
    protected function quest_sms($params)
    {
        Database::instance()->begin();
        $sms = ORM::factory('sms')
            ->where('sent_ts', '=', 0)
            ->where('status', '=', Model_Sms::STATUS_NEW)
            ->order_by('priority', 'DESC')
            ->limit(Model_Sms::SEND_RATE)
            ->find_all()
            ->as_array('id');

        if ( ! empty($sms)) {
            DB::update('z_sms')
                ->set(['status' => Model_Sms::STATUS_SENDING])
                ->where('id', 'IN', array_keys($sms))
                ->execute();
        }
        Database::instance()->commit();

        foreach($sms as $s) $s->send();

        return TRUE;
    }

    /**
     * Задание: Оптимизация картинки
     * @param $params
     * @return bool
     */
    protected function quest_image($params)
    {
        static $optimize = array(
            'png' => 'optipng -o7 ',
            'jpeg' => 'jpegoptim -f -o --strip-all --strip-icc --strip-iptc -m100 ',
            'gif' => 'gifsicle -b -O3 ',
        );
        chdir(APPPATH.'../www/');

        $ext = pathinfo($params, PATHINFO_EXTENSION);
        switch ($ext) {
            case 'gif':
            case 'png':
                exec($optimize[$ext].$params);
                break;
            default:
                exec($optimize['jpeg'].$params);
                break;
        }

        Log::instance()->add(Log::INFO, 'Daemon: image ' . $params . ' optimized by '.$ext);
        return TRUE;
    }

    /*
     * Задание: Тест задания
     */
    public function quest_test($params)
    {
        sleep(1);
        Log::instance()->add(Log::INFO, 'Test quest running.');
        return  TRUE;
    }
}