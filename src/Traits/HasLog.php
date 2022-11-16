<?php

namespace Verdient\Dora\Traits;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Framework\Logger\StdoutLogger;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;
use Verdient\Dora\Utils\Container;

/**
 * 包含日志
 * @author Verdient。
 */
trait HasLog
{
    /**
     * @var LoggerInterface 日志
     * @author Verdient。
     */
    protected $logger;

    /**
     * 获取日志名称
     * @return string
     * @author Verdient。
     */
    protected function getLogName()
    {
        return static::class;
    }

    /**
     * 获取日志分组
     * @return string
     * @author Verdient。
     */
    protected function getLogGroup()
    {
        if ($config = Container::get(ConfigInterface::class)) {
            $loggerConfig = $config->get('logger');
            if (isset($loggerConfig[static::class])) {
                return static::class;
            }
        }
        return 'default';
    }

    /**
     * 获取日志组件
     * @return LoggerInterface
     * @author Verdient。
     */
    protected function log(): LoggerInterface
    {
        if (!$this->logger) {
            if ($loggerFactory = Container::get(LoggerFactory::class)) {
                $this->logger = $loggerFactory->get($this->getLogName(), $this->getLogGroup());
            }
            if (!$this->logger) {
                $this->logger = new StdoutLogger(new Config([
                    StdoutLoggerInterface::class => [
                        'log_level' => [
                            LogLevel::EMERGENCY,
                            LogLevel::ALERT,
                            LogLevel::CRITICAL,
                            LogLevel::ERROR,
                            LogLevel::WARNING,
                            LogLevel::NOTICE,
                            LogLevel::INFO,
                            LogLevel::DEBUG,
                        ]
                    ]
                ]));
            }
        }
        return $this->logger;
    }

    /**
     * 设置记录器
     * @param LoggerInterface 记录器
     * @return static
     * @author Verdient。
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function logThrowable(Throwable $throwable): void
    {
        if ($formatter = Container::get(FormatterInterface::class)) {
            $this->log()->emergency($formatter->format($throwable));
        }
    }
}
