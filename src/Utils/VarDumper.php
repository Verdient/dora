<?php

namespace Verdient\Dora\Utils;

/**
 * 变量导出
 * @author Verdient。
 */
class VarDumper
{
    /**
     * 将变量导出为字符串
     * @param mixed $variable 变量
     * @return string
     * @author Verdient。
     */
    public static function dumpAsString($variable)
    {
        return static::dumpInternal($variable);
    }

    /**
     * 内部导出
     * @param mixed $variable 变量
     * @param string $prefix 前缀
     * @return string
     * @author Verdient。
     */
    protected static function dumpInternal($variable, $prefix = '')
    {
        if (is_array($variable)) {
            $output = '[' . PHP_EOL;
            $prefix2 = $prefix . '  ';
            foreach ($variable as $key => $value) {
                if (is_array($value)) {
                    $output .= $prefix2 . '\'' . $key . '\' => ' . static::dumpInternal($value, $prefix2);
                } else {
                    $output .= $prefix2 . '\'' . $key . '\' => ';
                    $type = gettype($value);
                    switch ($type) {
                        case 'boolean':
                            $output .= 'bool(' . ($value ? 'true' : false) . ')';
                            break;
                        case 'integer':
                            $output .= 'int(' . $value . ')';
                            break;
                        case 'double':
                            $output .= 'float(' . strlen($value) . ')';
                            break;
                        case 'string':
                            $output .= 'string(' . strlen($value) . ') "' . $value . '"';
                            break;
                        case 'object':
                            $output .= 'object(' . get_class($value) . ')';
                            break;
                        case 'resource':
                            $output .= 'resource of type (' . get_resource_type($value) . ')';
                            break;
                        default:
                            $output .= $type;
                            break;
                    }
                    $output .= ',' . PHP_EOL;
                }
            }
            return $output . $prefix . ']' . PHP_EOL;
        } else {
            $type = gettype($variable);
            switch ($type) {
                case 'boolean':
                    return 'bool(' . ($variable ? 'true' : false) . ')' . PHP_EOL;
                case 'integer':
                    return 'int(' . $variable . ')' . PHP_EOL;
                case 'double':
                    return 'float(' . strlen($variable) . ')' . PHP_EOL;
                case 'string':
                    return 'string(' . strlen($variable) . ') "' . $variable . '"' . PHP_EOL;
                case 'object':
                    return 'object(' . get_class($variable) . ')' . PHP_EOL;
                case 'resource':
                    return 'resource of type (' . get_resource_type($variable) . ')' . PHP_EOL;
                default:
                    return $type;
            }
        }
    }
}
