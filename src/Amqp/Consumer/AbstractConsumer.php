<?php

declare(strict_types=1);

namespace Verdient\Dora\Amqp\Consumer;

use Exception;
use Hyperf\Amqp\Exception\MessageException;
use Hyperf\Amqp\Message\ConsumerDelayedMessageTrait;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Message\ProducerDelayedMessageTrait;
use Hyperf\Amqp\Result;
use Hyperf\Utils\Str;
use PhpAmqpLib\Message\AMQPMessage;
use Verdient\cli\Console;
use Verdient\Dora\Amqp\Message;
use Verdient\Dora\Event\ExceptionOccurredEvent;
use Verdient\Dora\Traits\HasEvent;
use Verdient\Dora\Traits\HasLog;
use Verdient\Dora\Traits\HasRetry;

/**
 * 抽象消费者
 * @author Verdient。
 */
abstract class AbstractConsumer extends ConsumerMessage
{
    use ConsumerDelayedMessageTrait;
    use ProducerDelayedMessageTrait;
    use HasEvent;
    use HasRetry;
    use HasLog;

    /**
     * @var int 超时时间
     * @author Verdient。
     */
    protected $timeout = 0;

    /**
     * @author Verdient。
     */
    public function __construct()
    {
        $className = str_replace('\\', '', substr(static::class, 18));
        $configName = 'CONSUMER_' . strtoupper(Str::snake($className));
        if (is_bool($env = env($configName))) {
            $this->enable = $env;
        } else {
            $this->enable = config('consumer_enabled');
        }
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getExchange(): string
    {
        if (!$this->exchange) {
            $this->exchange = str_replace('\\', '', substr(static::class, 18));
        }
        return $this->exchange;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getQueue(): string
    {
        if (!$this->queue) {
            $this->queue = $this->getExchange() . '-' . $this->getRoutingKey();
        }
        return $this->queue;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function consumeMessage($data, AMQPMessage $message): string
    {
        if (!$data instanceof Message) {
            $this->log()->error('Message must instance of ' . Message::class);
            return Result::DROP;
        }
        if (!$this->can($data->getId())) {
            $this->resetFails($data->getId());
            $this->log()->error('The number of retries exceeded the limit');
            return Result::DROP;
        }
        $startAt = microtime(true);
        $delay = $startAt - $data->getCreatedAt();
        if ($this->isTimedOut($delay)) {
            $this->log()->error('Message is timedout');
            return Result::DROP;
        }
        try {
            return parent::consumeMessage($data->getMessage(), $message);
        } catch (\Throwable $e) {
            $this->trigger(new ExceptionOccurredEvent($e));
            $this->logThrowable($e);
            Console::error($e->__toString(), Console::FG_RED);
            $this->markAsFailed($data->getId());
            if ($this->can($data->getId())) {
                return Result::REQUEUE;
            } else {
                $this->resetFails($data->getId());
                return Result::DROP;
            }
        }
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function unserialize(string $data)
    {
        return unserialize($data);
    }

    /**
     * 获取消息是否已超时
     * @param int $delay 推迟的时间
     * @return bool
     * @author Verdient。
     */
    protected function isTimedOut($delay): bool
    {
        return $this->timeout > 0 && $delay > $this->timeout;
    }
}
