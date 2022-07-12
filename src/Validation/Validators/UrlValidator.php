<?php

declare(strict_types=1);

namespace Verdient\Dora\Validation\Validators;

use Hyperf\Utils\Arr;
use Verdient\Dora\Annotation\Validator;

/**
 * URL校验器
 * @Validator
 * @author Verdient。
 */
class UrlValidator extends AbstractValidator
{
    /**
     * 注册校验器
     * @param $validatorFactory 校验器工厂接口
     * @author Verdient。
     */
    public static function dependentValidators(): array
    {
        return [
            'url_unless' => 'urlUnless'
        ];
    }

    /**
     * 校验是否是URL，除非满足给定的条件
     * @param string $value 值
     * @return bool
     * @author Verdient。
     */
    public static function urlUnless($attribute, $value, $parameters, $validator)
    {
        $validator->requireParameterCount(2, $parameters, 'url_unless');
        $data = Arr::get($validator->getData(), $parameters[0]);
        $values = array_slice($parameters, 1);
        if (!in_array($data, $values)) {
            return $validator->validateUrl($attribute, $value);
        }
        return true;
    }
}
