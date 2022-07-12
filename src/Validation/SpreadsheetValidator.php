<?php

declare(strict_types=1);

namespace Verdient\Dora\Validation;

use Hyperf\Utils\MessageBag;
use Hyperf\Validation\ValidationException;

/**
 * @inheritdoc
 * @author Verdient。
 */
class SpreadsheetValidator extends Validator
{
    /**
     * @var array 头部信息
     * @author Verdient。
     */
    protected $headers = [];

    /**
     * @var int 当前的行
     * @author Verdient。
     */
    protected $currentRow = 0;

    /**
     * @var array 原始的数据
     * @author Verdient。
     */
    protected $rawData = [];

    /**
     * 设置头部信息
     * @param array $headers 头部信息
     * @author Verdient。
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function passes(): bool
    {
        $this->rawData = $this->data;;
        $this->messages = new MessageBag();
        [$this->distinctValues, $this->failedRules] = [[], []];
        $this->currentRow = 2;
        foreach ($this->rawData as $row) {
            $this->data = $row;
            foreach ($this->rules as $attribute => $rules) {
                $attribute = str_replace('\.', '->', $attribute);
                foreach ($rules as $rule) {
                    $this->validateAttribute($attribute, $rule);
                    if ($this->messages->has($attribute)) {
                        return false;
                    }
                }
            }
            foreach ($this->after as $after) {
                call_user_func($after);
            }
            $this->currentRow++;
        }
        $this->data = $this->rawData;
        return $this->messages->isEmpty();
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getDisplayableAttribute(string $attribute): string
    {
        $message = parent::getDisplayableAttribute($attribute);
        $message .= ' @ ' . $this->headers[$message] . $this->currentRow;
        return $message;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function validateDistinct(string $attribute, $value, array $parameters): bool
    {
        if (!isset($this->distinctValues[$attribute])) {
            $this->distinctValues[$attribute] = [];
        }
        if (in_array($value, $this->distinctValues[$attribute])) {
            return false;
        }
        $this->distinctValues[$attribute][] = $value;
        return true;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function validated(): array
    {
        if ($this->invalid()) {
            throw new ValidationException($this);
        }
        return $this->data;
    }
}
