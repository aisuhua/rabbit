<?php
require (__DIR__ . '/../init.php');;

// 队列名称
$queue_name = 'my_queue';

// 字符串消息
$payload = implode(' ', array_slice($argv, 1));
// 将消息发送到队列
$added = cls_rabbitmq::publish($queue_name, $payload, 'rabbitmq');
var_dump($added);

// 数组消息
$payload = array_slice($argv, 1);
// 将消息发送到队列
$added = cls_rabbitmq::publish($queue_name, $payload, 'rabbitmq');
var_dump($added);


/*
shell> php send.php Hello World!
 */
