<?php

namespace Verdient\Dora\Traits;

use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Amqp\Producer;
use Verdient\Dora\Utils\Container;

/**
 * 包含生产者
 * @author Verdient。
 */
trait HasProducer
{
    /**
     * 生产消息
     * @param ProducerMessageInterface $message 消息
     * @param bool $confirm 消息是否需要确认
     * @param int $timeout 超时时间
     * @return bool
     * @author Verdeint。
     */
    protected function produce(ProducerMessageInterface $message, bool $confirm = true, int $timeout = 5)
    {
        /**
         * @var Producer
         */
        $producer = Container::get(Producer::class);
        return $producer->produce($message, $confirm, $timeout);
    }
}
