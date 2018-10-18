<?php
/**
 * RabbitMQ 服务配置信息
 * 如提供了多个 RabbitMQ 服务，程序将随机连接服务正常的其中一台
 */
$GLOBALS['config']['rabbitmq'] = [
    [
        'host' => '172.16.0.108',
        'port' => '5672',
        'vhost' => '/',
        'login' => 'guest',
        'password' => 'guest',
    ]
];
