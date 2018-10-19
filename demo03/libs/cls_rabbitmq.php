<?php
/**
 * 业务层使用入口
 */
class cls_rabbitmq
{
    /**
     * 消费队列
     *
     * @param string $queue_name
     * @param $callback
     * @param string $host
     * @param array $options
     * @return void
     */
    public static function do_job($queue_name, $callback, $host = 'rabbitmq', array $options = [])
    {
        $AMQPCAdapter = self::getAMQPCAdapter($host);
        $AMQPCAdapter->applyConsumeOptions($options);
        $AMQPCAdapter->consume($queue_name, $callback);
    }

    /**
     * 添加队列任务
     *
     * @param string $queue_name
     * @param string|array $payload
     * @param string $host
     * @param array $options
     * @return bool 添加成功返回 true，否则返回 false
     */
    public static function add_job($queue_name, $payload, $host='rabbitmq', array $options = [])
    {
        $AMQPCAdapter = self::getAMQPCAdapter($host);
        $AMQPCAdapter->applyPublishOptions($options);
        return $AMQPCAdapter->publish($queue_name, $payload);
    }

    /**
     * 获取队列的消息总数
     *
     * @param $queue_name
     * @param string $host
     * @param array $options
     * @return int
     */
    public static function get_message_count($queue_name, $host='rabbitmq', array $options = [])
    {
        $AMQPCAdapter = self::getAMQPCAdapter($host);
        $AMQPCAdapter->applyConsumeOptions($options);
        return $AMQPCAdapter->getMessageCount($queue_name);
    }

    /**
     * 清空队列的所有消息
     *
     * @param $queue_name
     * @param string $host
     * @param array $options
     * @return bool
     */
    public static function purge($queue_name, $host='rabbitmq', array $options = [])
    {
        $AMQPCAdapter = self::getAMQPCAdapter($host);
        $AMQPCAdapter->applyConsumeOptions($options);
        return $AMQPCAdapter->purge($queue_name);
    }

    /**
     * 删除该队列
     *
     * @param $queue_name
     * @param string $host
     * @param array $options
     * @return int 返回被删除队列的消息数
     */
    public static function delete($queue_name, $host='rabbitmq', array $options = [])
    {
        $AMQPCAdapter = self::getAMQPCAdapter($host);
        $AMQPCAdapter->applyConsumeOptions($options);
        return $AMQPCAdapter->delete($queue_name);
    }

    /**
     * 获取 AMQPCAdapter 实例对象
     *
     * @param $host
     * @return AMQPCAdapter
     */
    private function getAMQPCAdapter($host)
    {
        if(!isset($GLOBALS['config'][$host]))
        {
            throw new InvalidArgumentException("The host {$host} is not exists.");
        }

        return AMQPCAdapter::getInstance($GLOBALS['config'][$host]);
    }
}

/**
 * 对 RabbitMQ 客户端连接库 php-amqp 进行封装
 *
 * @link https://github.com/pdezwart/php-amqp
 */
class AMQPCAdapter
{
    private $exchange_name = 'amq.direct';
    private $queue_prefix = '';
    private $prefetch_count = 1;
    private $auto_rerun = true;
    private $auto_ack = false;
    private $start_time;
    private $max_ttl = 300; // 5minutes
    private $max_mem = 52428800; // 50MB
    private $x_max_priority = 0;
    private $priority = 0;
    private $disable_signal_handle = false;

    /** @var AMQPConnection */
    private $connection;

    /** @var AMQPChannel */
    private $channel;

    /** @var AMQPQueue */
    private $queue;

    /** @var array */
    private $hosts;

    /** @var AMQPCAdapter */
    private static $instance;

    /**
     * 私有构造
     *
     * AMQPCAdapter constructor.
     * @param $hosts
     */
    private function __construct($hosts)
    {
        $this->hosts = $hosts;
    }

    /**
     * 获取当前实例
     *
     * @param array $hosts
     * @return AMQPCAdapter
     */
    public static function getInstance(array $hosts)
    {
        $key = json_encode($hosts);

        if(isset(self::$instance[$key]))
        {
            return self::$instance[$key];
        }

        self::$instance[$key] = new self($hosts);
        return self::$instance[$key];
    }

    /**
     * 应用消费配置选项
     *
     * @param $options
     */
    public function applyConsumeOptions($options)
    {
        $this->applyOptions($options);

        if(isset($options['prefetch_count']))
        {
            $this->prefetch_count = (int) $options['prefetch_count'];
        }

        if(isset($options['auto_ack']))
        {
            $this->auto_ack = (bool) $options['auto_ack'];
        }

        if(isset($options['auto_rerun']))
        {
            $this->auto_rerun = (bool) $options['auto_rerun'];
        }

        if(isset($options['max_ttl']))
        {
            $this->max_ttl = (int) $options['max_ttl'];
        }

        if(isset($options['max_mem']))
        {
            $this->max_mem = (int) $options['max_mem'];
        }

        // 定义队列最大的优先级
        if(isset($options['x_max_priority']))
        {
            $this->x_max_priority = min(255, (int) $options['x_max_priority']);
        }

        if(isset($options['disable_signal_handle']))
        {
            $this->disable_signal_handle = (bool) $options['disable_signal_handle'];
        }
    }

    /**
     * 应用发布配置选项
     *
     * @param array $options
     */
    public function applyPublishOptions(array $options)
    {
        $this->applyOptions($options);

        // 消息优先级
        if(isset($options['priority']))
        {
            $this->priority = min(255, (int) $options['priority']);
        }
    }

    /**
     * 应用配置选项
     *
     * @param array $options
     */
    private function applyOptions(array $options)
    {
        if(isset($options['exchange_name']))
        {
            $this->exchange_name = $options['exchange_name'];
        }

        if(isset($options['queue_prefix']))
        {
            $this->queue_prefix = $options['queue_prefix'];
        }
    }

    /**
     * 发布消息到队列
     *
     * @param $queue_name
     * @param string|array $payload
     * @return bool
     */
    public function publish($queue_name, $payload)
    {
        // 创建连接
        $this->connect();

        $exchange = new AMQPExchange($this->channel);
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $exchange->setName($this->exchange_name);
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declareExchange();

        // 使用 content_type 区分普通字符串还是 JSON
        if (is_array($payload))
        {
            $content_type = 'application/json';
            $message = json_encode($payload);
        }
        else
        {
            $content_type = 'text/plain';
            $message = $payload;
        }

        $attributes = [
            'content_type' => $content_type,
            'delivery_mode' => 2 // 2 表示消息持久化
        ];

        // 消息优先级
        if($this->priority)
        {
            $attributes['priority'] = $this->priority;
        }

        return $exchange->publish($message, $queue_name, AMQP_NOPARAM, $attributes);
    }

    /**
     * 消费队列
     *
     * @param $queue_name
     * @param $callback
     */
    public function consume($queue_name, $callback)
    {
        // 消费开始时间
        $this->start_time = time();

        // 消费前检测
        $this->beforeConsume();

        // 创建连接
        $this->connect();

        // 创建队列
        $this->declareQueue($queue_name);

        // 将队列绑定到交换器，使用队列名作为路由键
        $this->queue->bind($this->exchange_name, $queue_name);
        $queue = $this->queue;

        while(true)
        {
            $this->beforeConsumeMessage();

            $flags = $this->auto_ack == false ? AMQP_NOPARAM : AMQP_AUTOACK;
            $envelope = $queue->get($flags);

            if($envelope === false)
            {
                // 没有消息时，休息 1 秒，减少对服务器的请求
                sleep(1);
                continue;
            }

            // 普通文本和 JSON 分开处理
            if ($envelope->getContentType() == 'application/json')
            {
                $params = json_decode($envelope->getBody(), true);
            }
            else
            {
                $params = $envelope->getBody();
            }

            // 开始处理业务逻辑
            $result = $callback($params, $envelope);

            // 若非自动 ack，则需要手工 ack
            if($this->auto_ack == false)
            {
                // 拒绝确认并将消息重新放回队列（消息还是在队列的开头位置）
                if($result === 'reject')
                {
                    $queue->reject($envelope->getDeliveryTag(), AMQP_REQUEUE);
                }
                else
                {
                    $queue->ack($envelope->getDeliveryTag());
                }
            }

            $this->afterConsumeMessage();
        }
    }

    /**
     * 获取队列的消息总数
     *
     * @param $queue_name
     * @return int
     */
    public function getMessageCount($queue_name)
    {
        $this->connect();
        return $this->declareQueue($queue_name);
    }

    /**
     * 清空队列的所有消息
     *
     * @param $queue_name
     * @return bool
     */
    public function purge($queue_name)
    {
        $this->connect();
        $this->declareQueue($queue_name);

        return $this->queue->purge();
    }

    /**
     * 删除该队列
     *
     * @param $queue_name
     * @return bool
     */
    public function delete($queue_name)
    {
        $this->connect();
        $this->declareQueue($queue_name);

        return $this->queue->delete();
    }

    /**
     * 声明队列
     *
     * @param $queue_name
     * @return int 返回队列的消息数
     */
    private function declareQueue($queue_name)
    {
        $this->queue = new AMQPQueue($this->channel);
        $this->queue->setName($this->queue_prefix . $queue_name);
        $this->queue->setFlags(AMQP_DURABLE);

        // 设置队列支持的最大优先级
        if($this->x_max_priority)
        {
            $this->queue->setArgument('x-max-priority', $this->x_max_priority);
        }

        return $this->queue->declareQueue();
    }

    /**
     * beforeConsume
     */
    private function beforeConsume()
    {
        // 注册信号量处理程序
        if(!$this->disable_signal_handle)
        {
            // 处理信号量
            $signals = [
                SIGTERM => 'SIGTERM',
                SIGHUP  => 'SIGHUP',
                SIGINT  => 'SIGINT',
                SIGQUIT => 'SIGQUIT',
            ];

            $sig_handler = function ($signo) use ($signals)
            {
                echo sprintf(
                    '%s>> %s: %d, signal handler called, PID-%d exit peacefully.' . PHP_EOL,
                    date('Y-m-d H:i:s'),
                    isset($signals[$signo]) ? $signals[$signo] : 'Unknown',
                    $signo,
                    posix_getpid()
                );

                // 关闭连接
                $this->disconnect();
                exit();
            };

            pcntl_signal(SIGTERM, $sig_handler); // kill
            pcntl_signal(SIGHUP, $sig_handler); // kill -s HUP or kill -1
            pcntl_signal(SIGINT, $sig_handler); // Ctrl-C
            pcntl_signal(SIGQUIT, $sig_handler); // kill -3
        }

        // 收集需要监控变化的文件列表
        if ($this->auto_rerun)
        {
            $this->handleChangedFiles(0);
        }

        // 检查是否有信号量需要处理
        if(!$this->disable_signal_handle)
        {
            pcntl_signal_dispatch();
        }
    }

    /**
     * 检查是否有文件发生变化
     *
     * @param int $period
     */
    private function handleChangedFiles($period = 1)
    {
        $changed_files = get_changed_files($period);
        if ($changed_files)
        {
            foreach ($changed_files as $file_path => $file_info)
            {
                echo sprintf(
                    '%s>> %s was modified at %s(%s), PID-%d rerun automatically.' . PHP_EOL,
                    date('Y-m-d H:i:s'),
                    basename($file_path),
                    date('Y-m-d H:i:s', $file_info['time']),
                    $file_info['size'],
                    posix_getpid()
                );
            }

            rerun_process();
        }
    }

    /**
     * beforeConsumeMessage
     */
    private function beforeConsumeMessage()
    {
        // 检查是否有信号量需要处理
        if(!$this->disable_signal_handle)
        {
            pcntl_signal_dispatch();
        }

        if($this->auto_rerun)
        {
            // 检查是否有文件发生变化
            $this->handleChangedFiles(1);

            // 检查工作时间是否超过限制
            if (time() - $this->start_time >= $this->max_ttl)
            {
                echo sprintf(
                    '%s>> Process has been running for %ds, PID-%d rerun automatically.' . PHP_EOL,
                    date('Y-m-d H:i:s'),
                    time() - $this->start_time,
                    posix_getpid()
                );
                rerun_process();
            }

            // 检查内存占用是否超过限制
            if(memory_get_usage(true) > $this->max_mem)
            {
                echo sprintf(
                    '%s>> Process out of Memory %s, PID-%d rerun automatically.' . PHP_EOL,
                    date('Y-m-d H:i:s'),
                    format_size($this->max_mem),
                    posix_getpid()
                );
                rerun_process();
            }
        }
    }

    /**
     * afterConsumeMessage
     */
    private function afterConsumeMessage()
    {
        // 输出每次循环后的状态信息
        echo sprintf(
            '%s>> memory usage %s, uptime %s' . PHP_EOL,
            date('Y-m-d H:i:s'),
            format_size(memory_get_usage(true)),
            format_seconds(time() - $this->start_time)
        );
    }

    /**
     * 建立连接
     *
     * @return bool
     * @throws AMQPException
     */
    private function connect()
    {
        if ($this->connection &&
            $this->connection->isConnected() &&
            $this->channel &&
            $this->channel->isConnected())
        {
            return true;
        }

        // 有连接但没有 channel
        if ($this->connection &&
            $this->connection->isConnected() &&
            (!$this->channel || !$this->channel->isConnected()))
        {
            $this->declareChannel();
            return true;
        }

        // 尝试重新连接
        if ($this->connection)
        {
            if ($this->connection->reconnect() &&
                $this->connection->isConnected())
            {
                $this->declareChannel();
                return true;
            }
        }

        // 随机连接任意一台服务器
        $hosts = $this->hosts;
        shuffle($hosts);
        $len = count($hosts);

        foreach ($hosts as $i => $host)
        {
            try
            {
                $this->connection = new AMQPConnection($host);
                $this->connection->connect();

                $this->declareChannel();
                return true;
            }
            catch (\AMQPException $e)
            {
                trigger_error($e->getMessage(), E_USER_WARNING);

                // 没有一个能连接上则抛出异常
                if ($i == $len - 1)
                {
                    throw $e;
                }
            }
        }

        // 实际上执行不到这里，仅仅为了屏蔽编辑器的警告
        return false;
    }

    /**
     * 关闭连接
     */
    private function disconnect()
    {
        $this->connection->disconnect();
    }

    /**
     * 创建 channel
     */
    private function declareChannel()
    {
        $this->channel = new AMQPChannel($this->connection);
        $this->channel->setPrefetchCount($this->prefetch_count);
    }
}