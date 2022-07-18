<?php

declare(strict_types=1);

namespace Verdient\Dora\Annotation;

use Exception;
use Hyperf\Di\MetadataCollector;

/**
 * 数据采集器收集器
 * @author Verdient。
 */
class DataCollectorCollector extends MetadataCollector
{
    /**
     * @var array
     * @author Verdient。
     */
    protected static $container = [];

    /**
     * 收集类
     * @param string $className 类的名称
     * @param DataCollector $annotation 数据采集器注解
     * @author Verdient。
     */
    public static function collectClass($className, DataCollector $annotation): void
    {
        if (isset(static::$container[$annotation->dataSet])) {
            throw new Exception('Only one data collector can be set for the dataset ' . $annotation->dataSet);
        }
        static::$container[$annotation->dataSet] = $className;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function clear(?string $key = null): void
    {
    }
}
