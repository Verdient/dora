<?php

declare(strict_types=1);

namespace Verdient\Dora\HttpServer\Auth;

use Verdient\Dora\Model\AbstractModel;

/**
 * 认证信息接口
 * @author Verdient。
 */
interface IdentityInterface
{
    /**
     * 获取用户信息
     * @return AbstractModel|null
     * @author Verdient。
     */
    public function user();
}
