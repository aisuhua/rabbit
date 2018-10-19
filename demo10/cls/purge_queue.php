<?php
/**
 * 清空所有消息
 */
require (__DIR__ . '/../../demo09/init.php');

$queue_name = 'my_queue';
$purged = cls_rabbitmq::purge($queue_name, 'rabbitmq');

var_dump($purged);