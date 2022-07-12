<?php

declare(strict_types=1);

namespace Verdient\Dora\Request;

/**
 * 文件请求
 * @author Verdient。
 */
class FileRequest extends AbstractRequest
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
     * @inheritdoc
     * @author Verdient。
     */
    protected function validationData(): array
    {
        return $this->getUploadedFiles();
    }
}
