<?php

declare(strict_types=1);

namespace Verdient\Dora\Data\Collector;

use Verdient\Dora\Traits\HasLog;

/**
 * 抽象收集器
 * @author Verdient。
 */
abstract class AbstractDataCollector implements DataCollectorInterface
{
    use HasLog;

    /**
     * @var array 过滤参数
     * @author Verdient。
     */
    protected $params = [];

    /**
     * @param array $params 参数
     * @author Verdient。
     */
    public function __construct($params = [])
    {
        if (is_array($params)) {
            $this->params = $params;
        }
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getBatchSize(): int
    {
        return 5000;
    }
}
