<?php

declare(strict_types=1);

namespace Verdient\Dora\Request;

use Hyperf\Validation\Request\FormRequest;

/**
 * 抽象请求
 * @author Verdient。
 */
abstract class AbstractRequest extends FormRequest
{
    /**
     * 获取客户端IP地址
     * @return string|null
     * @author Verdient。
     */
    public function getClientIp()
    {
        foreach (['True-Client-IP', 'CF-Connecting-IP', 'X-Forwarded-For', 'X-Real-IP'] as $headerName) {
            if ($ip = $this->getHeaderLine($headerName)) {
                return $ip;
            }
        }
        $ip = $this->server('remote_addr');
        if (is_array($ip)) {
            return reset($ip);
        }
        return $ip;
    }

    /**
     * 获取客户端位置
     * @return string|null
     * @author Verdient。
     */
    public function getClientLocation()
    {
        if ($location = $this->getHeaderLine('CF-IPCountry')) {
            return $location;
        }
        return null;
    }
}
