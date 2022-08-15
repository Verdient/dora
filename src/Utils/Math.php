<?php

declare(strict_types=1);

namespace Verdient\Dora\Utils;

/**
 * 数学计算库
 * @author Verdient。
 */
class Math
{
    /**
     * 加法
     * @param string|int|float $left 左边的数字
     * @param string|int|float $right 左边的数字
     * @param int $scale 小数点位数
     * @return string
     * @author Verdient。
     */
    public static function add($left, $right, $scale = 0)
    {
        return bcadd(trim((string) $left), trim((string) $right), $scale);
    }

    /**
     * 减法
     * @param string|int|float $left 左边的数字
     * @param string|int|float $right 左边的数字
     * @param int $scale 小数点位数
     * @return string
     * @author Verdient。
     */
    public static function sub($left, $right, $scale = 0)
    {
        return bcsub(trim((string) $left), trim((string) $right), $scale);
    }

    /**
     * 乘法
     * @param string|int|float $left 左边的数字
     * @param string|int|float $right 左边的数字
     * @param int $scale 小数点位数
     * @return string
     * @author Verdient。
     */
    public static function mul($left, $right, $scale = 0)
    {
        return bcmul(trim((string) $left), trim((string) $right), $scale);
    }

    /**
     * 除法
     * @param string|int|float $left 左边的数字
     * @param string|int|float $right 左边的数字
     * @param int $scale 小数点位数
     * @return string
     * @author Verdient。
     */
    public static function div($left, $right, $scale = 0)
    {
        return bcdiv(trim((string) $left), trim((string) $right), $scale);
    }

    /**
     * 比较两个数字的大小
     * @param string|int|float $left 左边的数字
     * @param string|int|float $right 左边的数字
     * @param int $scale 小数点位数
     * @return int
     * @author Verdient。
     */
    public static function comp($left, $right, $scale = 0)
    {
        return bccomp(trim((string) $left), trim((string) $right), $scale);
    }

    /**
     * 拆分
     * @param string $amount 金额
     * @param int $number 拆分的份数
     * @param int $scale 小数点位数
     * @return array
     * @author Verdient。
     */
    public static function split($amount, $number, $scale): array
    {
        $splitdAmount = static::div($scale, $amount, $number);
        $result = array_fill(0, $number, $splitdAmount);
        $total = static::mul($scale, $splitdAmount, $number);
        if (static::comp($scale, $amount, $total) !== 0) {
            $result[$number - 1] = static::add($result[$number - 1], static::sub($amount, $total, $scale), $scale);
        }
        return $result;
    }

    /**
     * 按比例拆分
     * @param string $amount 金额
     * @param array $ratios 比例集合
     * @param int $scale 小数点位数
     * @return array
     * @author Verdient。
     */
    public static function splitRatio($amount, $ratios, $scale): array
    {
        $result = [];
        $total = 0;
        $lastIndex = count($ratios) - 1;
        $maxRatio = $ratios[$lastIndex];
        $maxIndex = $lastIndex;
        foreach ($ratios as $index => $ratio) {
            if ($ratio > $maxRatio) {
                $maxRatio = $ratio;
                $maxIndex = $index;
            }
            $total += $ratio;
        }
        $realTotal = 0;
        foreach ($ratios as $ratio) {
            $number = static::mul(($ratio / $total), $amount, 2);
            $result[] = $number;
            $realTotal = static::add($realTotal, $number, $scale);
        }
        if (static::comp($amount, $realTotal, $scale) !== 0) {
            $result[$maxIndex] = static::add($result[$maxIndex], static::sub($amount, $realTotal, $scale), $scale);
        }
        return $result;
    }
}
