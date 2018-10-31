
# How to use RabbitMQ in PHP

## Library 

- [php-amqp](https://github.com/pdezwart/php-amqp)
- [php-amqplib](https://github.com/php-amqplib/php-amqplib)

## How to use php-amqplib

demo01

## How to use php-amqp

demo02

## How to use cls_rabbitmq.php

demo03

cls_rabbitmq.php 是对 php-amqp 的封装，目的是为了屏蔽实现细节，简化操作。

对于长时间不间断在后台运行的进程，该库加入了以下特征：

1. 信号量处理，实现平滑退出。防止正在执行的队列任务被临时中断
2. 当文件发生修改时，自动退出。能让文件的修改实时生效，无需人工重启进程
3. 当内存占用超过限制时，自动退出。防止进程占用内存过大。
4. 支持设置队列前缀。（不建议）

该库需配合 Supervisor 才能更好工作。

### 弊端

这种封装虽能简化对队列的操作，但是队列所拥有的很多特征是不支持的，比如 死信队列、延迟队列等等。
这种封装可提供傻瓜式的使用体验，如果希望对队列的操作过程有更多控制，应该考虑直接使用第三方 library。

## 参考文献

- [解决rabbitmq消息队列的顺序及重复消费问题](http://xiaorui.cc/2017/05/04/%E8%A7%A3%E5%86%B3rabbitmq%E6%B6%88%E6%81%AF%E9%98%9F%E5%88%97%E7%9A%84%E9%A1%BA%E5%BA%8F%E5%8F%8A%E9%87%8D%E5%A4%8D%E6%B6%88%E8%B4%B9%E9%97%AE%E9%A2%98/)
- [阿里RocketMQ如何解决消息的顺序&重复两大硬伤？](https://dbaplus.cn/news-73-1123-1.html)
- [RabbitMQ](https://blog.mallux.me/2017/02/19/rabbitmq/)

## 相关软件

- [RocketMQ](https://rocketmq.apache.org)

