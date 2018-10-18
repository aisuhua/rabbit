<?php
require (__DIR__ . '/../init.php');;

// Usage: shell> php send.php Hello World!

// 队列名称
$queue_name = 'my_queue';

// 消息内容
$payload = implode(' ', array_slice($argv, 1));
// $payload = array_slice($argv, 1);

$added = cls_rabbitmq::add_job($queue_name, $payload, 'rabbitmq');
var_dump($added);


