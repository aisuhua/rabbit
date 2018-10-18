<?php

class cls_rabbitmq
{
    public static function do_job($queue_name, $callback, $host = 'rabbitmq', $options = array())
    {
        if(!isset($GLOBALS['config'][$host]))
        {
            throw new InvalidArgumentException("The host {$host} is not exists.");
        }

        $AMQPCAdapter = AMQPCAdapter::getInstance($GLOBALS['config'][$host]);
        $AMQPCAdapter->applyOptions($options);
        $AMQPCAdapter->consume($queue_name, $callback);
    }

    public static function add_job($queue_name, $payload, $host='rabbitmq', $options = array())
    {
        if(!isset($GLOBALS['config'][$host]))
        {
            throw new InvalidArgumentException("The host {$host} is not exists.");
        }

        $AMQPCAdapter = AMQPCAdapter::getInstance($GLOBALS['config'][$host]);
        $AMQPCAdapter->applyOptions($options);
        return $AMQPCAdapter->publish($queue_name, $payload);
    }
}

class AMQPCAdapter
{
    private $exchange_name = 'amq.direct';
    private $queue_prefix = '';
    private $prefetch_count = 1;
    
    /** @var AMQPConnection */
    private $connection;

    /** @var AMQPChannel */
    private $channel;

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
            return self::$instance;
        }

        self::$instance[$key] = new self($hosts);
        return self::$instance[$key];
    }

    /**
     * 应用自定义配置选项
     *
     * @param array $options
     */
    public function applyOptions(array $options)
    {
        if(isset($options['exchange_name']))
        {
            $this->exchange_name = $options['exchange_name'];
        }

        if(isset($options['queue_prefix']))
        {
            $this->queue_prefix = $options['queue_prefix'];
        }

        if(isset($options['prefetch_count']))
        {
            $this->prefetch_count = (int) $options['prefetch_count'];
        }
    }

    /**
     * 开始消费队列
     *
     * @param $queue_name
     * @param $callback
     */
    public function consume($queue_name, $callback)
    {
        // 创建连接
        $this->connect();

        $queue = new AMQPQueue($this->channel);
        $queue->setName($this->queue_prefix . $queue_name);
        $queue->setFlags(AMQP_DURABLE);
        $queue->declareQueue();

        $queue->bind($this->exchange_name, $queue_name);

        while(true)
        {
            $message = $queue->get();
            if($message === false)
            {
                continue;
            }

            // 业务规则在这里
            $result = $callback($message->getBody());

            // 拒绝确认并将消息重新放回队列（消息还是在队列的开头位置）
            if($result === 'reject')
            {
                $queue->reject($message->getDeliveryTag(), AMQP_REQUEUE);
            }
            else
            {
                $queue->ack($message->getDeliveryTag());
            }
        }
    }

    /**
     * 将消息发布到队列
     *
     * @param $queue_name
     * @param $payload
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

        $message = $payload;
        $attributes = [
            'content_type' => 'text/plain',
            'delivery_mode' => 2
        ];
        return $exchange->publish($message, $queue_name, AMQP_NOPARAM, $attributes);
    }

    /**
     * 与 RabbitMQ 建立连接
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

        $len = count($this->hosts);
        foreach ($this->hosts as $i => $host)
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
                trigger_error($e->getMessage(), E_USER_ERROR);

                // 没有一个能连接上则抛出异常
                if ($i == $len - 1)
                {
                    throw $e;
                }
            }
        }
    }

    /**
     * 声明 channel
     */
    private function declareChannel()
    {
        $this->channel = new AMQPChannel($this->connection);
        $this->channel->setPrefetchCount($this->prefetch_count);
    }
}


