<?php

declare(strict_types=1);

namespace Verdient\Dora\Annotation;

use Hyperf\Di\MetadataCollector;

/**
 * 冗余数据收集器
 * @author Verdient。
 */
class DataRedundancyCollector extends MetadataCollector
{
    /**
     * @var array
     * @author Verdient。
     */
    protected static $container = [];

    /**
     * 收集类
     * @param string $className 类的名称
     * @param DataRedundancy $annotation 冗余数据注解
     * @author Verdient。
     */
    public static function collectClass($className, DataRedundancy $annotation): void
    {
        if (!isset(static::$container[$annotation->sourceModel])) {
            static::$container[$annotation->sourceModel] = [];
        }
        if (!isset(static::$container[$annotation->sourceModel][$annotation->sourceField])) {
            static::$container[$annotation->sourceModel][$annotation->sourceField] = [];
        }
        if (!isset(static::$container[$annotation->sourceModel][$annotation->sourceField][$className])) {
            static::$container[$annotation->sourceModel][$annotation->sourceField][$className] = [];
        }
        static::$container[$annotation->sourceModel][$annotation->sourceField][$className] = [$annotation->field, $annotation->sourceKeyField, $annotation->keyField];
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function clear(?string $key = null): void
    {
    }
}
