<?php

declare(strict_types=1);

namespace Verdient\Dora\Pipeline;

use Verdient\Dora\Traits\HasLog;
use Verdient\Pipeline\ProcessInterface;

/**
 * 抽象流程
 * @author Verdient。
 */
abstract class AbstractProcess implements ProcessInterface
{
    use HasLog;

    /**
     * 处理
     * @param mixed $payload 载荷
     * @return mixed
     * @author Verdient。
     */
    public function process($payload = null)
    {
        return $this->pipeline()->process($payload);
    }
}
