<?php

declare(strict_types=1);

namespace Verdient\Dora\Utils;

/**
 * 随机
 * @author Verdient。
 */
class Random
{
    /**
     * 生成随机字符串
     * @param int $length
     * @author Verdient。
     */
    public static function string($length)
    {
        $chars = [
            '~', '`', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '=', '+',
            '[', '{', ']', '}', '\\', '|',
            ';', ':', '\'', '"',
            ',', '<', '.', '>', '/', '?',
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            '1', '2', '3', '4', '5', '6', '7', '8', '9'
        ];
        $max = count($chars) - 1;
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, $max)];
        }
        return $str;
    }
}
