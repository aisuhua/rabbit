<?php
include(__DIR__ . '/config.php');

$exchange_name = 'router';
$queue_name = 'suhua';
$routing_key = 'suhua';

$connection = new AMQPConnection($GLOBALS['rabbitmq']);
$connection->connect();

$channel = new AMQPChannel($connection);

$queue = new AMQPQueue($channel);
$queue->setName($queue_name);
$queue->setFlags(AMQP_DURABLE);
$queue->declareQueue();

$queue->bind($exchange_name, $routing_key);

while(true)
{
    $message = $queue->get();
    if($message === false)
    {
        continue;
    }

    $queue->ack($message->getDeliveryTag());
}

$connection->disconnect();