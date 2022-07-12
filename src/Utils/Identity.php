<?php

declare(strict_types=1);

namespace Verdient\Dora\Utils;

use Hyperf\Context\Context;
use Verdient\Dora\HttpServer\Auth\IdentityInterface;
use Verdient\Dora\Model\AbstractModel;

/**
 * 认证信息
 * @author Verdient。
 */
class Identity
{
    /**
     * @var string 上下文键值
     * @author Verdient。
     */
    const CONTEXT_KEY_NAME = 'userIdentity';

    /**
     * 获取是否是访客
     * @return bool
     * @author Verdient。
     */
    public static function isGuest(): bool
    {
        return !Context::has(static::CONTEXT_KEY_NAME);
    }

    /**
     * 获取认证信息
     * @return IdentityInterface|null
     * @author Verdient。
     */
    public static function get()
    {
        return Context::get(static::CONTEXT_KEY_NAME);
    }

    /**
     * 获取当前用户
     * @param boolean $cache 是否使用缓存中的数据
     * @return AbstractModel|null
     * @author Verdient。
     */
    public static function user($cache = true)
    {
        if (!$identity = static::get()) {
            return null;
        }
        if (!$cache) {
            $user = $identity->user();
            return $user->newQuery()->where($user->getKeyName(), '=', $user->getKey())->first();
        }
        return $identity->user();
    }

    /**
     * 登录
     * @param IdentityInterface 登录的用户
     * @author Verdient。
     */
    public static function login(IdentityInterface $identity)
    {
        Context::set(static::CONTEXT_KEY_NAME, $identity);
    }
}
