<?php

declare(strict_types=1);

namespace Verdient\Dora\Validation\Validators;

use Verdient\Dora\Annotation\Validator;

/**
 * 手机号码校验器
 * @Validator
 * @author Verdient。
 */
class ArrayValidator extends AbstractValidator
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function validators(): array
    {
        return [
            'unique_array' => 'uniqueArray'
        ];
    }

    /**
     * 数组内的元素是否唯一
     * @param string $value 值
     * @return bool
     * @author Verdient。
     */
    public static function uniqueArray($attribute, $value, $parameters, $validator)
    {
        if (!is_array($value)) {
            return false;
        }
        return count(array_unique($value)) === count($value);
    }
}
