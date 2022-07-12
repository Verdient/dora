<?php

declare(strict_types=1);

namespace Verdient\Dora\Process;

use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\System;
use Verdient\cli\Console;
use Verdient\Dora\Traits\HasLog;

/**
 * 抽象进程
 * @author Verdient。
 */
abstract class AbstractProcess extends \Hyperf\Process\AbstractProcess
{
    use HasLog;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public $name = null;

    /**
     * @var int|array 休眠时间
     * @author Verdient。
     */
    protected $sleep = 0;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __construct(ContainerInterface $container)
    {
        if (!$this->name) {
            $this->name = str_replace('\\', '', substr(static::class, 12));
        }
        parent::__construct($container);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function isEnable($server): bool
    {
        $name = 'PROCESS_' . strtoupper(Str::snake($this->name));
        return env($name, false);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function handle(): void
    {
        while (true) {
            try {
                $this->run();
            } catch (\Throwable $e) {
                $this->logThrowable($e);
                Console::error($e->__toString(), Console::FG_RED);
            }
            if (is_array($this->sleep)) {
                $sleep = random_int($this->sleep[0], $this->sleep[1]);
            } else {
                $sleep = $this->sleep;
            }
            if ($sleep) {
                $this->log()->info('Sleep ' . $sleep . ' seconds');
                System::sleep($sleep);
            }
        }
    }

    /**
     * 运行
     * @author Verdient。
     */
    abstract protected function run();
}
