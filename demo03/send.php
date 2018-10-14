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

// 使用默认 direct 交换器
$channel->basic_publish($msg, 'amq.direct', 'demo03');

echo ' [x] Sent ', $data, "\n";

$channel->close();
$connection->close();