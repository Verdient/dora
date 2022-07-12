<?php

declare(strict_types=1);

namespace Verdient\Dora\Command\Crontab;

use Hyperf\Command\Annotation\Command;
use Verdient\Dora\Crontab\CrontabInterface;
use Verdient\Dora\Traits\HasEvent;

/**
 * 执行定时任务
 * @Command
 * @author Verdient。
 */
class CrontabExecuteCommand extends AbstractCrontabCommand
{
    use HasEvent;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected $name = 'crontab:execute';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function configure()
    {
        parent::configure();
        $this->setDescription('执行定时任务');
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function handle()
    {
        $this->register();
        $choices = [];
        $maxLength = 0;
        foreach ($this->crontabs as $key => $crontab) {
            $length = strlen($key);
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }
        foreach ($this->crontabs as $key => $crontab) {
            $choices[] = $key . '  ' . str_repeat(' ', $maxLength - strlen($key)) . $crontab['description'];
        }
        $choice = $this->choice('请选择要执行的定时任务', $choices);
        $crontabName = substr($choice, 0, strpos($choice, ' '));
        $class = $this->crontabs[$crontabName]['class'];
        $crontab = new $class;
        if ($crontab instanceof CrontabInterface) {
            $crontab->execute();
        } else {
            $this->error('定时任务的实现类需继承自 ' . AbstractCrontab::class);
        }
    }
}
