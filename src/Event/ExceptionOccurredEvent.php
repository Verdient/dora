<?php

declare(strict_types=1);

namespace Verdient\Dora\Event;

use Throwable;
use Verdient\Dora\Annotation\Event;

/**
 * 发生异常事件
 * @Event
 * @author Verdient。
 */
class ExceptionOccurredEvent
{
    /**
     * @var string 消息
     * @author Verdient。
     */
    public $message;

    /**
     * @var string 类型
     * @author Verdient。
     */
    public $type;

    /**
     * @var string 文件
     * @author Verdient。
     */
    public $file;

    /**
     * @var int 行号
     * @author Verdient。
     */
    public $line;

    /**
     * @param Throwable|array $exception 异常
     * @author Verdient。
     */
    public function __construct($exception)
    {
        if (is_array($exception)) {
            $this->message = $exception['message'];
            $this->type = $exception['type'];
            $this->file = $exception['file'];
            $this->line = $exception['line'];
        } else {
            $this->message = $exception->getMessage();
            $this->type = get_class($exception);
            $this->file = $exception->getFile();
            $this->line = $exception->getLine();
        }
    }
}
