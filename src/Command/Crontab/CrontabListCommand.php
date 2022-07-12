<?php

declare(strict_types=1);

namespace Verdient\Dora\Command\Crontab;

use Hyperf\Command\Annotation\Command;
use Verdient\cli\Console;
use Verdient\Dora\Traits\HasDocBlock;

/**
 * 定时任务列表
 * @Command
 * @author Verdient。
 */
class CrontabListCommand extends AbstractCrontabCommand
{
    use HasDocBlock;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected $name = 'crontab:list';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function configure()
    {
        parent::configure();
        $this->setDescription('展示所有可用的定时任务');
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function handle()
    {
        $this->register();
        $data = [];
        foreach ($this->crontabs as $name => $crontab) {
            $data[] = [$name, $crontab['description'], $crontab['rule']];
        }
        Console::table($data, ['定时任务名称', '描述', '规则']);
    }
}
