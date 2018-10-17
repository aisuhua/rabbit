<?php
include(__DIR__ . '/config.php');

$GLOBALS['config']['rabbitmq'] = [
    'host' => '172.16.0.108',
    'port' => '5672',
    'vhost' => '/',
    'login' => 'guest',
    'password' => 'guest',
];
$exchange_name = 'amq.direct';
$queue_name = 'suhua';

$connection = new AMQPConnection($GLOBALS['rabbitmq']);
$connection->connect();

$channel = new AMQPChannel($connection);
$channel->setPrefetchCount(1);

$queue = new AMQPQueue($channel);
$queue->setName($queue_name);
$queue->setFlags(AMQP_DURABLE);
$queue->declareQueue();

$queue->bind($exchange_name, $queue_name);

while(true)
{
    // auto_ack
    // $message = $queue->get(AMQP_AUTOACK);
    $message = $queue->get();
    if($message === false)
    {
        continue;
    }

    // 业务规则在这里

    echo $message->getBody(), PHP_EOL;
    $queue->ack($message->getDeliveryTag());
}

$connection->disconnect();