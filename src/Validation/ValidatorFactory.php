<?php

declare(strict_types=1);

namespace Verdient\Dora\Validation;

use Hyperf\Validation\Validator;
use Hyperf\Validation\ValidatorFactory as ValidationValidatorFactory;

/**
 * @inheritdoc
 * @author Verdient。
 */
class ValidatorFactory extends ValidationValidatorFactory
{
    /**
     * 自定义制造
     * @param array $data 数据
     * @param array $rules 校验规则
     * @param array $message 消息
     * @param array $customAttributes 自定义属性
     * @param callable $resolver 处理器
     * @author Verdient。
     */
    public function makeCustom(array $data, array $rules, array $messages = [], array $customAttributes = [], callable $resolver)
    {
        $validator = call_user_func($resolver, $this->translator, $data, $rules, $messages, $customAttributes);
        if (!is_null($this->verifier)) {
            $validator->setPresenceVerifier($this->verifier);
        }
        if (!is_null($this->container)) {
            $validator->setContainer($this->container);
        }
        $validator instanceof Validator && $this->addExtensions($validator);
        return $validator;
    }
}
