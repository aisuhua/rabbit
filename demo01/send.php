<?php
require('../vendor/autoload.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// 连接 RabbitMQ
$connection = new AMQPStreamConnection('192.168.31.229', 5672, 'guest', 'guest');
$channel = $connection->channel();

// 创建队列
// https://www.rabbitmq.com/queues.html
// https://cizixs.com/2015/11/23/rabbitmq-concept-and-usage/
// $channel->queue_declare('task_queue', false, true, false, false);

$data = implode(' ', array_slice($argv, 1));
if (empty($data)) {
    $data = "Hello World!";
}

// 消息内容
$msg = new AMQPMessage(
    $data,
    [
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT // 持久化
    ]
);

while(true)
{
    $channel->basic_publish($msg, '', 'task_queue');
}


echo ' [x] Sent ', $data, "\n";

$channel->close();
$connection->close();