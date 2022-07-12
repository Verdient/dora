<?php

declare(strict_types=1);

namespace Verdient\Dora\Crontab;

use Verdient\Dora\Event\ExceptionOccurredEvent;
use Verdient\Dora\Traits\HasEvent;
use Verdient\Dora\Traits\HasLog;

/**
 * 抽象定时任务
 * @author Verdient。
 */
abstract class AbstractCrontab implements CrontabInterface
{
    use HasEvent;
    use HasLog;

    /**
     * 执行定时任务
     * @author Verdient。
     */
    public function __invoke(...$params)
    {
        try {
            $this->execute(...$params);
        } catch (\Throwable $e) {
            $this->logThrowable($e);
            $this->trigger(new ExceptionOccurredEvent($e));
        }
    }
}
