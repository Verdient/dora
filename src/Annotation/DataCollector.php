<?php

declare(strict_types=1);

namespace Verdient\Dora\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 数据收集器
 * @Annotation
 * @Target("CLASS")
 * @author Verdient。
 */
class DataCollector extends AbstractAnnotation
{
    /**
     * @var string 数据集
     * @author Verdient。
     */
    public $dataSet = '';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __construct($value)
    {
        parent::__construct($value);
        $this->bindMainProperty('dataSet', $value);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function collectClass(string $className): void
    {
        DataCollectorCollector::collectClass($className, $this);
    }
}
