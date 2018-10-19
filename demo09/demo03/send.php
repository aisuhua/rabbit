<?php
require (__DIR__ . '/../init.php');;

// 队列名称
$queue_name = 'options_queue';

// 消息内容
$payload = implode(' ', array_slice($argv, 1));

// 发布配置选项，可选
$options = [
    'exchange_name' => '115.web', // 使用的交换器
    'queue_prefix' => '115.web.', // 设置队列前缀
];

$added = cls_rabbitmq::add_job($queue_name, $payload, 'rabbitmq', $options);
var_dump($added);


/*
shell> php send.php suhua is a good boy.
 */