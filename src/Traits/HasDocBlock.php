<?php

declare(strict_types=1);

namespace Verdient\Dora\Traits;

use phpDocumentor\Reflection\DocBlockFactory;

/**
 * 包含注释
 * @author Verdient。
 */
trait HasDocBlock
{
    /**
     * @var DocBlockFactory 解析器
     * @author Verdient。
     */
    protected $parser;

    /**
     * 获取注释解析器
     * @return DocBlockFactory
     * @author Verdient。
     */
    protected function getDocBlockParser(): DocBlockFactory
    {
        if (!$this->parser) {
            $this->parser = DocBlockFactory::createInstance();
        }
        return $this->parser;
    }
}
