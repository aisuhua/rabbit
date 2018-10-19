<?php
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

// 消费配置选项
$options = [
    // 队列最大优先级，创建后该数值无法修改
    // 每次添加任务时都需要提供该值
    'x_max_priority' => 10,
];

cls_rabbitmq::do_job($queue_name, $callback, 'rabbitmq', $options);