<?php

declare(strict_types=1);

namespace Verdient\Dora\Command\Process;

use Hyperf\Command\Annotation\Command;
use Verdient\cli\Console;

/**
 * 展示所有可用的进程
 * @Command
 * @author Verdient。
 */
class ProcessListCommand extends AbstractProcessCommand
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected $name = 'process:list';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function configure()
    {
        parent::configure();
        $this->setDescription('展示所有可用的进程');
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function handle()
    {
        $this->register();
        $data = [];
        foreach ($this->processes as $name => $process) {
            $data[] = [$name, $process['class'], $process['description']];
        }
        Console::table($data, ['进程名称', '实现的类', '描述']);
    }
}
