<?php

declare(strict_types=1);

namespace Verdient\Dora\HttpServer\Router;

use Hyperf\Utils\Str;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PatchMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use ReflectionMethod;

/**
 * @inheritdoc
 * @author Verdient。
 */
class DispatcherFactory extends \Hyperf\HttpServer\Router\DispatcherFactory
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function getPrefix(string $className, string $prefix): string
    {
        if (!$prefix) {
            $className = explode('\\', $className);
            if ($handledNamespace = substr(end($className), 0, -10)) {
                $prefix = Str::snake($handledNamespace, '-');
            }
        }
        if ($prefix[0] !== '/') {
            $prefix = '/' . $prefix;
        }
        return $prefix;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function handleController(string $className, Controller $annotation, array $methodMetadata, array $middlewares = []): void
    {
        $mappingAnnotations = [
            RequestMapping::class,
            GetMapping::class,
            PostMapping::class,
            PutMapping::class,
            PatchMapping::class,
            DeleteMapping::class,
        ];
        foreach ($methodMetadata as $methodName => $values) {
            foreach ($mappingAnnotations as $mappingAnnotation) {
                if ($mapping = $values[$mappingAnnotation] ?? null) {
                    if (!isset($mapping->path)) {
                        $mapping->path = Str::snake($methodName, '-');
                    }
                }
            }
        }
        parent::handleController($className, $annotation, $methodMetadata, $middlewares);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function parsePath(string $prefix, ReflectionMethod $method): string
    {
        return $prefix . '/' . Str::snake($method->getName(), '-');
    }
}
