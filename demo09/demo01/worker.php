<?php
require (__DIR__ . '/../init.php');

// 队列名称
$queue_name = 'my_queue';

/**
 * 处理消息的回调函数
 *
 * @param string|array $params 消息内容
 * @param AMQPEnvelope $envelope 可选参数，可以获取有关该消息的详细信息
 */
$callback = function($params, AMQPEnvelope $envelope)
{
    // 打印消息内容
    var_dump($params);

    // 打印 RAW MESSAGE 的内容类型
    var_dump($envelope->getContentType());
};

// 开始消费队列
cls_rabbitmq::do_job($queue_name, $callback, 'rabbitmq');