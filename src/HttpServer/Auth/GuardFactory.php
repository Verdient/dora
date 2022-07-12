<?php

declare(strict_types=1);

namespace Verdient\Dora\HttpServer\Auth;

use Hyperf\HttpServer\Router\DispatcherFactory;
use Verdient\Dora\Utils\Container;

/**
 * 守卫工厂
 * @author Verdient。
 */
class GuardFactory
{
    /**
     * @var array 守卫集合
     * @author Verdient。
     */
    protected $guards = [];

    /**
     * 获取守卫
     * @param string 服务器名称
     * @return Guard
     * @author Verdient。
     */
    public function getGuard($serverName)
    {
        if (isset($this->guards[$serverName])) {
            return $this->guards[$serverName];
        }
        $dispatcherFactory = Container::get(DispatcherFactory::class);
        $router = $dispatcherFactory->getRouter($serverName);
        return $this->guards[$serverName] = new Guard($router);
    }
}
