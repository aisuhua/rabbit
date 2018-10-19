<?php
/**
 * 自定义交换器和队列前缀 do_job
 */
require (__DIR__ . '/../../demo09/init.php');

// 队列名称
$queue_name = 'options_usage1_queue';

/**
 * 处理消息的回调函数
 *
 * @param string|array $params 消息内容
 */
$callback = function($params)
{
    var_dump($params);
};

// 其他参数
$options = [
    'exchange_name' => '115.web', // 交换器
    'queue_prefix' => '115.web.', // 队列前缀
];

cls_rabbitmq::do_job($queue_name, $callback, 'rabbitmq', $options);
