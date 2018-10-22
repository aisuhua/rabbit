<?php

include(__DIR__ . '/config.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
$channel = $connection->channel();

// declare  exchange but don`t bind any queue
$channel->exchange_declare('hidden_exchange', 'topic');

$message = new AMQPMessage("Hello World!");

// 若该消息没有路由给任何一个队列，则会丢弃
echo " [x] Sent non-mandatory ...";
$channel->basic_publish(
    $message,
    'hidden_exchange',
    'rkey'
);
echo " done.\n";

$wait = true;

$returnListener = function (
    $replyCode,
    $replyText,
    $exchange,
    $routingKey,
    $message
) use ($wait) {
    $GLOBALS['wait'] = false;

    echo "return: ",
    $replyCode, "\n",
    $replyText, "\n",
    $exchange, "\n",
    $routingKey, "\n",
    $message->body, "\n";
};

$channel->set_return_listener($returnListener);

// 若该消息没有路由给任何一个队列，则会返回给生产者
echo " [x] Sent mandatory ... ";
$channel->basic_publish(
    $message,
    'hidden_exchange',
    'rkey',
    true
);
echo " done.\n";

while ($wait) {
    $channel->wait();
}

$channel->close();
$connection->close();