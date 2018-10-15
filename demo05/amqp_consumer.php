<?php
include(__DIR__ . '/config.php');

$exchangeName = 'router';
$queueName = 'msgs';
$consumerTag = 'consumer';

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

echo ' [*] Waiting for logs. To exit press CTRL+C', PHP_EOL;

$process_message = function(AMQPEnvelope $message, AMQPQueue $queue) use (&$max_jobs) {
    echo "\n--------\n";
    echo $message->getBody();
    echo "\n--------\n";

    $queue->ack($message->getDeliveryTag());

    if ($message->getBody() === 'quit') {
        $queue->cancel($queue->getConsumerTag());
    }
};

$queue->consume($process_message, AMQP_NOPARAM, $consumerTag);
$connection->disconnect();