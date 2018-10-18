<?php
require (__DIR__ . '/../init.php');

// 队列名称
$queue_name = 'options_queue';

// 回调函数
$callback = function($params)
{
    var_dump($params);
    sleep(3);
};

// 发布配置选项，可选
$options = [
    'exchange_name' => '115.web', // 使用的交换器
    'queue_prefix' => '115.web.', // 设置队列前缀
    'auto_rerun' => true, // 是否开启重新运行，默认为 true
    'max_ttl' => 10, // worker 工作时长超过该值会重新运行，仅当 auto_return = true 时有效。
    'max_mem' => 20 * 1024 * 1024, // worker 占用内存超过该值会重新运行，仅当 auto_return = true 时有效
    'auto_ack' => false, // 若需要使用 auto_ack 时可以设置为 true
    'prefetch_count' => 1, // 预提取的消息数，默认为 1，较适合目前网盘大部分场景
    'disable_signal_handle' => false, // 是否禁用信号量处理，在 shell 中进行调试时，设置该值为 true，按 Ctrl+C 能够立即退出进程。
];

cls_rabbitmq::do_job($queue_name, $callback, 'rabbitmq', $options);