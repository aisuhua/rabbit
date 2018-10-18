<?php
require ('config.php');
require('cls_rabbitmq.php');
require ('functions.php');

$data = [];

$queue_name = 'suhua';
$callback = function($params) use (&$data) {
    echo $params, PHP_EOL;
    $start_time = microtime(true);

    $data[] = file_get_contents('/www/web/v.demo.114la.com.tar.gz');

    $i = 0;
    while ($i++ < 5)
    {
        echo $i, PHP_EOL;
        sleep(1);
    }

    $cost_time = microtime(true) - $start_time;
    echo 'done, time ', $cost_time, PHP_EOL;
    return true;
};

// 自定义其他配置
$options = [
    'exchange_name' => 'router',
    'queue_prefix' => 'aisuhua.',
    // 'max_ttl' => 10,
    // 'max_mem' => 60 * 1024 * 1024,
    // 'auto_rerun' => true,
    // 'auto_ack' => true,
];

cls_rabbitmq::do_job($queue_name, $callback, 'rabbitmq', $options);