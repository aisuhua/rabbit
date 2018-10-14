<?php
require('../vendor/autoload.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('192.168.31.229', 5672, 'guest', 'guest');
$channel = $connection->channel();

$data = "Hello World!";
$msg = new AMQPMessage(
    $data,
    [
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT // 持久化
    ]
);

// 自定义 direct 类型的交换器
$channel->exchange_declare('my_exchange', 'direct', false, true, false);
// 将队列绑定到自定义交换器
// 交换器会根据 routing key 将消息路由到所有绑定到 my_exchange 并且 binding key 和 routing key 一致的队列。
$channel->basic_publish($msg, 'my_exchange', 'demo03_queue1');

echo ' [x] Sent ', $data, "\n";

$channel->close();
$connection->close();