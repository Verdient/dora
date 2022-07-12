<?php

declare(strict_types=1);

namespace Verdient\Dora\Utils;

use Verdient\Dora\HttpServer\Auth\IdentityInterface;

/**
 * 权限
 * @author Verdient。
 */
class Privilege
{
    /**
     * 判断是否允许访问
     * @param IdentityInterface $user 用户
     * @param string $class 要访问的类
     * @param string $method 要访问的方法
     * @return bool
     * @author Verdient。
     */
    public static function isAllowAccess(IdentityInterface $user, $class, $method): bool
    {
        return true;
    }

    /**
     * 允许访问的动作
     * @param IdentityInterface $identity 认证信息
     * @return array
     * @author Verdient。
     */
    public static function allowActions(IdentityInterface $identity)
    {
        $result = [];
        $guardFactory = Container::get(GuardFactory::class);
        $guard = $guardFactory->getGuard('http');
        foreach ($guard->services() as $service) {
            foreach ($guard->actions($service['service']) as $action) {
                if ($guard->needPrivilege($service['service'], $action['action']) && !static::isAllowAccess($identity, $service['service'], $action['action'])) {
                    continue;
                }
                if (!isset($result[$service['service']])) {
                    $result[$service['service']] = [];
                }
                $result[$service['service']][] = $action['action'];
            }
        }
        return $result;
    }
}
