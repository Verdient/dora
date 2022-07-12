<?php

declare(strict_types=1);

namespace Verdient\Dora\Pipeline;

use Verdient\Dora\Traits\HasLog;
use Verdient\Pipeline\Pipeline;
use Verdient\Pipeline\StageInterface;

/**
 * 抽象步骤
 * @author Verdient。
 */
abstract class AbstractStage implements StageInterface
{
    use HasLog;

    /**
     * 处理数据
     * @param mixed $payload 载荷
     * @param Pipeline $pipeline 流水线
     * @return mixed
     * @author Verdient。
     */
    abstract public function process($payload, Pipeline $pipeline);

    /**
     * @param mixed $payload 载荷
     * @param Pipeline $pipeline 流水线
     * @return mixed
     * @author Verdient。
     */
    public function __invoke($payload, Pipeline $pipeline)
    {
        return $this->process($payload, $pipeline);
    }
}
