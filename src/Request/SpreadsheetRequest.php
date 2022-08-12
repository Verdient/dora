<?php

declare(strict_types=1);

namespace Verdient\Dora\Request;

use Hyperf\Contract\ValidatorInterface;
use Hyperf\Validation\Contract\ValidatesWhenResolved;
use Hyperf\Validation\Request\FormRequest;
use Verdient\Dora\Validation\SpreadsheetValidator;
use Verdient\Dora\Validation\ValidatorFactory;

/**
 * 电子表格请求
 * @author Verdient。
 */
class SpreadsheetRequest extends FormRequest implements ValidatesWhenResolved
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
     * 获取文件名称
     * @return string
     * @author Verdient。
     */
    protected function fileName()
    {
        return 'file';
    }

    /**
     * 最少需要的行数
     * @return int
     * @author Verdient。
     */
    protected function minRows()
    {
        return 1;
    }

    /**
     * 最多允许的行数
     * @return int
     * @author Verdient。
     */
    protected function maxRows()
    {
        return 0;
    }

    /**
     * 最多允许的文件大小
     * @return int
     * @author Verdient。
     */
    protected function maxFilesize()
    {
        return 0;
    }

    /**
     * 数据起始行
     * @return int
     * @author Verdient。
     */
    protected function dataRowStartIndex()
    {
        return 2;
    }

    /**
     * 校验规则
     * @return array
     * @author Verdient。
     */
    protected function rules()
    {
        return [];
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function validationData(): array
    {
        return $this->getUploadedFiles();
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function validator(ValidatorFactory $factory): ValidatorInterface
    {
        return $factory->makeCustom($this->validationData(), $this->rules(), $this->messages(), $this->attributes(), function (...$args) {
            $args = [...$args, $this->fileName(), $this->minRows(), $this->maxRows(), $this->maxFilesize(), $this->dataRowStartIndex()];
            $validator = new SpreadsheetValidator(...$args);
            return $validator;
        });
    }
}
