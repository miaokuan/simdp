<?php

$config = [];

//File cache
$config['File']['dir'] = WEE_ROOT . '/var/cache';

//Memcached cache
$config['Memcached']['servers'] = ['127.0.0.1:4730'];

return $config;
