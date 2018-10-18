<?php
require (__DIR__ . '/../init.php');;

// Usage:
// shell> php send.php 1 a
// shell> php send.php 1 a
// shell> php send.php 1 a
// shell> php send.php 5 b
// shell> php send.php 5 b

// 队列名称
$queue_name = 'priority_queue';

// 消息优先级
$priority = (int) $argv[1];

// 消息内容
$payload = implode(' ', array_slice($argv, 2));

// 发布配置选项，可选
$options = [
    'priority' => $priority, // 消息优先级，数值越大表示优先级越高
];

$added = cls_rabbitmq::add_job($queue_name, $payload, 'rabbitmq', $options);
var_dump($added);


