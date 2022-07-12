<?php

declare(strict_types=1);

namespace Verdient\Dora\Command\Crontab;

use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\ReflectionManager;
use Verdient\Dora\Command\AbstractCommand;
use Verdient\Dora\Traits\HasDocBlock;

/**
 * 抽象定时任务命令
 * @author Verdient。
 */
abstract class AbstractCrontabCommand extends AbstractCommand
{
    use HasDocBlock;

    /**
     * @var array 定时任务集合
     * @author Verdient。
     */
    protected $crontabs = [];

    /**
     * 注册所有的进程
     * @author Verdient。
     */
    public function register()
    {
        $crontabs = $this->getAnnotationCrontabs();
        foreach ($crontabs as $name => $crontab) {
            if ($crontab->memo) {
                $description = $crontab->memo;
            } else {
                $description = '';
                $reflectClass = ReflectionManager::reflectClass($name);
                $docComment = $reflectClass->getDocComment();
                if ($docComment) {
                    $description = $this->getDocBlockParser()->create($docComment)->getSummary();
                }
            }
            $this->crontabs[$name] = [
                'class' => $name,
                'rule' => $crontab->rule,
                'description' => $description,
                'annotation' => $crontab
            ];
            ksort($this->crontabs);
        }
    }

    /**
     * 获取所有通过注解定义的定时任务
     * @return array
     * @author Verdient。
     */
    protected function getAnnotationCrontabs()
    {
        return AnnotationCollector::getClassesByAnnotation(Crontab::class);
    }
}
