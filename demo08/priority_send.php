<?php
require ('config.php');
require ('cls_rabbitmq.php');
require ('functions.php');

$queue_name = 'priority-suhua';
$priority = $argv[1];
$payload = array_slice($argv, 2);

$options = [
    'priority' => $priority, // 消息优先级
];

$added = cls_rabbitmq::add_job($queue_name, $payload, 'rabbitmq', $options);
var_dump($added);


