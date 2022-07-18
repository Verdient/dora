<?php

declare(strict_types=1);

namespace Verdient\Dora\Data\Exporter;

/**
 * 导出器接口
 * @author Verdient。
 */
interface DataExporterInterface
{
    /**
     * 导出
     * @param string $filename 文件名
     * @return string|false
     * @author Verdient。
     */
    public function export($filename);

    /**
     * 获取后缀名
     * @return string
     * @author Verdient。
     */
    public function getExtension(): string;
}
