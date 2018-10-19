<?php
/**
 * 清空队列的所有消息
 */

require (__DIR__ . '/../init.php');

$queue_name = 'my_queue';
$purged = cls_rabbitmq::purge($queue_name, 'rabbitmq');
var_dump($purged);