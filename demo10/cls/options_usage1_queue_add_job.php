<?php
/**
 * 自定义交换器和队列前缀 add_job
 */
require (__DIR__ . '/../../demo09/init.php');

// 队列名称
$queue_name = 'options_usage1_queue';

// 消息内容
$payload = implode(' ', array_slice($argv, 1));

// 其他参数
$options = [
    'exchange_name' => '115.web', // 交换器
    'queue_prefix' => '115.web.', // 队列前缀
];

$added = cls_rabbitmq::add_job($queue_name, $payload, 'rabbitmq', $options);
var_dump($added);