<?php

declare(strict_types=1);

namespace Verdient\Dora\Cache;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @inheritdoc
 * @author Verdient。
 */
class AnnotationManager extends \Hyperf\Cache\AnnotationManager
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function getAnnotation(string $annotation, string $className, string $method): AbstractAnnotation
    {
        $result = parent::getAnnotation($annotation, $className, $method);
        if (!$result->prefix) {
            $result->prefix = str_replace('\\', '_', $className) . '__' . $method;
        }
        return $result;
    }
}
