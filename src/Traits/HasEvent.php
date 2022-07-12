<?php

namespace Verdient\Dora\Traits;

use Psr\EventDispatcher\EventDispatcherInterface;
use Verdient\Dora\Utils\Container;

/**
 * 包含事件
 * @author Verdient。
 */
trait HasEvent
{
    /**
     * 触发事件
     * @param object $event 事件
     * @author Verdeint。
     */
    protected function trigger(object $event)
    {
        Container::get(EventDispatcherInterface::class)->dispatch($event);
    }
}
