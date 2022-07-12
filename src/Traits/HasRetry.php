<?php

declare(strict_types=1);

namespace Verdient\Dora\Traits;

use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Verdient\Dora\Utils\Container;

/**
 * 包含重试
 * @author Verdient。
 */
trait HasRetry
{
    /**
     * @var RedisProxy
     * @author Verdient。
     */
    protected $redis = null;

    /**
     * @var string Redis连接池
     * @author Verdient。
     */
    protected $redisPool = 'default';

    /**
     * 重试次数
     * @return int
     * @author Verdient。
     */
    public function retryLimit(): int
    {
        return 1;
    }

    /**
     * 获取Redis对象
     * @return RedisProxy
     * @author Verdient。
     */
    public function getRedis(): RedisProxy
    {
        if ($this->redis === null) {
            /**
             * @var RedisFactory
             */
            $redisFactory = Container::get(RedisFactory::class);
            $this->redis = $redisFactory->get($this->redisPool);
        }
        return $this->redis;
    }

    /**
     * 将数据标记为失败
     * @param string $key 标识
     * @return static
     * @author Verdient。
     */
    protected function markAsFailed($key)
    {
        $this->getRedis()->incr((string) $key);
        return $this;
    }

    /**
     * 判断是否可以操作
     * @param string $key 标识
     * @param int $failedCount 已经失败的次数
     * @return bool
     * @author Verdient。
     */
    protected function can($key, $failedCount = null): bool
    {
        $result = true;
        $count = $failedCount ?: $this->getRedis()->get((string) $key);
        if ($count && $count > $this->retryLimit()) {
            $result = false;
        }
        return $result;
    }

    /**
     * 重置失败计数
     * @param string $key 标识
     * @return static
     * @author Verdient。
     */
    protected function resetFails($key)
    {
        $this->getRedis()->del((string) $key);
        return $this;
    }
}
