<?php

declare(strict_types=1);

namespace Verdient\Dora\Command\Event;

use Hyperf\Command\Annotation\Command;
use Verdient\cli\Console;
use Verdient\Dora\Traits\HasDocBlock;

/**
 * 事件列表
 * @Command
 * @author Verdient。
 */
class EventListCommand extends AbstractEventCommand
{
    use HasDocBlock;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected $name = 'event:list';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function configure()
    {
        parent::configure();
        $this->setDescription('展示所有可用的事件');
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function handle()
    {
        $this->register();
        $data = [];
        foreach ($this->events as $name => $enent) {
            $data[] = [$name, $enent['class'], $enent['description']];
        }
        Console::table($data, ['事件名称', '事件类', '描述']);
    }
}
