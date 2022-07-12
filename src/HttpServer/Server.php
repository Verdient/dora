<?php

declare(strict_types=1);

namespace Verdient\Dora\HttpServer;

/**
 * @inheritdoc
 * @author Verdient。
 */
class Server extends \Hyperf\HttpServer\Server
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function onRequest($request, $response): void
    {
        if (isset($request->header['host'])) {
            if ($host = $this->normalizeHost($request->header['host'])) {
                $request->header['host'] = $host;
            } else {
                unset($request->header['host']);
            }
        }
        parent::onRequest($request, $response);
    }

    /**
     * 格式化主机名
     * @param string $host 主机名
     * @return string|false
     * @author Verdient。
     */
    public function normalizeHost($host)
    {
        if (!$parsedUrl = parse_url($host)) {
            return false;
        }
        if (!isset($parsedUrl['host'])) {
            return false;
        }
        $result = $parsedUrl['host'];
        if (isset($parsedUrl['port']) && ($parsedUrl['port'] > 0 && $parsedUrl['port'] < 65535)) {
            $result .= ':' . $parsedUrl['port'];
        }
        return $result;
    }
}
