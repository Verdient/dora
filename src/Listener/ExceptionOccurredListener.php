<?php

declare(strict_types=1);

namespace Verdient\Dora\Listener;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Annotation\Listener;
use Swoole\Coroutine;
use Verdient\Dora\Annotation\Alertor;
use Verdient\Dora\Event\ExceptionOccurredEvent;
use Verdient\Dora\Traits\HasLog;

/**
 * 异常发生监听器
 * @Listener
 * @author Verdient。
 */
class ExceptionOccurredListener extends AbstractListener
{
    use HasLog;

    /**
     * @param bool 是否发送异常消息
     * @author Verdient。
     */
    protected $exceptionAlert = false;

    /**
     * @var array 发送的消息的哈希和时间
     * @author Verdient。
     */
    protected $hashs = [];

    /**
     * @var int 静默时间
     * @author Verdient。
     */
    protected $silence = 7200;

    /**
     * @var int 上次发送时间
     * @author Verdient。
     */
    protected $lastSendAt = null;

    /**
     * @author Verdient。
     */
    public function __construct()
    {
        $this->exceptionAlert = config('exception_alert', false);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function listen(): array
    {
        return [
            ExceptionOccurredEvent::class,
        ];
    }

    /**
     * @param ExceptionOccurredEvent $event
     * @author Verdient。
     */
    public function process(object $event)
    {
        if ($this->exceptionAlert) {
            $env = env('APP_ENV', 'Unknown environment');
            $key = $event->file . ':' . $event->line;
            if ($this->should($key)) {
                $data = [
                    'message' => $event->message,
                    'type' => $event->type,
                    'file' => $event->file,
                    'line' => $event->line,
                    'env' => $env
                ];
                $alert = '[炸弹] EXCEPTION OCCURRED ‼️‼️';
                foreach ($data as $name => $value) {
                    $alert .= "\n  $name: $value";
                }
                $this->alertDevelopers($alert);
            }
        }
    }

    /**
     * 判断是否要发送
     * @param string $content 消息内容
     * @return bool
     * @author Verdient。
     */
    protected function should($content)
    {
        $now = time();
        $contentHash = hash('sha256', $content);
        foreach ($this->hashs as $hash => $time) {
            $endAt = $now - $this->silence;
            if ($time < $endAt) {
                unset($this->hashs[$hash]);
            }
        }
        if (!isset($this->hashs[$contentHash])) {
            $this->hashs[$contentHash] = $now;
            $this->lastSendAt = $now;
            return true;
        }
        return false;
    }

    /**
     * 提醒开发者
     * @param string $message 提示信息
     * @author Verdient。
     */
    public function alertDevelopers($message)
    {
        $alertors = AnnotationCollector::getClassesByAnnotation(Alertor::class);
        foreach ($alertors as $alertor) {
            Coroutine::create(function () use ($message, $alertor) {
                try {
                    (new $alertor)($message);
                } catch (\Throwable $e) {
                    $this->logThrowable($e);
                }
            });
        }
    }
}
