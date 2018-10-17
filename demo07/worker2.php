<?php

require ('demo2.php');

// test
$GLOBALS['config']['rabbitmq'][] = [
    'host' => '172.16.0.108',
    'port' => '5672',
    'vhost' => '/',
    'login' => 'guest',
    'password' => 'guest',
];
$queue_name = 'suhua';

$callback = function($params)
{
    echo $params, PHP_EOL;

    return 'fuck you.';
};

cls_rabbitmq::do_job($queue_name, $callback, 'rabbitmq');