<?php

declare(strict_types=1);

namespace Verdient\Dora\Validation\Validators;

use Verdient\Dora\Annotation\Validator;

/**
 * 数字校验器
 * @Validator
 * @author Verdient。
 */
class NumberValidator extends AbstractValidator
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function numericValidators(): array
    {
        return [
            'decimal' => 'decimal'
        ];
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function replacers(): array
    {
        return ['decimal' => function ($message, $attribute, $rule, $parameters) {
            return str_replace(':decimal', isset($parameters[0]) ? '包含' . $parameters[0] . '个小数的' : '', $message);
        }];
    }

    /**
     * 校验是否是合法的十进制数字
     * @param string $value 值
     * @return bool
     * @author Verdient。
     */
    public static function decimal($attribute, $value, $parameters, $validator)
    {
        $validator->addNumericRule('decimal');
        if (isset($parameters[0])) {
            $decimal = $parameters[0];
            if ($decimal > 0) {
                return !!preg_match('/^-?[0-9]+(.[0-9]{1,' . $decimal . '})?$/', (string) $value);
            }
        }
        $value = explode('.', (string) $value);
        if (count($value) > 2) {
            return false;
        }
        foreach ($value as $element) {
            if (!ctype_digit($element)) {
                return false;
            }
        }
        return true;
    }
}
