<?php
include(__DIR__ . '/config.php');

$exchange_name = 'router';
$normal_queue_name = 'normal_queue';
$ha_two_queue_name = 'two.queue';
$ha_queue_name = 'ha.queue';
$ha_nodes_queue_name = 'nodes.queue';
$consumer_tag = 'consumer';

// Establish connection to AMQP
$connection = new AMQPConnection($GLOBALS['rabbitmq']);
$connection->connect();

// Create and declare channel
$channel = new AMQPChannel($connection);

// Set prefetch count
$channel->setPrefetchCount(1);

// AMQPC Exchange is the publishing mechanism
$exchange = new AMQPExchange($channel);
$exchange->setType(AMQP_EX_TYPE_DIRECT);
$exchange->setName($exchange_name);
$exchange->setFlags(AMQP_DURABLE);
$exchange->declareExchange();

// Create queue
$queue = new AMQPQueue($channel);
$queue->setName($normal_queue_name);
$queue->setFlags(AMQP_DURABLE);
$queue->declareQueue();

$ha_two_queue = new AMQPQueue($channel);
$ha_two_queue->setName($ha_two_queue_name);
$ha_two_queue->setFlags(AMQP_DURABLE);
$ha_two_queue->declareQueue();

$ha_queue = new AMQPQueue($channel);
$ha_queue->setName($ha_queue_name);
$ha_queue->setFlags(AMQP_DURABLE);
$ha_queue->declareQueue();

$ha_nodes_queue = new AMQPQueue($channel);
$ha_nodes_queue->setName($ha_nodes_queue_name);
$ha_nodes_queue->setFlags(AMQP_DURABLE);
$ha_nodes_queue->declareQueue();

// binding key is empty
$queue->bind($exchange_name);
$ha_two_queue->bind($exchange_name);
$ha_queue->bind($exchange_name);
$ha_nodes_queue->bind($exchange_name);

echo ' [*] Waiting for logs. To exit press CTRL+C', PHP_EOL;

$process_message = function(AMQPEnvelope $message, AMQPQueue $queue) use (&$max_jobs) {
    echo "\n--------\n";
    echo $message->getBody();
    echo "\n--------\n";

    sleep(5);

    $queue->ack($message->getDeliveryTag());

    if ($message->getBody() === 'quit') {
        $queue->cancel($queue->getConsumerTag());
    }
};

$queue->consume($process_message, AMQP_NOPARAM, $consumer_tag);
$connection->disconnect();