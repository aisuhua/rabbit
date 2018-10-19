<?php
require (__DIR__ . '/../init.php');;

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


/*
shell> php send.php 1 1
shell> php send.php 1 2
shell> php send.php 1 3
shell> php send.php 2 4
shell> php send.php 2 5
shell> php send.php 3 6
 */

