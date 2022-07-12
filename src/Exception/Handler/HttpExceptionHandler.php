<?php

declare(strict_types=1);

namespace Verdient\Dora\Exception\Handler;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Exception\Http\EncodingException;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * HTTP异常处理器
 * @author Verdient。
 */
class HttpExceptionHandler extends \Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $response = parent::handle($throwable, $response);
        try {
            $result = Json::encode([
                'code' => $response->getStatusCode(),
                'data' => null,
                'message' => (string) $response->getBody()
            ]);
        } catch (\Throwable $exception) {
            throw new EncodingException($exception->getMessage(), $exception->getCode());
        }
        return $response
            ->withHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream($result));
    }
}
