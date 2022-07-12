<?php

declare(strict_types=1);

namespace Verdient\Dora\Validation\Validators;

use Verdient\Dora\Annotation\Validator;

/**
 * 雪花校验器
 * @Validator
 * @author Verdient。
 */
class SnowflakeValidator extends AbstractValidator
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function implicitValidator(): array
    {
        return [
            'snowflake' => 'snowflake'
        ];
    }

    /**
     * 校验必须是雪花编号
     * @param string $value 值
     * @return bool
     * @author Verdient。
     */
    public static function snowflake($attribute, $value, $parameters, $validator): bool
    {
        if (is_null($value)) {
            return true;
        }
        if ($value === '') {
            return true;
        }
        if (!is_numeric($value)) {
            return false;
        }
        if (strlen((string) $value) < 18) {
            return false;
        }
        if ((floor((float)$value) - $value) != 0) {
            return false;
        }
        return true;
    }
}
