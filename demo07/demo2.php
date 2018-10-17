<?php

class cls_rabbitmq
{
    protected $exchange_name = 'amq.direct';
    protected $queue_prefix = '';

    /** @var AMQPConnection */
    protected $connection;

    /** @var AMQPChannel */
    protected $channel;
    protected static $instance;
    protected $hosts;

    public function __construct($hosts)
    {
        $this->hosts = $hosts;
    }

    /**
     * @param $hosts
     * @return cls_rabbitmq
     */
    public static function get_instance($hosts)
    {
        $key = json_encode($hosts);

        if(isset(self::$instance[$key]))
        {
            return self::$instance;
        }

        self::$instance[$key] = new self($hosts);
        return self::$instance[$key];
    }

    public static function do_job($queue_name, $callback, $host = 'rabbitmq', $options = array())
    {
        if(!isset($GLOBALS['config'][$host]))
        {
            throw new InvalidArgumentException("The host {$host} is not exists.");
        }

        $that = self::get_instance($GLOBALS['config'][$host]);
        $that->connect();

        if(isset($options['exchange_name']))
        {
            $that->exchange_name = $options['exchange_name'];
        }

        if(isset($options['queue_prefix']))
        {
            $that->queue_prefix = $options['queue_prefix'];
        }

        $queue = new AMQPQueue($that->channel);
        // Full queue name prepend queue prefix
        $queue->setName($that->queue_prefix . $queue_name);
        $queue->setFlags(AMQP_DURABLE);
        $queue->declareQueue();

        // Use queue name as the binding key
        $queue->bind($that->exchange_name, $queue_name);

        while(true)
        {
            $message = $queue->get();
            if($message === false)
            {
                continue;
            }

            // 业务规则在这里
            $result = $callback($message->getBody());

            var_dump($result);

            // echo $message->getBody(), PHP_EOL;
            $queue->ack($message->getDeliveryTag());
        }
    }

    public static function add_job($queue_name, $payload, $host='rabbitmq', $options = array())
    {
        if(!isset($GLOBALS['config'][$host]))
        {
            throw new InvalidArgumentException("The host {$host} is not exists.");
        }

        $that = self::get_instance($GLOBALS['config'][$host]);
        $that->connect();

        $that = self::get_instance($GLOBALS['config'][$host]);
        $that->connect();

        if(isset($options['exchange_name']))
        {
            $that->exchange_name = $options['exchange_name'];
        }

        if(isset($options['queue_prefix']))
        {
            $that->queue_prefix = $options['queue_prefix'];
        }

        // AMQPC Exchange is the publishing mechanism
        $exchange = new AMQPExchange($that->channel);
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $exchange->setName($that->exchange_name);
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declareExchange();

        $message = $payload;
        $attributes = [
            'content_type' => 'text/plain',
            'delivery_mode' => 2
        ];
        return $exchange->publish($message, $queue_name, AMQP_NOPARAM, $attributes);
    }

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

    private function declareChannel()
    {
        $this->channel = new AMQPChannel($this->connection);
    }
}


