<?php

declare(strict_types=1);

namespace Verdient\Dora\Command\Producer;

use Hyperf\Command\Annotation\Command;
use Hyperf\Di\ReflectionManager;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use ReflectionNamedType;
use Swoole\Coroutine;
use Verdient\cli\Console;
use Verdient\Dora\Traits\HasProducer;
use function Swoole\Coroutine\run;

/**
 * 生产消息
 * @Command
 * @author Verdient。
 */
class ProducerProduceCommand extends AbstractProducerCommand
{
    use HasProducer;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected $name = 'producer:produce';

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
        $this->setDescription('生产消息');
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
        foreach ($this->producers as $key => $producer) {
            $length = strlen($key);
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }
        foreach ($this->producers as $key => $producer) {
            $choices[] = $key . '  ' . str_repeat(' ', $maxLength - strlen($key)) . $producer['description'];
        }
        run(function () use ($choices) {
            Coroutine::create(function () use ($choices) {
                loop:
                $choice = $this->choice('请选择生产的消息', $choices);
                $producerName = substr($choice, 0, strpos($choice, ' '));
                $tip = '您选择的消息为: ' . preg_replace('/\s(?=\s)/', '\\1', $choice);
                $this->comment($tip);
                $reflectClass = ReflectionManager::reflectClass($this->producers[$producerName]['class']);
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

                    $this->produce($reflectClass->newInstanceArgs($args));
                } else {
                    $this->produce($reflectClass->newInstance());
                }
                $this->success('消息生产成功');
                Console::output('');
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
        } else if ($paramType === 'array') {
            $tip = $description ? ('请输入 ' . $description . '(' . $name . ')' . ', 多个值请使用,隔开') : ('请输入 ' . $name . ', 多个值请使用,隔开');
            $answer = $this->ask($tip, $default);
            return explode(',', $answer);
        }
        return null;
    }
}
