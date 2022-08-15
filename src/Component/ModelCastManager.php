<?php

declare(strict_types=1);

namespace Verdient\Dora\Component;

/**
 * 模型字段映射管理器
 * @author Verdient。
 */
class ModelCastManager
{
    /**
     * @var array 映射信息
     * @author Verdient。
     */
    protected static $casts = [];

    /**
     * 获取表的字段
     * @param string $tableName 表名称
     * @return array
     * @author Verdient。
     */
    public static function get($tableName): array
    {
        if (isset(static::$casts[$tableName]) && !empty(static::$casts[$tableName])) {
            return static::$casts[$tableName];
        }
        $casts = [];
        foreach (SchemaManager::getColumns($tableName) as $name => $value) {
            switch (static::getType($value['type'])) {
                case 'int':
                case 'integer':
                case 'tinyint':
                case 'smallint':
                case 'bigint':
                    $casts[$name] = 'integer';
                    break;
                case 'json':
                    $casts[$name] = 'json';
                    break;
                case 'decimal':
                    if ($length = static::getLength($value['type'])) {
                        $length = array_map('trim', explode(',', $length));
                        if (count($length) === 2) {
                            $casts[$name] = 'decimal:' . $length[1];
                        }
                    }
                    break;
            }
        }
        static::$casts[$tableName] = $casts;
        return $casts;
    }

    /**
     * 获取类型
     * @param string $type 类型
     * @return string
     * @author Verdient。
     */
    protected static function getType(string $type): string
    {
        if ($pos = strpos($type, '(')) {
            return substr($type, 0, $pos);
        }
        return $type;
    }

    /**
     * 获取长度
     * @param string $type 类型
     * @return string
     * @author Verdient。
     */
    protected static function getLength(string $type): string
    {
        if ($pos = strpos($type, '(')) {
            if ($pos2 = strpos($type, ')')) {
                if ($pos2 > $pos) {
                    return substr($type, $pos + 1, $pos2 - $pos - 1);
                }
            }
        }
        return null;
    }
}
