<?php

declare(strict_types=1);

namespace Verdient\Dora\HttpServer\Auth;

use Verdient\Dora\Model\AbstractModel;

/**
 * 认证信息
 * @author Verdient。
 */
class Identity implements IdentityInterface
{
    /**
     * @var AbstractModel|null 用户信息
     * @author Verdient。
     */
    protected $user = null;

    /**
     * @var AbstractModel 用户信息
     * @author Verdient。
     */
    public function __construct(AbstractModel $user)
    {
        $this->user = $user;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function user()
    {
        return $this->user;
    }
}
