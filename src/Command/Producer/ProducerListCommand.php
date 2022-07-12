<?php

declare(strict_types=1);

namespace Verdient\Dora\Command\Producer;

use Hyperf\Command\Annotation\Command;
use Verdient\cli\Console;
use Verdient\Dora\Traits\HasDocBlock;

/**
 * 生产者列表
 * @Command
 * @author Verdient。
 */
class ProducerListCommand extends AbstractProducerCommand
{
    use HasDocBlock;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected $name = 'producer:list';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function configure()
    {
        parent::configure();
        $this->setDescription('展示所有可用的生产者');
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function handle()
    {
        $this->register();
        $data = [];
        foreach ($this->producers as $name => $enent) {
            $data[] = [$name, $enent['class'], $enent['description']];
        }
        Console::table($data, ['生产者名称', '生产者类', '描述']);
    }
}
