<?php
/**
 * @author miaokuan
 */

if (substr(php_sapi_name(), 0, 3) !== 'cli') {
    die("only run in cli mode.");
}

error_reporting(E_ALL & ~E_NOTICE);
define('SIM_ROOT', dirname(__DIR__));
chdir(SIM_ROOT);
set_include_path(__DIR__ . ':' . SIM_ROOT . '/src');

require 'Sim/Common.php';
$config = array();
$config['exception'] = true;
$config['autoload'] = true;
Sim\Common::init($config);

$run = new Sim\Run();
