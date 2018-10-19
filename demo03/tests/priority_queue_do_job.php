<?php
/**
 * 优先级队列 do_job
 */
require (__DIR__ . '/../init.php');

// 队列名称
$queue_name = 'priority_queue';

/**
 * 处理队列消息的回调函数
 *
 * @param string|array $params 消息内容
 * @param AMQPEnvelope $envelope 可选参数，可以获取有关该消息的详细信息
 */
$callback = function($params, AMQPEnvelope $envelope)
{
    // 打印优先级
    var_dump($envelope->getPriority());

    // 打印消息内容
    var_dump($params);

    sleep(10);
};

// 其他参数
$options = [
    // 优先级队列必须指定一个最大优先级，该数值创建后无法修改
    'x_max_priority' => 10,
];

cls_rabbitmq::do_job($queue_name, $callback, 'rabbitmq', $options);