<?php

declare(strict_types=1);

namespace Verdient\Dora\Command\Process;

use Hyperf\Command\Annotation\Command;
use Symfony\Component\Console\Input\InputArgument;
use Verdient\Dora\Utils\Container;

/**
 * 启动进程
 * @Command
 * @author Verdient。
 */
class ProcessStartCommand extends AbstractProcessCommand
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected $name = 'process:start';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function configure()
    {
        parent::configure();
        $this->setDescription('启动进程');
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function handle()
    {
        $this->register();
        $name = $this->input->getArgument('name');
        if (!isset($this->processes[$name])) {
            return $this->error('Unknown process name ' . $name);
        }
        $process = $this->processes[$name];
        $instance = Container::get($process['class']);
        if ($process['annotation']) {
            foreach ($process['annotation'] as $property => $value) {
                if (property_exists($instance, $property) && !is_null($value)) {
                    $instance->{$property} = $value;
                }
            }
        }
        $instance->handle();
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, '进程名称']
        ];
    }
}
