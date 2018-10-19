<?php
/**
 * 删除队列
 */
require (__DIR__ . '/../../demo09/init.php');

$queue_name = 'my_queue';
$deleted_message_count = cls_rabbitmq::delete($queue_name, 'rabbitmq');

var_dump($deleted_message_count);