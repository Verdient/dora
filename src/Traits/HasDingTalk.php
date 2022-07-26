<?php

declare(strict_types=1);

namespace Verdient\Dora\Traits;

use Verdient\DingTalk\DingTalk;
use Verdient\Dora\Utils\Container;

/**
 * 包含钉钉
 * @author Verdient。
 */
trait HasDingTalk
{
    use HasLog;

    /**
     * 发送钉钉消息
     * @param string $message 消息内容
     * @param array|string $to 接收人
     * @author Verdient。
     */
    protected function sendDingTalkMessage($message, $to)
    {
        if (!is_array($to)) {
            $to = [$to];
        }
        /**
         * @var DingTalk
         */
        $dingTalk = Container::get(DingTalk::class);
        $res = $dingTalk->request('robot/oToMessages/batchSend')
            ->setMethod('POST')
            ->setBody([
                'robotCode' => $dingTalk->appKey,
                'userIds' => $to,
                'msgKey' => 'sampleText',
                'msgParam' => json_encode([
                    'content' => $message
                ])
            ])
            ->withToken()
            ->send();
        if (!$res->getIsOK()) {
            $this->log()->error($res->getErrorMessage());
            return false;
        }
        return true;
    }
}
