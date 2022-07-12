<?php

declare(strict_types=1);

namespace Verdient\Dora\Command\Producer;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\Utils\Str;
use Verdient\Dora\Command\AbstractCommand;
use Verdient\Dora\Traits\HasDocBlock;

/**
 * 抽象生产者命令
 * @author Verdient。
 */
abstract class AbstractProducerCommand extends AbstractCommand
{
    use HasDocBlock;

    /**
     * @var array 生产者集合
     * @author Verdient。
     */
    protected $producers = [];

    /**
     * 注册所有的生产者
     * @author Verdient。
     */
    public function register()
    {
        $producers = array_keys($this->getAnnotationProducers());
        foreach ($producers as $event) {
            $reflectClass = ReflectionManager::reflectClass($event);
            $name = Str::kebab(str_replace('\\', '', substr($event, strlen('App\Amqp\Producer'))));
            $docComment = $reflectClass->getDocComment();
            $this->producers[$name] = [
                'class' => $event,
                'description' => $docComment ? $this->getDocBlockParser()->create($docComment)->getSummary() : '',
                'annotation' => $annotationProcesses[$event] ?? null
            ];
            ksort($this->producers);
        }
    }

    /**
     * 获取所有通过注解定义的生产者
     * @return array
     * @author Verdient。
     */
    protected function getAnnotationProducers()
    {
        return AnnotationCollector::getClassesByAnnotation(Producer::class);
    }
}
