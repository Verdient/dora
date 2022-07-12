<?php

declare(strict_types=1);

namespace Verdient\Dora\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Verdient\cli\Console;

/**
 * 访问日志中间件
 * @author Verdient。
 */
class AccessLogMiddleware extends AbstractMiddleware
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
            $this->log($request);
            return $response;
        } catch (\Exception $e) {
            $this->log($request);
            throw $e;
        }
    }

    /**
     * 记录访问日志
     * @param ServerRequestInterface $request 请求
     * @author Verdient。
     */
    protected function log(ServerRequestInterface $request)
    {
        if (config('print_access_log')) {
            $serverParams = $request->getServerParams();
            $uri = $request->getUri();
            $timeCost = round((microtime(true) - $serverParams['request_time_float']) * 1000, 2);
            Console::output(implode(' ', [
                date('Y-m-d H:i:s', $serverParams['request_time']),
                Console::colour('[' . $timeCost . ' ms]', Console::FG_YELLOW),
                Console::colour($request->getMethod(), Console::FG_GREEN),
                (string) $uri
            ]));
            Console::stdout(PHP_EOL);
        }
    }
}
