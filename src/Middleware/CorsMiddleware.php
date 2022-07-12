<?php

declare(strict_types=1);

namespace Verdient\Dora\Middleware;

use Exception;
use Hyperf\Context\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * CORS中间件
 * @author Verdient。
 */
class CorsMiddleware implements MiddlewareInterface
{
    /**
     * @var array|string 允许的域
     * @author Verdient。
     */
    protected $origin = '*';

    /**
     * @var array|string 允许的头部
     * @author Verdient。
     */
    protected $headers = '*';

    /**
     * @var array|string 允许的方法
     * @author Verdient。
     */
    protected $methods = '*';

    /**
     * @var bool 是否允许携带证书
     * @author Verdient。
     */
    protected $credentials = true;

    /**
     * @var array|string 允许额外暴露的头部
     * @author Verdient。
     */
    protected $exposeHeaders = '*';

    /**
     * @var int 预检请求缓存的最大时间
     * @author Verdient。
     */
    protected $maxAge = 86400;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = Context::get(ResponseInterface::class);
        $response = $this->allowOrigin($response, $request);
        $response = $this->allowHeaders($response, $request);
        $response = $this->allowMethods($response, $request);
        $response = $this->allowCredentials($response);
        $response = $this->allowMaxAge($response);
        Context::set(ResponseInterface::class, $response);
        if ($this->isPreflight($request)) {
            return $this->allowExposeHeaders($response);
        }
        try {
            return $this->allowExposeHeaders($handler->handle($request));
        } catch (Exception $e) {
            Context::set(ResponseInterface::class, $this->allowExposeHeaders($response));
            throw $e;
        }
    }

    /**
     * 是否是预检请求
     * @param ServerRequestInterface $request 请求对象
     * @return bool
     * @author Verdient。
     */
    protected function isPreflight($request): bool
    {
        return $request->getMethod() == 'OPTIONS' && $request->hasHeader('Access-Control-Request-Method');
    }

    /**
     * 允许域
     * @param ResponseInterface $response 响应对象
     * @param ServerRequestInterface $request 请求对象
     * @return ResponseInterface
     * @author Verdient。
     */
    protected function allowOrigin($response, $request): ResponseInterface
    {
        $requestOrigin = $request->getHeaderLine('Origin');
        if ($this->origin === '*') {
            return $response->withHeader('Access-Control-Allow-Origin', $requestOrigin ?: '*');
        } else if (in_array($requestOrigin, $this->origin)) {
            return $response->withHeader('Access-Control-Allow-Origin', $requestOrigin);
        }
        return $response;
    }

    /**
     * 允许头部
     * @param ResponseInterface $response 响应对象
     * @param ServerRequestInterface $request 请求对象
     * @return ResponseInterface
     * @author Verdient。
     */
    protected function allowHeaders($response, $request): ResponseInterface
    {
        if ($this->headers === '*') {
            if ($this->isPreflight($request)) {
                if ($requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers')) {
                    return $response->withHeader('Access-Control-Allow-Headers', $requestHeaders);
                }
            } else {
                $headers = $request->getHeaders();
                foreach ([
                    'accept', 'accept-language', 'content-language', 'dpr',
                    'downlink', 'save-data', 'viewport-width', 'width', 'host', 'connection',
                    'pragma', 'cache-control', 'sec-ch-ua', 'sec-ch-ua-mobile', 'user-agent', 'origin',
                    'sec-fetch-site', 'sec-fetch-mode', 'sec-fetch-dest', 'accept-encoding'
                ] as $name) {
                    unset($headers[$name]);
                }
                return $response->withHeader('Access-Control-Allow-Headers', implode(',', array_keys($headers)));
            }
        } else {
            return $response->withHeader('Access-Control-Allow-Headers', implode(',', $this->headers));
        }
        return $response;
    }

    /**
     * 允许方法
     * @param ResponseInterface $response 响应对象
     * @param ServerRequestInterface $request 请求对象
     * @return ResponseInterface
     * @author Verdient。
     */
    protected function allowMethods($response, $request): ResponseInterface
    {
        if ($this->methods === '*') {
            if ($this->isPreflight($request)) {
                return $response->withHeader('Access-Control-Allow-Methods', $request->getHeaderLine('Access-Control-Request-Method'));
            } else {
                return $response->withHeader('Access-Control-Allow-Methods', $request->getMethod());
            }
        } else {
            return $response->withHeader('Access-Control-Allow-Methods', implode(',', $this->methods));
        }
        return $response;
    }

    /**
     * 允许方法
     * @param ResponseInterface $response 响应对象
     * @return ResponseInterface
     * @author Verdient。
     */
    protected function allowCredentials($response): ResponseInterface
    {
        return $response->withHeader('Access-Control-Allow-Credentials', $this->credentials ? 'true' : 'false');
    }

    /**
     * 允许暴露额外的头部
     * @param ResponseInterface $response 响应对象
     * @return ResponseInterface
     * @author Verdient。
     */
    protected function allowExposeHeaders($response): ResponseInterface
    {
        if ($this->exposeHeaders === '*') {
            if (!$response->hasHeader('Access-Control-Expose-Headers')) {
                $responseHeaders = $response->getHeaders();
                foreach ([
                    'Access-Control-Allow-Origin', 'Access-Control-Allow-Headers', 'Access-Control-Allow-Methods',
                    'Access-Control-Allow-Credentials', 'Access-Control-Max-Age', 'Content-Type', 'Server', 'Connection',
                    'Date', 'Content-Length', 'Content-Encoding'
                ] as $name) {
                    unset($responseHeaders[$name]);
                }
                if (!empty($responseHeaders)) {
                    return $response->withHeader('Access-Control-Expose-Headers', implode(',', array_keys($responseHeaders)));
                }
            }
        }
        return $response;
    }

    /**
     * 允许最大缓存时间
     * @param ResponseInterface $response 响应对象
     * @return ResponseInterface
     * @author Verdient。
     */
    protected function allowMaxAge($response): ResponseInterface
    {
        if ($this->maxAge > 0) {
            return $response->withHeader('Access-Control-Max-Age', $this->maxAge);
        }
        return $response;
    }
}
