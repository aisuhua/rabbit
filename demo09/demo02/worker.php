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

// 消费配置选项，可选
$options = [
    'x_max_priority' => 10, // 队列最大优先级，创建后该数值无法修改
];

cls_rabbitmq::consume($queue_name, $callback, 'rabbitmq', $options);


/*
shell> php worker.php
int(1)
string(1) "1"
2018-10-18 21:45:09>> memory 2MB, uptime 0:0:15.
int(2)
string(1) "4"
2018-10-18 21:45:19>> memory 2MB, uptime 0:0:25.
int(3)
string(1) "6"
2018-10-18 21:45:29>> memory 2MB, uptime 0:0:35.
int(2)
string(1) "5"
2018-10-18 21:45:39>> memory 2MB, uptime 0:0:45.
int(1)
string(1) "2"
2018-10-18 21:45:49>> memory 2MB, uptime 0:0:55.
int(1)
string(1) "3"
2018-10-18 21:45:59>> memory 2MB, uptime 0:1:5.
 */