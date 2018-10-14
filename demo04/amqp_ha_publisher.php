<?php

include(__DIR__ . '/config.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$exchange = 'router';
$queue = 'normal_queue';
$ha_two_queue = 'two.queue';
$ha_all_queue = 'ha.queue';
$ha_nodes_queue = 'nodes.queue';

$connection = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
$channel = $connection->channel();

$channel->exchange_declare($exchange, 'direct', false, true, false);

$channel->queue_declare($queue, false, false, false, true);
$channel->queue_declare($ha_two_queue, false, false, false, true);
$channel->queue_declare($ha_all_queue, false, false, false, true);
$channel->queue_declare($ha_nodes_queue, false, false, false, true);

$channel->queue_bind($queue, $exchange);
$channel->queue_bind($ha_two_queue, $exchange);
$channel->queue_bind($ha_all_queue, $exchange);
$channel->queue_bind($ha_nodes_queue, $exchange);

$messageBody = implode(' ', array_slice($argv, 1));
$message = new AMQPMessage($messageBody, array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
$channel->basic_publish($message, $exchange);

$channel->close();
$connection->close();