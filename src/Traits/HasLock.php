<?php

namespace Verdient\Dora\Traits;

use Hyperf\Redis\RedisFactory;
use Lysice\HyperfRedisLock\LockTimeoutException;
use Lysice\HyperfRedisLock\RedisLock;
use Verdient\Dora\Utils\Container;

/**
 * 包含锁
 * @author Verdient。
 */
trait HasLock
{
    /**
     * 获取锁
     * @param string $name 锁的名称
     * @param int $timeout 获取锁超时时间
     * @param int $seconds 锁的时长
     * @param string $redis Redis名称
     * @return RedisLock|false
     * @author Verdient。
     */
    public static function lock($name, $timeout = 5, $seconds = 60, $redis = 'default')
    {
        $lock = new RedisLock(Container::get(RedisFactory::class)->get($redis), $name, $seconds);
        try {
            $lock->block($timeout);
            return $lock;
        } catch (LockTimeoutException $e) {
            return false;
        }
    }
}
