<?php
/**
 * 获取消息总数
 */
require (__DIR__ . '/../../demo09/init.php');

$queue_name = 'my_queue';
$message_count = cls_rabbitmq::get_message_count($queue_name, 'rabbitmq');

var_dump($message_count);