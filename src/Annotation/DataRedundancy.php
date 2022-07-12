<?php

declare(strict_types=1);

namespace Verdient\Dora\Annotation;

use Exception;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 冗余数据
 * @Annotation
 * @Target("CLASS")
 * @author Verdient。
 */
class DataRedundancy extends AbstractAnnotation
{
    /**
     * @var string 表名称
     * @author Verdient。
     */
    public $table = null;

    /**
     * @var string 字段
     * @author Verdient。
     */
    public $field = null;

    /**
     * @var string 来源模型
     * @author Verdient。
     */
    public $sourceModel = null;

    /**
     * @var string 来源字段
     * @author Verdient。
     */
    public $sourceField = null;

    /**
     * @var string 外键字段
     * @author Verdient。
     */
    public $keyField = null;

    /**
     * @var string 来源外键字段
     * @author Verdient。
     */
    public $sourceKeyField = null;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __construct($value)
    {
        parent::__construct($value);
        if (!$this->field) {
            throw new Exception('field must be declared');
        }
        if (!$this->sourceModel) {
            throw new Exception('sourceModel must be declared');
        }
        if (!$this->keyField) {
            throw new Exception('keyField must be declared');
        }
        if (!$this->sourceField) {
            $this->sourceField = $this->field;
        }
        if (!$this->sourceKeyField) {
            $this->sourceKeyField = $this->keyField;
        }
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function collectClass(string $className): void
    {
        DataRedundancyCollector::collectClass($className, $this);
    }
}
