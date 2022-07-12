<?php

declare(strict_types=1);

namespace Verdient\Dora\Command\Process;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\Process\Annotation\Process;
use Hyperf\Utils\Str;
use Verdient\Dora\Command\AbstractCommand;
use Verdient\Dora\Traits\HasDocBlock;

/**
 * 抽象进程命令
 * @author Verdient。
 */
abstract class AbstractProcessCommand extends AbstractCommand
{
    use HasDocBlock;

    /**
     * @var array 进程集合
     * @author Verdient。
     */
    protected $processes = [];

    /**
     * 注册所有的进程
     * @author Verdient。
     */
    public function register()
    {
        $processes = config('processes', []);
        $annotationProcesses = $this->getAnnotationProcesses();
        $processes = array_merge($processes, array_keys($annotationProcesses));
        foreach ($processes as $process) {
            $reflectClass = ReflectionManager::reflectClass($process);
            $propertys = $reflectClass->getDefaultProperties();
            $name = $propertys['name'] ?: Str::kebab(str_replace('\\', '', substr($process, 12)));
            $docComment = $reflectClass->getDocComment();
            $this->processes[$name] = [
                'class' => $process,
                'description' => $docComment ? $this->getDocBlockParser()->create($docComment)->getSummary() : '',
                'annotation' => $annotationProcesses[$process] ?? null
            ];
            ksort($this->processes);
        }
    }

    /**
     * 获取所有通过注解定义的进程
     * @return array
     * @author Verdient。
     */
    protected function getAnnotationProcesses()
    {
        return AnnotationCollector::getClassesByAnnotation(Process::class);
    }
}
