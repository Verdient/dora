<?php

declare(strict_types=1);

namespace Verdient\Dora\Command;

use Hyperf\Command\Command;

/**
 * 抽象命令
 * @author Verdient。
 */
abstract class AbstractCommand extends Command
{
    /**
     * 成功消息
     * @param string $message 提示消息
     * @author Verdient。
     */
    protected function success($message)
    {
        return $this->info('✅ ' . $message);
    }
}
