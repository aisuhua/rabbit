<?php
require(__DIR__ . '/../vendor/autoload.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('192.168.31.229', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('demo03', false, true, false, false);
$channel->queue_bind('demo03', 'amq.direct', 'demo03');

$callback = function ($msg) {
    echo ' [x] Received ', $msg->body, "\n";
    sleep(1);
    echo " [x] Done\n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('demo03', '', false, false, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();