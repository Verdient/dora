<?php

declare(strict_types=1);

namespace Verdient\Dora\Data\Exporter;

use Verdient\Dora\Traits\HasLog;
use Vtiful\Kernel\Excel;
use Vtiful\Kernel\Format;

/**
 * 电子表格导出器
 * @author Verdient。
 */
class SpreadsheetExporter extends AbstractDataExporter
{
    use HasLog;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getExtension(): string
    {
        return 'xlsx';
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function export($filename)
    {
        $dirPath = sys_get_temp_dir();
        $excel  = new Excel([
            'path' => $dirPath
        ]);
        $fileObject = $excel->constMemory($filename, 'Sheet1', false);
        $fileHandle = $fileObject->getHandle();
        $format = new Format($fileHandle);
        $headerStyle = $format->bold()->align(Format::FORMAT_ALIGN_VERTICAL_CENTER)->toResource();
        $fileObject
            ->setType([Excel::TYPE_STRING])
            ->setRow('A1', 20, $headerStyle)
            ->header($this->collector->getHeaders());
        foreach ($this->collector->collect() as $row) {
            foreach ($row as $index2 => $value) {
                if (is_numeric($value) && $value > 99999999999) {
                    $row[$index2] = (string) $value;
                }
            }
            $fileObject->data([$row]);
        }
        $fileObject->output();
        return $dirPath . DIRECTORY_SEPARATOR . $filename;
    }
}
