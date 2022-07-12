<?php

declare(strict_types=1);

namespace Verdient\Dora\Amqp\Producer;

use Hyperf\Amqp\Message\ProducerDelayedMessageTrait;
use Hyperf\Amqp\Message\ProducerMessage;
use Verdient\Dora\Amqp\Message;

/**
 * 抽象生产者
 * @author Verdient。
 */
abstract class AbstractProducer extends ProducerMessage
{
    use ProducerDelayedMessageTrait;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function serialize(): string
    {
        return serialize(new Message($this->payload));
    }
}
