<?php

declare(strict_types=1);

namespace Verdient\Dora\Utils;

use Verdient\token\Token as TokenToken;

/**
 * 令牌
 * @author Verdient。
 */
class Token
{
    /**
     * 生成秘钥
     * @param int $id 用户编号
     * @param string $type 类型
     * @return array
     * @author Verdient。
     */
    public static function generate($id, $type = null)
    {
        if (!$type) {
            $type = 'authorization';
        }
        $token = new TokenToken(config('token.' . $type));
        return [$token->generate($id), time() + $token->duration];
    }

    /**
     * 解析
     * @param string $token 秘钥
     * @param string $type 类型
     * @return string|false
     * @author Verdient。
     */
    public static function parse($token, $type = null)
    {
        if (empty($token)) {
            return false;
        }
        if (!$type) {
            $type = 'authorization';
        }
        try {
            return (new TokenToken(config('token.' . $type)))->parse($token);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
