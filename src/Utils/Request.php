<?php

declare(strict_types=1);

namespace Verdient\Dora\Utils;

use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * 请求
 * @author Verdient。
 */
class Request
{
    /**
     * 获取基础URL
     * @return string
     * @author Verdient。
     */
    public static function baseUrl()
    {
        /** @var RequestInterface */
        $request = Container::get(RequestInterface::class);
        $baseUrl = null;
        if ($scheme = $request->getHeaderLine('X-Forwarded-Scheme')) {
            if ($host = $request->getHeaderLine('X-Forwarded-Host')) {
                $baseUrl = $scheme . '://' . $host;
            }
        }
        if (!$baseUrl) {
            $uri = $request->getUri();
            $baseUrl = Uri::composeComponents($uri->getScheme(), $uri->getAuthority(), '', '', '');
        }
        return $baseUrl;
    }

    /**
     * 生成访问地址
     * @param string $path 访问路径
     * @return string
     * @author Verdient。
     */
    public function to(string $path): string
    {
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        return static::baseUrl() . $path;
    }
}
