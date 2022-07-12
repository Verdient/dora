<?php

declare(strict_types=1);

namespace Verdient\Dora\Amqp;

use Hyperf\Snowflake\IdGeneratorInterface;
use Verdient\Dora\Utils\Container;

/**
 * 消息
 * @author Verdient。
 */
class Message
{
    /**
     * @var int 消息编号
     * @author Verdient。
     */
    protected $id;

    /**
     * @var string 消息内容
     * @author Verdient。
     */
    protected $message;

    /**
     * @var float 创建时间
     * @author Verdient。
     */
    protected $createdAt;

    /**
     * @author Verdient。
     */
    public function __construct($message)
    {
        $this->id = Container::get(IdGeneratorInterface::class)->generate();
        $this->message = $message;
        $this->createdAt = microtime(true);
    }

    /**
     * 获取编号
     * @return int
     * @author Verdient。
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 获取消息内容
     * @return string
     * @author Verdient。
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * 获取消息创建时间
     * @return float
     * @author Verdient。
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
