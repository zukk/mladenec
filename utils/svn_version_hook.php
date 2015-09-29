<?php

require('../www/preload.php');

define('_FILE', APPPATH . 'views/layout/assets/common.tpl');
unlink(_FILE);
chdir(APPPATH . '../');
exec('svn up');

$vdata = explode(':', exec('svnversion'));
$version = intval(array_pop($vdata));

file_put_contents(_FILE, str_replace('$SVN$', $version, file_get_contents(_FILE)));

$yui = 'java -jar ../utils/yuicompressor-2.4.8.jar --charset utf-8 --type ';

chdir('www');
exec('cat ' . implode(' ', Controller_Frontend::$css) . ' > c/style.combined.css'); // клеим css
exec('cat ' . implode(' ', Controller_Frontend::$scripts) . ' > j/script.combined.js'); // клеим js

exec($yui . 'css c/style.combined.css -o c/style.min.css'); // жмем css
exec($yui . 'js j/script.combined.js -o j/script.min.js'); // жмем js
