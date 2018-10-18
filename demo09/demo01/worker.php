<?php
require (__DIR__ . '/../init.php');

// 队列名称
$queue_name = 'my_queue';

// 回调函数
$callback = function($params)
{
    var_dump($params);
};

cls_rabbitmq::do_job($queue_name, $callback, 'rabbitmq');