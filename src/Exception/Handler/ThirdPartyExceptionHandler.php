<?php

declare(strict_types=1);

namespace Verdient\Dora\Exception\Handler;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Exception\Http\EncodingException;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Verdient\Dora\Exception\ThirdPartyException;

/**
 * 第三方异常处理器
 * @author Verdient。
 */
class ThirdPartyExceptionHandler extends \Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler
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
            ->withStatus($throwable->getCode())
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
        if (config('debug', false)) {
            $result = [
                'code' => $throwable->getCode(),
                'data' => $throwable->getResponse(),
                'message' => $throwable->getMessage(),
                'type' => get_class($throwable),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine()
            ];
            $this->logger->error(
                '[' . $result['type'] . '] ' . $result['message'] . ' in ' . $result['file'] . ':' . $result['line'] . PHP_EOL .
                    $result['data']
            );
            return $result;
        } else {
            return [
                'code' => $throwable->getCode(),
                'data' => null,
                'message' => 'Internal Server Error.'
            ];
        }
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ThirdPartyException;
    }
}
