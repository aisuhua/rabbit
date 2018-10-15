<?php
include(__DIR__ . '/config.php');

$exchangeName = 'basic_get_exchange';
$queueName = 'basic_get_queue';

// Establish connection to AMQP
$connection = new AMQPConnection($GLOBALS['rabbitmq']);
$connection->connect();

// Create and declare channel
$channel = new AMQPChannel($connection);

// AMQPC Exchange is the publishing mechanism
$exchange = new AMQPExchange($channel);
$exchange->setType(AMQP_EX_TYPE_DIRECT);
$exchange->setName($exchangeName);
$exchange->setFlags(AMQP_DURABLE);
$exchange->declareExchange();

// Create queue
$queue = new AMQPQueue($channel);
$queue->setName($queueName);
$queue->setFlags(AMQP_DURABLE);
$queue->declareQueue();

// binding key is empty
$queue->bind($exchangeName);

$message = implode(' ', array_slice($argv, 1));
$attributes = [
    'content_type' => 'text/plain',
    'delivery_mode' => 2
];
// Routing key is empty
$exchange->publish($message, '', AMQP_NOPARAM, $attributes);

$message = $queue->get();
var_dump($message->getBody());

$queue->ack($message->getDeliveryTag());

$connection->disconnect();