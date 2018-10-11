<?php
require(__DIR__ . '/../vendor/autoload.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;

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

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$channel->basic_qos(null, 1, null);

// 从服务器拉取消息： pull 模式
while (true)
{
    $msg = $channel->basic_get('task_queue');

    if(!$msg)
    {
        continue;
    }

    echo ' [x] Received ', $msg->body, "\n";
    sleep(1);
    echo " [x] Done\n";
    $channel->basic_ack($msg->delivery_info['delivery_tag']);
}


$channel->close();
$connection->close();