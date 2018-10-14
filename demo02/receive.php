<?php
require(__DIR__ . '/../vendor/autoload.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('192.168.31.229', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('suhua', false, true, false, false);

// 当声明一个队列时，它会自动绑定到默认交换机，并以队列名称作为路由键
// ACCESS_REFUSED - operation not permitted on the default exchange
// $channel->queue_bind('task_queue', '', 'task_queue');

$callback = function ($msg) {
    echo ' [x] Received ', $msg->body, "\n";
    sleep(10);
    echo " [x] Done\n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('suhua', '', false, false, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();