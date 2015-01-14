<?php

$child_pid = pcntl_fork();

if (-1 == $child_pid) {
    exit('Fork error.');
} elseif ($child_pid) {
    // Выходим из родительского, привязанного к консоли, процесса
    exit($child_pid);
}

date_default_timezone_set('Europe/Moscow');

if (!defined('DAEMON_APPPATH')) define('DAEMON_APPPATH', realpath(dirname(__FILE__)));
if (!defined('DAEMON_STOP_FILE')) define('DAEMON_STOP_FILE', '');
if (!defined('DAEMON_PAUSE_FILE')) define('DAEMON_PAUSE_FILE', '');

require_once DAEMON_APPPATH . '/classes/daemon.php';

// Дальнейший код выполнится только дочерним процессом, который уже отвязан от консоли
// Делаем основным процессом дочерний.
posix_setsid();

$daemon = new Daemon();

$daemon->log('Pid ' . posix_getpid() . ' born.');

$running = $daemon->run();

$daemon->log('Pid ' . posix_getpid() . ' dead.', TRUE);
