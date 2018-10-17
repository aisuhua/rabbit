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
$payload = 'lala23424';

$added = cls_rabbitmq::add_job($queue_name, $payload, 'rabbitmq');

var_dump($added);