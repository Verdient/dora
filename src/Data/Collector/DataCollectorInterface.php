<?php

declare(strict_types=1);

namespace Verdient\Dora\Data\Collector;

use Iterator;

/**
 * 收集器接口
 * @author Verdient。
 */
interface DataCollectorInterface
{
    /**
     * 收集数据
     * @return Iterator
     * @author Verdient。
     */
    public function collect(): Iterator;

    /**
     * 获取表头
     * @return array
     * @author Verdient。
     */
    public function getHeaders(): array;

    /**
     * 获取分批大小
     * @return int
     * @author Verdient。
     */
    public function getBatchSize(): int;
}
