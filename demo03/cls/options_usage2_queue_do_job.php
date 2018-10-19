<?php
/**
 * auto_ack
 */
require (__DIR__ . '/../init.php');

// 队列名称
$queue_name = 'my_queue';

/**
 * 处理消息的回调函数
 *
 * @param string|array $params 消息内容
 */
$callback = function($params)
{
    var_dump($params);
    sleep(10);
};

// 其他参数
$options = [
    // 开启消息的自动确认
    // 若该队列的每条消息只允许消费一次（无论成功或失败）并且可接受丢失，可设置为 true.
    'auto_ack' => true,
];

cls_rabbitmq::do_job($queue_name, $callback, 'rabbitmq', $options);
