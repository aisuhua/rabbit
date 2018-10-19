<?php
include(__DIR__ . '/config.php');

$queue_name = 'my_queue';

$connection = new AMQPConnection($GLOBALS['rabbitmq']);
$connection->connect();

$channel = new AMQPChannel($connection);
$channel->setPrefetchCount(1);

// Message count
$queue = new AMQPQueue($channel);
$queue->setName($queue_name);
$queue->setFlags(AMQP_DURABLE);
$message_count = $queue->declareQueue();

var_dump($message_count);

// Purge queue
$queue = new AMQPQueue($channel);
$queue->setName($queue_name);
$queue->setFlags(AMQP_DURABLE);
$purged = $queue->purge();

var_dump($purged);

// Delete queue
$queue = new AMQPQueue($channel);
$queue->setName($queue_name);
$queue->setFlags(AMQP_DURABLE);
$deleted = $queue->delete();

var_dump($deleted);


