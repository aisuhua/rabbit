<?php

include(__DIR__ . '/config.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

$exchange = 'router';
$queue = 'priority-queue';

$connection = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
$channel = $connection->channel();

/*
    The following code is the same both in the consumer and the producer.
    In this way we are sure we always have a queue to consume from and an
        exchange where to publish messages.
*/

/*
    name: $queue
    passive: false
    durable: true // the queue will survive server restarts
    exclusive: false // the queue can be accessed in other channels
    auto_delete: false //the queue won't be deleted once the channel is closed.
*/
$args = new AMQPTable(array(
    "x-max-priority" => 10
));
$channel->queue_declare($queue, false, true, false, false, false, $args);

/*
    name: $exchange
    type: direct
    passive: false
    durable: true // the exchange will survive server restarts
    auto_delete: false //the exchange won't be deleted once the channel is closed.
*/

$channel->exchange_declare($exchange, 'direct', false, true, false);

$channel->queue_bind($queue, $exchange);

$messageBody = implode(' ', array_slice($argv, 1));
$message = new AMQPMessage(
    $messageBody,
    [
        'content_type' => 'text/plain',
        'priority' => 8,
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
    ]
);
$channel->basic_publish($message, $exchange);

$channel->close();
$connection->close();