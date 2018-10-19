<?php
/**
 * 优先级队列 add_job
 */
require (__DIR__ . '/../../demo09/init.php');

// 队列名称
$queue_name = 'priority_queue';

// 消息优先级
$priority = (int) $argv[1];

// 消息内容
$payload = implode(' ', array_slice($argv, 2));

// 其他参数
$options = [
    // 消息优先级，数值越大表示优先级越高
    'priority' => $priority,
];

$added = cls_rabbitmq::add_job($queue_name, $payload, 'rabbitmq', $options);
var_dump($added);