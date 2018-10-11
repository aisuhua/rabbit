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

$channel->exchange_declare('my_exchange', 'direct', false, true, false);
$channel->basic_publish($msg, 'my_exchange', 'demo03_queue1');

echo ' [x] Sent ', $data, "\n";

$channel->close();
$connection->close();