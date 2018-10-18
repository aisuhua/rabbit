<?php
class cls_rabbitmq2
{
    /**
     * 队列开始工作
     *
     * @param $queue_name
     * @param $callback
     * @param string $host
     * @param array $options
     */
    public static function do_job($queue_name, $callback, $host = 'rabbitmq', $options = array())
    {
        if(!isset($GLOBALS['config'][$host]))
        {
            throw new InvalidArgumentException("The host {$host} is not exists.");
        }

        $AMQPCAdapter = AMQPCAdapter::getInstance($GLOBALS['config'][$host]);
        $AMQPCAdapter->run();

        $connection = new AMQPConnection($GLOBALS['config'][$host]);
        $connection->connect();

        $channel = new AMQPChannel($connection);


        $queue = new AMQPQueue($channel);
        $queue->setName($queue_name);
        $queue->setFlags(AMQP_DURABLE);
        $queue->declareQueue();

        //$queue->bind($this->exchange_name, $queue_name);

    }
}

class AMQPCAdapter1
{
    private $connection;
    private $channel;
    private $exchange;
    private $queue;
    private $credentials = [];
    private $exchange_name = 'amq.direct';
    private $queue_prefix = '';

    private static $instance = null;

    public function do_job($queue_name, $callback, $host = 'rabbitmq', $options = array())
    {

    }

    public function __construct($credentials)
    {
        $this->credentials = $credentials;
//        $this->connection = new AMQPConnection($credentials);
//        $this->connection->connect();
//        $this->channel = new AMQPChannel($this->connection);
    }

    private function connect()
    {
        $this->connection = new AMQPConnection($this->credentials);
        $this->connection->connect();

        $this->declareChannel();
    }

    private function declareChannel()
    {
        $this->channel = new AMQPChannel($this->connection);
        $this->channel->setPrefetchCount(1);
    }

    private function declareExchange($exchange_name)
    {
        $this->exchange = new AMQPExchange($this->channel);
        $this->exchange->setType(AMQP_EX_TYPE_DIRECT);
        $this->exchange->setName($exchange_name);
        $this->exchange->setFlags(AMQP_DURABLE);
        $this->exchange->declareExchange();
    }

    private function declareQueue()
    {

    }

    /**
     * 获取当前对象实例（单例模式）
     *
     * @param $credentials
     * @return AMQPCAdapter
     */
    public static function getInstance($credentials)
    {
        $key = json_encode($credentials);
        if(isset(self::$instance[$key]))
        {
            return self::$instance;
        }

        self::$instance[$key] = new self($credentials);
        return self::$instance[$key];
    }
}