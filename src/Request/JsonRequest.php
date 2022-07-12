<?php

declare(strict_types=1);

namespace Verdient\Dora\Request;

/**
 * JSON请求
 * @author Verdient。
 */
class JsonRequest extends AbstractRequest
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function authorize(): bool
    {
        return true;
    }

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
    protected function validationData(): array
    {
        return $this->getParsedBody();
    }
}
