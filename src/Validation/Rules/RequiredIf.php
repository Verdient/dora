<?php

declare(strict_types=1);

namespace Verdient\Dora\Validation\Rules;

use Hyperf\Utils\Str;

/**
 * 当符合条件时必填
 * @author Verdient。
 */
class RequiredIf extends AbstractRule
{
    /**
     * @var string 字段
     * @author Verdient。
     */
    protected $attribute;

    /**
     * @var mixed 值
     * @author Verdient。
     */
    protected $value;

    /**
     * @param string $attribute 属性
     * @param string $value 值
     * @author Verdient。
     */
    public function __construct($attribute, $value)
    {
        $this->attribute = $attribute;
        $this->value = $value;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function toString(): string
    {
        if (!$this->rule) {
            $class = new \ReflectionClass($this);
            $this->rule = Str::snake(lcfirst($class->getShortName()));
        }
        return $this->rule . ':' . $this->attribute . ',' . $this->value;
    }
}
