<?php

declare(strict_types=1);

namespace Verdient\Dora\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Verdient\cli\Console;
use Verdient\Dora\Event\ExceptionOccurredEvent;
use Verdient\Dora\Traits\HasEvent;

/**
 * App异常处理器
 * @author Verdient。
 */
class AppExceptionHandler extends ExceptionHandler
{
    use HasEvent;

    /**
     * @var LoggerInterface
     * @author Verdient。
     */
    protected $logger;

    /**
     * @var bool 是否是DEBUG模式
     * @author Verdient。
     */
    protected $isDebug = false;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __construct(LoggerFactory $logger)
    {
        $this->logger = $logger->get('app');
        $this->isDebug = config('debug', false);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $error = $this->normalizeThrowable($throwable);
        $this->logger->error(
            '[' . $error['type'] . '] ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line'] . PHP_EOL . $error['traceAsString']
        );
        try {
            $this->trigger(new ExceptionOccurredEvent($error));
        } catch (\Throwable $e) {
            $error = $this->normalizeThrowable($e, $throwable);
            $this->logger->error(
                '[' . $error['type'] . '] ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line'] . PHP_EOL . $error['traceAsString']
            );
        }
        if ($this->isDebug) {
            Console::output('[' . $error['type'] . '] ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line'] . PHP_EOL . Console::colour($error['traceAsString'], Console::FG_RED));
        }
        return $response
            ->withStatus(500)
            ->withHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream(Json::encode($this->buildResponseData($error))));
    }

    /**
     * 构建响应数据
     * @param array $error 错误数据
     * @return array
     * @author Verdient。
     */
    protected function buildResponseData($error): array
    {
        if ($this->isDebug) {
            $result = [
                'code' => $error['code'],
                'data' => null,
                'message' => $error['message'],
                'type' => $error['type'],
                'file' => $error['file'],
                'line' => $error['line'],
                'trace' => explode("\n", $error['traceAsString']),
                'previous' => $error['previous'] ? $this->buildResponseData($error['previous']) : null
            ];
        } else {
            $result = [
                'code' => $error['code'],
                'data' => null,
                'message' => 'Internal Server Error.'
            ];
        }
        return $result;
    }

    /**
     * 格式化异常信息
     * @param Throwable $throwable
     * @param Throwable $previous 先前的错误
     * @return array
     * @author Verdient。
     */
    protected function normalizeThrowable(Throwable $throwable, Throwable $previous = null)
    {
        $file = $throwable->getFile();
        $line = $throwable->getLine();
        $message = $throwable->getMessage();
        $traceAsString = $throwable->getTraceAsString();
        $previous = $previous ?: $throwable->getPrevious();
        return [
            'code' => $throwable->getCode(),
            'message' => $message,
            'type' => get_class($throwable),
            'file' => $file,
            'line' => $line,
            'traceAsString' => $traceAsString,
            'previous' => $previous ? $this->normalizeThrowable($previous) : null
        ];
    }

    /**
     * 格式化异常信息
     * @param Throwable $throwable
     * @return array
     * @author Verdient。
     */
    protected function normalize(Throwable $throwable)
    {
        $throwable = $this->normalizeThrowable($throwable);
        $this->logger->error(
            '[' . $throwable['type'] . '] ' . $throwable['message'] . ' in ' . $throwable['file'] . ':' . $throwable['line'] . PHP_EOL . $throwable['trace']
        );
        if (config('debug', false)) {
            $result = [
                'code' => $throwable['code'],
                'data' => null,
                'message' => $throwable['message'],
                'type' => $throwable['type'],
                'file' => $throwable['file'],
                'line' => $throwable['line'],
                'trace' => explode("\n", $throwable['trace']),
                'previous' => $throwable['previous']
            ];
            Console::output('[' . $throwable['type'] . '] ' . $throwable['message'] . ' in ' . $throwable['file'] . ':' . $throwable['line'] . PHP_EOL . Console::colour($throwable['trace'], Console::FG_RED));
            return $result;
        } else {
            return [
                'code' => $throwable['code'],
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
        return true;
    }
}
