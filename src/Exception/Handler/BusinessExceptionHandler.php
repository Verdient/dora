<?php

declare(strict_types=1);

namespace Verdient\Dora\Exception\Handler;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Exception\Http\EncodingException;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Verdient\Dora\Exception\BusinessException;

/**
 * 业务异常处理器
 * @author Verdient。
 */
class BusinessExceptionHandler extends \Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->stopPropagation();
        try {
            $result = Json::encode($this->normalize($throwable));
        } catch (\Throwable $exception) {
            throw new EncodingException($exception->getMessage(), $exception->getCode());
        }
        return $response
            ->withStatus(200)
            ->withHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream($result));
    }

    /**
     * 格式化
     * @param BusinessException $throwable
     * @return array
     * @author Verdient。
     */
    protected function normalize($throwable)
    {
        return [
            'code' => $throwable->getCode(),
            'data' => $throwable->getData(),
            'message' => $throwable->getMessage()
        ];
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof BusinessException;
    }
}
