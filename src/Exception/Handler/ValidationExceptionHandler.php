<?php

declare(strict_types=1);

namespace Verdient\Dora\Exception\Handler;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Exception\Http\EncodingException;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Verdient\Dora\Exception\ValidationException;

/**
 * 校验异常处理器
 * @author Verdient。
 */
class ValidationExceptionHandler extends \Hyperf\Validation\ValidationExceptionHandler
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        if ($throwable instanceof ValidationException) {
            $this->stopPropagation();
            $message = $throwable->getMessage();
        } else {
            $response = parent::handle($throwable, $response);
            $message = (string) $response->getBody();
        }
        try {
            $result = Json::encode([
                'code' => 422,
                'data' => null,
                'message' => $message
            ]);
        } catch (\Throwable $exception) {
            throw new EncodingException($exception->getMessage(), $exception->getCode());
        }
        return $response
            ->withStatus(200)
            ->withHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream($result));
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function isValid(Throwable $throwable): bool
    {
        if (!parent::isValid($throwable)) {
            return $throwable instanceof ValidationException;
        }
        return true;
    }
}
