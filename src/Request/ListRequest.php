<?php

namespace Verdient\Dora\Request;

use Hyperf\Contract\ValidatorInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

/**
 * 列表请求
 * @author Verdient。
 */
class ListRequest extends QueryRequest
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function attributes(): array
    {
        return [
            'page' => '页码',
            'page_size' => '分页大小'
        ];
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function getRules()
    {
        return array_merge([
            'page' => ['int', ['min', 1]],
            'page_size' => ['int', ['min', 1], ['max', 500]]
        ], parent::getRules());
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function createDefaultValidator(ValidatorFactoryInterface $factory): ValidatorInterface
    {
        $rules = array_merge([
            'page' => ['int', ['min', 1]],
            'page_size' => ['int', ['min', 1], ['max', 500]]
        ], $this->getRules());
        $attributes = array_merge([
            'page' => '页码',
            'page_size' => '分页大小'
        ], $this->attributes());
        return $factory->make(
            $this->validationData(),
            $rules,
            $this->messages(),
            $attributes
        );
    }
}
