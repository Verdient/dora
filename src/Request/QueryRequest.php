<?php

namespace Verdient\Dora\Request;

/**
 * 查询请求
 * @author Verdient。
 */
class QueryRequest extends AbstractRequest
{
    /**
     * 校验规则
     * @return array
     * @author Verdient。
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function validationData(): array
    {
        return $this->getQueryParams();
    }
}
