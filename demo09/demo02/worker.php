<?php
require (__DIR__ . '/../init.php');

// 队列名称
$queue_name = 'priority_queue';

// 回调函数
$callback = function($params)
{
    var_dump($params);
    sleep(3);
};

// 消费配置选项，可选
$options = [
    'x_max_priority' => 10, // 队列最大优先级，创建后该数值无法修改
];

cls_rabbitmq::do_job($queue_name, $callback, 'rabbitmq', $options);