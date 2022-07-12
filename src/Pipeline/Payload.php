<?php

declare(strict_types=1);

namespace Verdient\Dora\Pipeline;

/**
 * 载荷
 * @author Verdient。
 */
class Payload
{
    /**
     * @var array 数据
     * @author Verdient。
     */
    protected $data = [];

    /**
     * 设置数据
     * @param string $name 名称
     * @param mixed $value 内容
     * @return static
     * @author Verdient。
     */
    public function setData($name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * 获取数据
     * @param string $name 名称
     * @param mixed $default 默认值
     * @return mixed
     * @author Verdient。
     */
    public function getData($name = null, $dafault = false)
    {
        if ($name) {
            return $this->data[$name] ?? $dafault;
        }
        return $this->data;
    }

    /**
     * 判断数据是否存在
     * @param string $name 名称
     * @return bool
     * @author Verdient。
     */
    public function hasData($name)
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * 合并数据
     * @param static $payloads 载荷
     * @author Verdient。
     */
    public function mergeData(...$payloads)
    {
        foreach ($payloads as $payload) {
            $this->data = array_merge($this->data, $payload->data);
        }
        return $this;
    }
}
