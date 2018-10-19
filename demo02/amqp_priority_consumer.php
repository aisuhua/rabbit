<?php
include(__DIR__ . '/config.php');

$exchange_name = 'router';
$queue_name = 'priority-queue';
$consumer_tag = 'consumer';

// Establish connection to AMQP
$connection = new AMQPConnection($GLOBALS['rabbitmq']);
$connection->connect();

// Create and declare channel
$channel = new AMQPChannel($connection);

// Set prefetch count
$channel->setPrefetchCount(1);

// Create queue and set the priority
$queue = new AMQPQueue($channel);
$queue->setName($queue_name);
$queue->setFlags(AMQP_DURABLE);
$queue->setArgument('x-max-priority', 10);
$queue->declareQueue();

// binding key is empty
$queue->bind($exchange_name, $queue_name);

echo ' [*] Waiting for logs. To exit press CTRL+C', PHP_EOL;

$process_message = function(AMQPEnvelope $message, AMQPQueue $queue) use (&$max_jobs) {
    echo "\n--------\n";
    echo $message->getBody();
    echo "\n--------\n";

    echo 'Priority ', $message->getPriority(), PHP_EOL;
    sleep(10);

    $queue->ack($message->getDeliveryTag());

    if ($message->getBody() === 'quit') {
        $queue->cancel($queue->getConsumerTag());
    }
};

$queue->consume($process_message, AMQP_NOPARAM, $consumer_tag);
$connection->disconnect();

