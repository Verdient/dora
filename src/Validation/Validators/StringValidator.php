<?php

declare(strict_types=1);

namespace Verdient\Dora\Validation\Validators;

use Verdient\Dora\Annotation\Validator;

/**
 * 字符串校验器
 * @Validator
 * @author Verdient。
 */
class StringValidator extends AbstractValidator
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function implicitValidator(): array
    {
        return [
            'contain_letters' => 'containLetters',
            'contain_numbers' => 'containNumbers'
        ];
    }

    /**
     * 包含字母
     * @param string $value 值
     * @return bool
     * @author Verdient。
     */
    public static function containLetters($attribute, $value, $parameters, $validator): bool
    {
        for ($i = 0; $i < strlen($value); $i++) {
            $charCode = ord($value[$i]);
            if ($charCode >= 97 && $charCode <= 122) {
                return true;
            }
            if ($charCode >= 65 && $charCode <= 90) {
                return true;
            }
        }
        return false;
    }

    /**
     * 包含数字
     * @param string $value 值
     * @return bool
     * @author Verdient。
     */
    public static function containNumbers($attribute, $value, $parameters, $validator): bool
    {
        for ($i = 0; $i < strlen($value); $i++) {
            $charCode = ord($value[$i]);
            if ($charCode >= 48 && $charCode <= 57) {
                return true;
            }
        }
        return false;
    }
}
