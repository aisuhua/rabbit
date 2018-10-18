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
$payload = implode(' ', array_slice($argv, 1));

// 自定义其他配置
$options = [
    'exchange_name' => 'router',
    'queue_prefix' => 'aisuhua.'
];

$added = cls_rabbitmq::add_job($queue_name, $payload, 'rabbitmq', $options);

var_dump($added);