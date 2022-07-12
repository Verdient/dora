<?php

declare(strict_types=1);

namespace Verdient\Dora\Validation\Validators;

use Hyperf\Validation\ValidatorFactory;

/**
 * 抽象校验器
 * @author Verdient。
 */
abstract class AbstractValidator
{
    /**
     * 替换器
     * @return array
     * @author Verdient。
     */
    public static function replacers(): array
    {
        return [];
    }

    /**
     * 校验器
     * @return array
     * @author Verdient。
     */
    public static function validators(): array
    {
        return [];
    }

    /**
     * 依赖其他字段的校验器
     * @return array
     * @author Verdient。
     */
    public static function dependentValidators(): array
    {
        return [];
    }

    /**
     * 数字的校验器
     * @return array
     * @author Verdient。
     */
    public static function numericValidators(): array
    {
        return [];
    }

    /**
     * 必须存在的校验器
     * @return array
     * @author Verdient。
     */
    public static function implicitValidator(): array
    {
        return [];
    }

    /**
     * 注册校验器
     * @param $validatorFactory 校验器工厂接口
     * @author Verdient。
     */
    public static function register(ValidatorFactory $validatorFactory)
    {
        foreach (static::validators() as $name => $validator) {
            $validatorFactory->extend($name, static::normalizeCallback($validator));
        }
        foreach (static::dependentValidators() as $name => $validator) {
            $validatorFactory->extendDependent($name, static::normalizeCallback($validator));
        }
        foreach (static::numericValidators() as $name => $validator) {
            $validatorFactory->extend($name, static::normalizeCallback($validator));
        }
        foreach (static::implicitValidator() as $name => $validator) {
            $validatorFactory->extendImplicit($name, static::normalizeCallback($validator));
        }
        foreach (static::replacers() as $name => $replacer) {
            $validatorFactory->replacer($name, $replacer);
        }
    }

    /**
     * 格式化回调函数
     * @return mixed
     * @author Verdient。
     */
    protected static function normalizeCallback($callback)
    {
        if (is_string($callback) && method_exists(static::class, $callback)) {
            $callback = [static::class, $callback];
        }
        return $callback;
    }
}
