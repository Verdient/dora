<?php

declare(strict_types=1);

namespace Verdient\Dora\Data\Exporter;

use Verdient\Dora\Data\Collector\DataCollectorInterface;

/**
 * 抽象导出器
 * @author Verdient。
 */
abstract class AbstractDataExporter implements DataExporterInterface
{
    /**
     * @var CollectorInterface 采集器
     * @author Verdient。
     */
    protected $collector;

    /**
     * @param CollectorInterface 采集器
     * @author Verdient。
     */
    public function __construct(DataCollectorInterface $collector)
    {
        $this->collector = $collector;
    }
}
