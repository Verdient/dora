<?php

declare(strict_types=1);

namespace Verdient\Dora\Traits;

use Verdient\Dora\Utils\Container;
use Verdient\WechatWork\WechatWork;

/**
 * 包含企业微信
 * @author Verdient。
 */
trait HasWechatWork
{
    use HasLog;

    /**
     * 发送企业微信消息
     * @param string $message 消息内容
     * @param array|string $to 接收人
     * @author Verdient。
     */
    protected function sendWechatWorkMessage($message, $to)
    {
        if (!$agentId = config('wechat_work.agentId')) {
            return false;
        }
        if (!is_array($to)) {
            $to = [$to];
        }
        /**
         * @var WechatWork
         */
        $wechatWork = Container::get(WechatWork::class);
        $res = $wechatWork
            ->request('message/send')
            ->setMethod('POST')
            ->setBody([
                'agentid' => $agentId,
                'text' => [
                    'content' => $message
                ],
                'msgtype' => 'text',
                'touser' => implode('|', $to)
            ])
            ->withToken($agentId)
            ->send();
        if (!$res->getIsOK()) {
            $this->log()->error($res->getErrorMessage());
            return false;
        }
        return true;
    }
}
