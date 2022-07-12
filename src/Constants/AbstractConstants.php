<?php

declare(strict_types=1);

namespace Verdient\Dora\Constants;

use Hyperf\Constants\ConstantsCollector;
use Hyperf\Di\ReflectionManager;

/**
 * 抽象枚举类
 * @author Verdient。
 */
abstract class AbstractConstants extends \Hyperf\Constants\AbstractConstants
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function __callStatic($name, $arguments)
    {
        $result = parent::__callStatic($name, $arguments);
        $class = class_basename(static::class);
        $key = 'constants.' . $class . '.' . $result;
        $result2 = trans($key);
        if ($key !== $result2) {
            return $result2;
        }
        return $result;
    }

    /**
     * 判断是否存在该记录
     * @return bool
     * @author Verdient。
     */
    public static function has($code): bool
    {
        $codes = static::list();
        return isset($codes[$code]);
    }

    /**
     * 获取所有定义的常量
     * @return array
     * @author Verdient。
     */
    public static function constants(): array
    {
        return array_keys(static::list());
    }

    /**
     * 获取常量
     * @param string $name 常量名称
     * @return mixed
     * @author Verdient。
     */
    public static function constant($name)
    {
        $reflection = ReflectionManager::reflectClass(static::class);
        return $reflection->getConstant($name);
    }

    /**
     * 获取定义的常量列表
     * @return array
     * @author Verdient。
     */
    public static function list(): array
    {
        return ConstantsCollector::get(static::class) ?: [];
    }

    /**
     * 获取所有的信息
     * @return array
     * @author Verdient。
     */
    public static function messages(): array
    {
        $result = [];
        foreach (static::list() as $key => $value) {
            $result[$key] = static::getMessage($key);
        }
        return $result;
    }
}
