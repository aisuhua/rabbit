<?php
/**
 * auto_rerun
 */
require (__DIR__ . '/../init.php');

// 队列名称
$queue_name = 'my_queue';

/**
 * 处理消息的回调函数
 *
 * @param string|array $params 消息内容
 */
$callback = function($params)
{
    var_dump($params);
};

// 其他参数
$options = [
    'auto_rerun' => true, // 开启重新运行，默认为 true。
    'max_ttl' => 5, // 工作超过 5 秒后会重新运行，仅当 auto_return = true 时有效。
    'max_mem' => 20 * 1024 * 1024, // 内存占用超过 20MB 后会重新运行，仅当 auto_return = true 时有效。
];

cls_rabbitmq::do_job($queue_name, $callback, 'rabbitmq', $options);
