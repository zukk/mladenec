<?php
class Daemon
{
    const LOG_BUFFER_SIZE   = 10;
    const ALIVE_FILE        = 'cache/daemon/master_alive';
    const IMP_ALIVE_FILE    = 'cache/daemon/imp_alive';
    const STOP_FILE         = 'cache/daemon/stop';
    const PAUSE_FILE        = 'cache/daemon/pause';
    const TASK_FILE         = 'cache/daemon/task';
    const LIFETIME          = 0;
    const LOG_FILE_SUFFIX   = 'daemon';
    const MAX_IMPS          = 5;
    const ERROR_DELAY       = 20;   // Пауза в секундах после выхода потомка с ненулевым кодом
    
    private $imps           = array();  // [pid=>start_timestamp]
    private $stop           = FALSE;    // Флаг остановки
    private $pause          = FALSE;    // Флаг паузы, не создавать импов
    private $recount_childs = FALSE;    // Флаг остановки
    private $logs           = array();  // Массив логов
    private $start_ts       = 0;
    private $delay          = 1;
    private $tasks_counter  = 1; // We are to check tasks on startup
    
    public static function stop($go = FALSE)
    {
        $filename = self::get_appath() . self::STOP_FILE;
        
        if ( ! $go) touch($filename);
        elseif (file_exists($filename)) unlink($filename);
    }
    
    public static function pause($go = FALSE)
    {
        $filename = self::get_appath() . self::PAUSE_FILE;
        
        if ( ! $go) touch($filename);
        elseif (file_exists($filename)) unlink($filename);
    }
    
    public static function new_task()
    {
        if (class_exists('Log'))
        {
            Log::instance()->add(Log::INFO, 'Daemon: new task!');
        }
        
        if (defined('SIGUSR1'))
        {
            $master_pid = self::get_pid_by_file(self::ALIVE_FILE);

            return posix_kill($master_pid, SIGUSR1);
        }
        else
        {
            return touch(self::get_appath() . self::TASK_FILE);
        }
    }
    
    public static function no_more_tasks()
    {
        if (class_exists('Log'))
        {
            Log::instance()->add(Log::INFO, 'Daemon: no more tasks');
        }
        
        $return = self::unlink_tasks_file();
        
        if (defined('SIGUSR2'))
        {
            $master_pid = self::get_pid_by_file(self::ALIVE_FILE);
            
            $return = posix_kill($master_pid, SIGUSR2);
        }
        
        return $return;
    }
    
    public function new_tasks_handler()
    {
        $this->tasks_counter++;
        $this->log('Have a new job.');
    }
    
    public function no_tasks_handler()
    {
        $this->tasks_counter = 0;
        $this->log('No more tasks.');
    }
    
    public function child_dead_handler()
    {
        $this->recount_childs = TRUE;
        $this->log('Child dead!!!');
    }
    
    public function stop_signal_handler()
    {
        $this->stop = TRUE;
        $this->log('Stopping by signal.');
    }
    
    public function run()
    {
        if( ! $this->write_alive_file(self::ALIVE_FILE)) exit('Master: Already running!');
        
        pcntl_signal(SIGHUP,  array($this, 'stop_signal_handler'));
        pcntl_signal(SIGUSR1, array($this, 'new_tasks_handler'));
        pcntl_signal(SIGUSR2, array($this, 'no_tasks_handler'));
        
        while ( $this->executon_allowed() )
        {
            if (0 == $this->tasks_counter AND file_exists(self::get_appath() . self::TASK_FILE)) 
            {
                $this->log('Task file found');
                $this->tasks_counter = 1;
            }
            
            if ( ! $this->pause AND $this->count_running_imps() < self::MAX_IMPS AND $this->tasks_counter > 0)
            {
                $child_pid = $this->run_imp();
                $this->imps[$child_pid] = time();
                $this->log('Imps running: ' . count($this->imps));
            }
            
            sleep($this->delay);
            
            pcntl_signal_dispatch();
            
            gc_collect_cycles();
            
//            $this->cycle_counter ++; // never found anywhere else
        }
        
        $this->unlink_alive_file(self::ALIVE_FILE);
    }
    
    public function log($string, $flush = FALSE)
    {
        $log = date('Y-m-d H:i:s ') 
                . get_class($this) 
                . ', pid:' . posix_getpid() 
                . ', memory: ' . memory_get_usage() . ', real: ' .  memory_get_usage(TRUE) . ' bytes, ' 
                . (time() - $this->start_ts) . 's. ' 
                . $string;
        
        $this->logs[] = $log;

        if ($flush OR count($this->logs >= self::LOG_BUFFER_SIZE)) $this->flush_log();
    }

    public function __construct()
    {
        $this->start_ts = time();
    }
    
    private static function get_pid_by_file($alive_file)
    {
        if( ! file_exists(self::get_appath() . $alive_file)) return FALSE;
        
        $old_alivefile = file_get_contents(self::get_appath() . $alive_file);

        return intval( substr( $old_alivefile, 0, strpos( $old_alivefile, ',' ) ) );
    }
    
    private function check_alive ($alive_file)
    {
        $pid = self::get_pid_by_file($alive_file);

        if ($pid AND posix_kill ($pid , 0 ))
        {
            $this->log('Instance with ' . $pid . ' already alive');
            return TRUE;
        }
        else
        {
            $this->unlink_alive_file($alive_file);
            return FALSE;
        }
    }
    
    private function write_alive_file ($alive_file)
    {
        if($this->check_alive($alive_file)) return FALSE;

        if (FALSE === file_put_contents(self::get_appath() . $alive_file, posix_getpid() . ',' . time(), LOCK_EX))
        {
            $this->log('Unable to write alive file: ' . self::get_appath() . $alive_file);
            exit('Unable to write alive file: ' . self::get_appath() . $alive_file);
        }
        
        return TRUE;
    }
    
    private function unlink_alive_file($alive_file)
    {
        if (file_exists(self::get_appath() . $alive_file))
        {
            $this->log('Deleting alive file: ' . self::get_appath() . $alive_file);
            unlink(self::get_appath() . $alive_file);
        }
    }
       
    private static function unlink_tasks_file()
    {
        if (file_exists(self::get_appath() . self::TASK_FILE))
        {
            return unlink ( self::get_appath() . self::TASK_FILE );
        }
    }
    
    private function count_running_imps() 
    {
        $pids = array_keys($this->imps);
        
        foreach ($pids as $p)
        {
            $status = null;
            $exited = pcntl_waitpid($p, $status, WNOHANG);
            
            if (0 !== $exited)
            {
                $this->on_imp_exit($p, $status);
            }
        }
        
        return count($this->imps);
    }
    
    private function on_imp_exit($imp_pid, $status)
    {
        $this->log('Imp ' . $imp_pid . ' exited with status ' . $status);
        unset($this->imps[$imp_pid]);
        
        if (0 != $status)
        {
            $this->on_imp_error($imp_pid);
        }
    }
    
    private function on_imp_error($imp_pid)
    {
        // Уменьшаем скорость работы, чтобы разгрузить сервер на время, пока есть фатальные ошибки
        sleep(self::ERROR_DELAY); 

        // Файл скорее всего не был удален - удаляем, т.к. потомок уже завершился
        $this->unlink_alive_file(self::IMP_ALIVE_FILE . '_' . $imp_pid);
    }
    
    private function run_imp()
    {
        $child_pid = pcntl_fork();

        if (-1 == $child_pid)
        {
            $this->log('Unable to run imp');
            
        }
        elseif ($child_pid)
        {
            // Здесь у нас код мастера, просто возвращаем PID
            // $this->tasks_counter--;
            return $child_pid;
        }
        
        $pid = posix_getpid();
        
        $this->write_alive_file(self::IMP_ALIVE_FILE . '_' . $pid);
        
        $this->log('Born. Pid: ' . $pid);
        
        require_once(self::get_appath() . '../www/preload.php');
        
        //Log::instance()->add(Log::INFO, 'Imp working, YO=1!!!');
        
        $reply = Request::factory('/daemon/imp')->execute()->body();
        
        $this->log('Controller reply: ' . $reply);
        
        $this->log('Imp dead. Pid: ' . $pid);
        
        $this->unlink_alive_file(self::IMP_ALIVE_FILE . '_' . $pid);
        
        // принудительно завершаем работу, 
        // чтобы клон случайно не остался в памяти.
        exit(0);
    }
    
    private function executon_allowed()
    {
        if (( self::LIFETIME > 0 ) AND ( time() >= $this->start_ts + self::LIFETIME )) $this->stop = TRUE;
        
        if (file_exists(self::get_appath() . self::STOP_FILE))
        {
            $this->log('Stop flag found!');
            $this->stop = TRUE;
        }
        
        if ( file_exists(self::get_appath() . self::PAUSE_FILE))
        {
            if ( FALSE == $this->pause ) $this->log('Pause flag found!');
            $this->pause = TRUE;
        }
        else
        {
            $this->pause = FALSE;
        }
        
        if ($this->stop) $this->do_stop();
        
        return ( ! $this->stop);
    }
    
    private function do_stop()
    {
        $this->log('Stopping, wait while child exits');
        while (count($this->imps) > 0) {
            $this->count_running_imps();
            
            $sig = pcntl_sigtimedwait ( array(SIGCHLD), $siginfo, $this->delay );
            $this->log('Got a signal: ' . $sig);
        }
    }
    
    private function flush_log()
    {
        $log_dir = self::get_appath() . 'logs/' . date('Y/m');

        if ( ! file_exists($log_dir)) mkdir($log_dir, 0777, TRUE);

        // Saving logs to file
        file_put_contents(
                $log_dir . '/' . date('d_') . self::LOG_FILE_SUFFIX . '.log',
                implode(PHP_EOL, $this->logs) . PHP_EOL  ,
                FILE_APPEND | LOCK_EX
                );

        $this->logs = array();
    }
    
    /**
     * Возвращает правильный путь к application
     * 
     * @return string
     */
    private static function get_appath()
    {
        if      ( defined('APPPATH'))        return APPPATH;
        elseif  ( defined('DAEMON_APPPATH')) return DAEMON_APPPATH . '/';
        
        return dirname(__FILE__);
    }
}