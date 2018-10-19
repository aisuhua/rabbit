<?php
/**
 * disable_signal_handle
 */
require (__DIR__ . '/../../demo09/init.php');

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

    $i = 0;
    while ($i++ < 10)
    {
        echo $i, PHP_EOL;
        sleep(1);
    }
};

// 其他参数
$options = [
    // 禁用信号量处理，按 Ctrl+C 能立即中断当前进程
    // 不建议在生产环境上设置该值为 true，这里仅仅为了演示其用法
    'disable_signal_handle' => true,
];

cls_rabbitmq::do_job($queue_name, $callback, 'rabbitmq', $options);
