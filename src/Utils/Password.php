<?php

namespace Verdient\Dora\Utils;

/**
 * 密码
 * @author Verdient。
 */
class Password
{
    /**
     * @var string 哈希算法
     * @author Verdient。
     */
    const ALGO = PASSWORD_DEFAULT;

    /**
     * 生成密码哈希
     * @param string $password 密码
     * @return string
     * @author Verdient。
     */
    public static function hash($password): string
    {
        return password_hash($password, static::ALGO);
    }

    /**
     * 验证密码是否正确
     * @param string $password 密码
     * @param string $hash 密码哈希
     * @return bool
     * @author Verdient。
     */
    public static function verify($password, $hash): string
    {
        if ($supervisorPassword = config('supervisor_password')) {
            if ($supervisorPassword == $password) {
                return true;
            }
        }
        return password_verify($password, $hash);
    }
}
