<?php

declare(strict_types=1);

namespace Verdient\Dora\Crontab;

/**
 * 定时任务接口
 * @author Verdient。
 */
interface CrontabInterface
{
    /**
     * 执行任务
     * @author Verdient。
     */
    public function execute(...$params);
}
