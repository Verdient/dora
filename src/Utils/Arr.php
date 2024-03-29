<?php

declare(strict_types=1);

namespace Verdient\Dora\Utils;

use Exception;
use Hyperf\Database\Model\Collection;
use Hyperf\Utils\Arr as UtilsArr;
use Hyperf\Utils\Contracts\Arrayable;

/**
 * 数组
 * @author Verdient。
 */
class Arr extends UtilsArr
{
    /**
     * 判断数组内的值是否唯一
     * @param array $array 数组
     * @param int $flags 排序方式
     * @author Verdient。
     */
    public static function isDistinct($array, $flags = SORT_REGULAR)
    {
        return count($array) === count(array_unique($array, $flags));
    }

    /**
     * 修改数组键名
     * @param array $array 数组
     * @param string $from 要修改的键名
     * @param string $to 要修改为的键名
     * @return array
     * @author Verdient。
     */
    public static function changeKey($array, $from, $to)
    {
        $alias[$from] = $to;
        return array_combine(array_map(function ($key) use ($alias) {
            return $alias[$key] ?? $key;
        }, array_keys($array)), array_values($array));
    }

    /**
     * 以字段的值作为数组的索引
     * @param array $array 数组
     * @param string $column 字段名
     * @return array
     * @author Verdient。
     */
    public static function indexBy($array, $column)
    {
        if ($array instanceof Collection) {
            $array = $array->all();
        } else if ($array instanceof Arrayable) {
            $array = $array->toArray();
        }
        $result = [];
        foreach ($array as $value) {
            if (is_array($value)) {
                $key = $value[$column];
            } else if (is_object($value)) {
                $key = $value->$column;
            } else {
                throw new Exception('The contents of the array must be an array or stdClass');
            }
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * 以字段的值作为数组的索引
     * @param array $array 数组
     * @param string $column 字段名
     * @return array
     * @author Verdient。
     */
    public static function indexByMulit($array, $column)
    {
        if ($array instanceof Collection) {
            $array = $array->all();
        } else if ($array instanceof Arrayable) {
            $array = $array->toArray();
        }
        $result = [];
        foreach ($array as $value) {
            if (is_array($value)) {
                $key = $value[$column];
            } else if (is_object($value)) {
                $key = $value->$column;
            } else {
                throw new Exception('The contents of the array must be an array or stdClass');
            }
            if (!isset($result[$key])) {
                $result[$key] = [];
            }
            $result[$key][] = $value;
        }
        return $result;
    }

    /**
     * 移除数组中的值
     * @param array $array 数组
     * @param mixed $value 要移除的值
     * @param boolean $strict 是否严格匹配
     * @return array
     * @author Verdient。
     */
    public static function removeValue(array &$array, $value, bool $strict = false)
    {
        foreach ($array as $key => $val) {
            if ($strict) {
                if ($val === $value) {
                    unset($array[$key]);
                }
            } else {
                if ($val == $value) {
                    unset($array[$key]);
                }
            }
        }
        return $array;
    }
}
