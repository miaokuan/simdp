<?php
/**
 * @author miaokuan
 */

define('APP_PATH', dirname(__DIR__));

error_reporting(E_ALL & ~E_NOTICE);
define('SIM_ROOT', dirname(APP_PATH));
chdir(SIM_ROOT);
set_include_path('.:' . SIM_ROOT . '/src');

require 'Sim/Common.php';
$config = array();
$config['exception'] = true;
$config['autoload'] = true;
Sim\Common::init($config);

$app = new Yaf_Application(APP_PATH . '/conf/app.ini');
$app->bootstrap()->run();

