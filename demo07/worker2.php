<?php

require('cls_rabbitmq.php');

// test
$GLOBALS['config']['rabbitmq'] = [
    [
        'host' => '172.16.0.108',
        'port' => '5672',
        'vhost' => '/',
        'login' => 'guest',
        'password' => 'guest',
    ]
];

$queue_name = 'suhua';
$callback = function($params)
{
    echo $params, PHP_EOL;
    $start_time = microtime(true);

    sleep(10);

    $cost_time = microtime(true) - $start_time;
    echo 'done, time ', $cost_time, PHP_EOL;
    return true;
};

// 自定义其他配置
$options = [
    'exchange_name' => 'router',
    'queue_prefix' => 'aisuhua.'
];

cls_rabbitmq::do_job($queue_name, $callback, 'rabbitmq', $options);