<?php

declare(strict_types=1);

namespace Verdient\Dora\Utils;

use Exception;

/**
 * @inheritdoc
 * @author Verdient。
 */
class Str extends \Hyperf\Utils\Str
{
    /**
     * @var string 全部格式化
     * @author Verdient。
     */
    const NORMALIZE_MODE_FULL = 'full';

    /**
     * @var string 只格式化第一个
     * @author Verdient。
     */
    const NORMALIZE_MODE_FIRST = 'first';

    /**
     * @var string 只格式化最后一个
     * @author Verdient。
     */
    const NORMALIZE_MODE_LAST = 'last';

    /**
     * 格式化字符串
     * @param string $str 待格式化的字符串
     * @param string[] $params 要替换的参数
     * @param string $placeholderStart 占位符起始字符
     * @param string $placeholderEnd 占位符结束字符
     * @param int $mode 替换模式
     * @return string
     * @author Verdient。
     */
    public static function normalize($str, $params, $placeholderStart = '{{', $placeholderEnd = '}}', $mode = 'full')
    {
        switch ($mode) {
            case static::NORMALIZE_MODE_FULL:
                foreach ($params as $name => $value) {
                    $str = static::replace($placeholderStart . $name . $placeholderEnd, (string) $value, $str);
                }
                return $str;
            case static::NORMALIZE_MODE_FIRST:
                foreach ($params as $name => $value) {
                    $str = static::replaceFirst($placeholderStart . $name . $placeholderEnd, (string) $value, $str);
                }
                return $str;
            case static::NORMALIZE_MODE_LAST:
                foreach ($params as $name => $value) {
                    $str = static::replaceLast($placeholderStart . $name . $placeholderEnd, (string) $value, $str);
                }
                return $str;
            default:
                throw new Exception('UNknown mode ' . $mode);
        }
    }

    /**
     * 解析占位符
     * @param string $str 待解析的占位符
     * @param string $placeholderStart 占位符起始字符
     * @param string $placeholderEnd 占位符结束字符
     * @return array
     * @author Verdient。
     */
    public static function parsePlaceholders($str, $placeholderStart = '{{', $placeholderEnd = '}}')
    {
        $placeholderStartLength = mb_strlen($placeholderStart);
        $placeholderEndLength = mb_strlen($placeholderEnd);
        $result = [];
        $offset = 0;
        while (true) {
            $open = mb_strpos($str, $placeholderStart, $offset);
            if ($open === false) {
                break;
            }
            while (true) {
                if (mb_substr($str, $open + 1, $placeholderStartLength) == $placeholderStart) {
                    $open++;
                } else {
                    break;
                }
            }
            $offset = $open;
            $close = mb_strpos($str, $placeholderEnd, $offset);
            if ($close === false) {
                break;
            }
            $result[] = mb_substr($str, $open + $placeholderStartLength, $close - $open - $placeholderStartLength);
            $offset += $placeholderEndLength;
        }
        return array_unique($result);
    }
}
