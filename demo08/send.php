<?php
require ('config.php');
require ('cls_rabbitmq.php');
require ('functions.php');

$queue_name = 'suhua';
$payload = implode(' ', array_slice($argv, 1));

// 自定义其他配置
$options = [
    'exchange_name' => 'router',
    'queue_prefix' => 'aisuhua.'
];

while(true)
{
    $added = cls_rabbitmq::add_job($queue_name, $payload, 'rabbitmq', $options);

    var_dump($added);
}

