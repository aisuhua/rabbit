
总结几个概念

1. 消息是发布到交换器，由交换器将消息路由到队列。
    - 发送到交换器的每条信息都带有 routing key；
    - 队列使用 binding key 绑定到交换器；
    - 交换器根据消息的 routing key，将消息路由到已绑定到该交换器并且 binding key 与 routing key 相符合的队列；
    - fanout 交换器为广播类型，不用指定 binding key 和 routing key，若存在则会被忽略；
    - direct 交换器要求队列的 binding key 和消息的 routing key 必须一致，才会将该消息路由到该队列；
    - topic 交换器可以让队列的 binding key 更加灵活，模糊匹配到更多 routing key。
    
2. 若不进行显式绑定，队列创建后默认使用队列名作为 binding key 绑定到默认交换器（AMQP default）。
    - 默认交换器（AMQP default）是一个名字为空的 direct 类型的交换器；
    - 系统内置的 amp.direct、amp.fanout、amp.topic 交换器可以直接使用，无需自行创建交换器亦可直接使用；
3. 连接到集群中哪一个 RabbitMQ 实例创建队列，队列就会创建在这一个 RabbitMQ 实例。

## ack/nack/reject 的区别

- [Negative Acknowledgements](https://www.rabbitmq.com/nack.html)
- [Consumer Acknowledgements and Publisher Confirms](https://www.rabbitmq.com/confirms.html)
- [Ack or Nack in rabbitMQ](https://stackoverflow.com/questions/28794123/ack-or-nack-in-rabbitmq)