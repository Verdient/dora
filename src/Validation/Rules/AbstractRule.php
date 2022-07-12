<?php

declare(strict_types=1);

namespace Verdient\Dora\Validation\Rules;

/**
 * 抽象规则
 * @author Verdient。
 */
abstract class AbstractRule
{
    /**
     * @var string 规则名称
     * @author Verdient。
     */
    protected $rule = null;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * 转为字符串
     * @return string
     * @author Verdient。
     */
    abstract public function toString(): string;
}
