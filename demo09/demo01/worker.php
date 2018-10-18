<?php
require (__DIR__ . '/../init.php');

// 队列名称
$queue_name = 'my_queue';

/**
 * 处理消息的回调函数
 *
 * @param string|array $params 消息内容
 * @param AMQPEnvelope $envelope 可选参数，可以获取有关该消息的详细信息
 */
$callback = function($params, AMQPEnvelope $envelope)
{
    // 打印消息内容
    var_dump($params);

    // 打印 RAW 消息的内容类型
    var_dump($envelope->getContentType());
};

// 开始消费队列
cls_rabbitmq::consume($queue_name, $callback, 'rabbitmq');


/*
shell> php worker.php
/www/web/rabbit/demo09/demo01/worker.php:11:
string(12) "Hello World!"
/www/web/rabbit/demo09/demo01/worker.php:12:
string(10) "text/plain"
2018-10-18 21:24:04>> memory 2MB, uptime 0:0:24.
/www/web/rabbit/demo09/demo01/worker.php:11:
array(2) {
  [0] =>
  string(5) "Hello"
  [1] =>
  string(6) "World!"
}
/www/web/rabbit/demo09/demo01/worker.php:12:
string(16) "application/json"
2018-10-18 21:24:04>> memory 2MB, uptime 0:0:24.
 */