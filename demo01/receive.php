<?php
require(__DIR__ . '/../vendor/autoload.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;

define('AMQP_DEBUG', false);

$connection = new AMQPStreamConnection(
    '192.168.31.229',
    5672,
    'guest',
    'guest',
    '/',
    false,
    'AMQPLAIN',
    null,
    'en_US',
    3.0,
    3.0,
    null,
    false,
    0
);
$channel = $connection->channel();

$channel->queue_declare('task_queue', false, true, false, false);

// 当声明一个队列时，它会自动绑定到默认交换机，并以队列名称作为路由键
// ACCESS_REFUSED - operation not permitted on the default exchange
// $channel->queue_bind('task_queue', '', 'task_queue');

echo " [*] Waiting for messages. To exit press CTRL+C\n";

// 消息确认
$callback = function ($msg) {
    echo ' [x] Received ', $msg->body, "\n";
    //sleep(substr_count($msg->body, '.'));
    sleep(1);
    echo " [x] Done\n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);

// 非自动 ack
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();