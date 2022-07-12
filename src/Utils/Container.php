<?php

declare(strict_types=1);

namespace Verdient\Dora\Utils;

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
        return ApplicationContext::getContainer()->get($id);
    }

    /**
     * 获取容器
     * @return mixed
     * @author Verdient。
     */
    public static function container()
    {
        return ApplicationContext::getContainer();
    }
}
