<?php

declare(strict_types=1);

namespace Verdient\Dora\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 认证
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 * @author Verdient。
 */
class Authentication extends AbstractAnnotation
{
    /**
     * @var string 认证类型
     * @author Verdient。
     */
    public $type = '';

    /**
     * @var bool 是否受权限控制
     * @author Verdient。
     */
    public $privilege = true;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->bindMainProperty('type', $value);
    }
}
