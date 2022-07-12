<?php

declare(strict_types=1);

namespace Verdient\Dora\Command\Event;

use Hyperf\Command\Annotation\Command;
use Hyperf\Di\ReflectionManager;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use ReflectionNamedType;
use Swoole\Coroutine;
use Verdient\Dora\Traits\HasEvent;
use function Swoole\Coroutine\run;

/**
 * 触发事件
 * @Command
 * @author Verdient。
 */
class EventTriggerCommand extends AbstractEventCommand
{
    use HasEvent;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected $name = 'event:trigger';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected $coroutine = false;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function configure()
    {
        parent::configure();
        $this->setDescription('触发事件');
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
        foreach ($this->events as $key => $event) {
            $length = strlen($key);
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }
        foreach ($this->events as $key => $event) {
            $choices[] = $key . '  ' . str_repeat(' ', $maxLength - strlen($key)) . $event['description'];
        }

        run(function () use ($choices) {
            Coroutine::create(function () use ($choices) {
                loop:
                $choice = $this->choice('请选择要触发的事件', $choices);
                $eventName = substr($choice, 0, strpos($choice, ' '));
                $tip = '您选择的事件为: ' . preg_replace('/\s(?=\s)/', '\\1', $choice);
                $this->comment($tip);
                $reflectClass = ReflectionManager::reflectClass($this->events[$eventName]['class']);
                if ($constructor = $reflectClass->getConstructor()) {
                    $args = [];
                    $docCommentParams = [];
                    if ($docComment = $constructor->getDocComment()) {
                        foreach ($this->getDocCommentParams($docComment) as $param) {
                            $docCommentParams[$param->getVariableName()] = [
                                'type' => (string) $param->getType(),
                                'description' => $param->getDescription()->getBodyTemplate()
                            ];
                        }
                    }
                    foreach ($constructor->getParameters() as $param) {
                        $name = $param->getName();
                        if (!$type = $param->getType()) {
                            if (isset($docCommentParams[$name])) {
                                $type = $docCommentParams[$name]['type'];
                            }
                        }
                        $default = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                        $args[$name] = $this->getParam($name, $type, $default, $docCommentParams[$name]['description'] ?? null);
                    }
                    $this->trigger($reflectClass->newInstanceArgs($args));
                } else {
                    $this->trigger($reflectClass->newInstance());
                }
                $this->success('事件触发成功');
                goto loop;
            });
        });
    }

    /**
     * 获取注释定义的参数
     * @param string $docComment 注释
     * @return Param[]
     * @author Verdient。
     */
    protected function getDocCommentParams($docComment)
    {
        return $this->getDocBlockParser()->create($docComment)->getTagsByName('param');
    }

    /**
     * 获取参数
     * @param string $name 参数名称
     * @param ReflectionNamedType $type 参数类型
     * @param mixed $default 默认值
     * @param string 参数描述
     * @return mixed
     * @author Verdient。
     */
    protected function getParam($name, $type, $default = null, $description = null)
    {
        if (!$type) {
            $type = 'string';
        }
        $paramType = is_string($type) ? $type : $type->getName();
        if (in_array($paramType, ['int', 'float', 'string'])) {
            $tip = $description ? ('请输入 ' . $description . '(' . $name . ')') : ('请输入 ' . $name);
            return $this->ask($tip, $default);
        }
        return null;
    }
}
