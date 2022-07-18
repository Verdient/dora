<?php

declare(strict_types=1);

namespace Verdient\Dora\Model;

use Verdient\Dora\Constants\DataExportStatus;

/**
 * 数据导出
 * @author Verdient。
 */
class DataExport extends AbstractModel
{
    /**
     * 状态标签
     * @return string
     * @author Verdient。
     */
    public function statusLabel()
    {
        return DataExportStatus::getMessage($this->status);
    }
}
