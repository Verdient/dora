<?php

declare(strict_types=1);

namespace Verdient\Dora\Validation;

/**
 * 校验规则
 * @author Verdient。
 */
class Rule extends \Hyperf\Validation\Rule
{
    /**
     * 当不符合时必填
     * @param string $attribute 字段
     * @param mixed $value 值
     * @author Verdient。
     */
    public static function requiredUnless($attribute, $value): Rules\RequiredUnless
    {
        return new Rules\RequiredUnless($attribute, $value);
    }

    /**
     * 当符合时必填
     * @param string $attribute 字段
     * @param mixed $value 值
     * @author Verdient。
     */
    public static function requiredIf2($attribute, $value): Rules\RequiredIf
    {
        return new Rules\RequiredIf($attribute, $value);
    }
}
