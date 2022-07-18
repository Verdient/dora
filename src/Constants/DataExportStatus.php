<?php

declare(strict_types=1);

namespace Verdient\Dora\Constants;

use Hyperf\Constants\Annotation\Constants;

/**
 * 账单状态
 * @Constants
 * @author Verdient。
 */
class DataExportStatus extends AbstractConstants
{
    /**
     * @Message("等待中")
     * @author Verdient。
     */
    const PENDING = 1;

    /**
     * @Message("已完成")
     * @author Verdient。
     */
    const COMPLETED = 2;

    /**
     * @Message("失败")
     * @author Verdient。
     */
    const FAILED = 3;
}
