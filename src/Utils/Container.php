<?php

declare(strict_types=1);

namespace Verdient\Dora\Utils;

use Hyperf\Contract\ContainerInterface;
use Hyperf\Utils\ApplicationContext;

/**
 * 容器
 * @author Verdient。
 */
class Container
{
    /**
     * 获取对象
     * @param string $id 标识
     * @return mixed
     * @author Verdient。
     */
    public static function get(string $id)
    {
        if ($container = static::container()) {
            return $container->get($id);
        }
        return null;
    }

    /**
     * 获取容器
     * @return ContainerInterface|null
     * @author Verdient。
     */
    public static function container()
    {
        if (static::hasContainer()) {
            return ApplicationContext::getContainer();
        }
        return null;
    }

    /**
     * 获取是否存在容器
     * @return bool
     * @author Verdient。
     */
    public static function hasContainer()
    {
        return ApplicationContext::hasContainer();
    }
}
