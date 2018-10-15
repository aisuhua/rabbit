<?php
include(__DIR__ . '/config.php');

$exchange_name = 'router';

// Establish connection to AMQP
$connection = new AMQPConnection($GLOBALS['rabbitmq']);
$connection->connect();

// Create and declare channel
$channel = new AMQPChannel($connection);

// AMQPC Exchange is the publishing mechanism
$exchange = new AMQPExchange($channel);
$exchange->setType(AMQP_EX_TYPE_DIRECT);
$exchange->setName($exchange_name);
$exchange->setFlags(AMQP_DURABLE);
$exchange->declareExchange();

$message = implode(' ', array_slice($argv, 1));
$attributes = [
    'content_type' => 'text/plain',
    'delivery_mode' => 2
];

// Routing key is empty
$exchange->publish($message, '', AMQP_NOPARAM, $attributes);

$connection->disconnect();