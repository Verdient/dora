<?php

declare(strict_types=1);

namespace Verdient\Dora\Command\Event;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\Utils\Str;
use Verdient\Dora\Annotation\Event;
use Verdient\Dora\Command\AbstractCommand;
use Verdient\Dora\Traits\HasDocBlock;

/**
 * 抽象事件命令
 * @author Verdient。
 */
abstract class AbstractEventCommand extends AbstractCommand
{
    use HasDocBlock;

    /**
     * @var array 事件集合
     * @author Verdient。
     */
    protected $events = [];

    /**
     * 注册所有的进程
     * @author Verdient。
     */
    public function register()
    {
        $events = array_keys($this->getAnnotationEvents());
        foreach ($events as $event) {
            $reflectClass = ReflectionManager::reflectClass($event);
            $name = Str::kebab(str_replace('\\', '', substr($event, 10)));
            $docComment = $reflectClass->getDocComment();
            $this->events[$name] = [
                'class' => $event,
                'description' => $docComment ? $this->getDocBlockParser()->create($docComment)->getSummary() : '',
                'annotation' => $annotationProcesses[$event] ?? null
            ];
            ksort($this->events);
        }
    }

    /**
     * 获取所有通过注解定义的事件
     * @return array
     * @author Verdient。
     */
    protected function getAnnotationEvents()
    {
        return AnnotationCollector::getClassesByAnnotation(Event::class);
    }
}
