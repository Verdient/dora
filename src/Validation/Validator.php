<?php

declare(strict_types=1);

namespace Verdient\Dora\Validation;

use Hyperf\Validation\Validator as ValidationValidator;
use Hyperf\Utils\Str;

/**
 * @inheritdoc
 * @author Verdient。
 */
class Validator extends ValidationValidator
{
    /**
     * 批量添加数字扩展
     * @param array $extensions 扩展
     * @author Verdient。
     */
    public function addNumericExtensions($extensions)
    {
        $this->addExtensions($extensions);
        foreach ($extensions as $rule => $extension) {
            $this->numericRules[] = Str::studly($rule);
        }
    }

    /**
     * 添加数字扩展
     * @param string $rule 规则名称
     * @param array $extension 扩展
     * @author Verdient。
     */
    public function addNumericExtension(string $rule, $extension)
    {
        $this->addExtension($rule, $extension);
        $this->numericRules[] = Str::studly($rule);
    }

    /**
     * 添加数字规则
     * @param string $rule 规则名称
     * @author Verdient。
     */
    public function addNumericRule($rule)
    {
        $rule = Str::studly($rule);
        if (!in_array($rule, $this->numericRules)) {
            $this->numericRules[] = $rule;
        }
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function validateString(string $attribute, $value): bool
    {
        if (is_string($value)) {
            return true;
        }
        if (is_numeric($value)) {
            return true;
        }
        return false;
    }
}
