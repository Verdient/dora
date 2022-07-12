<?php

declare(strict_types=1);

namespace Verdient\Dora\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Request;
use Hyperf\HttpServer\Response;
use Verdient\Dora\Component\DataProvider;
use Verdient\Dora\Model\AbstractModel;
use Verdient\Dora\Utils\Container;
use Verdient\Dora\Utils\Identity;

/**
 * 抽象控制器
 * @author Verdient。
 */
abstract class AbstractController
{
    /**
     * 获取响应对象
     * @return Request
     * @author Verdient。
     */
    protected function request(): Request
    {
        return Container::get(RequestInterface::class);
    }

    /**
     * 获取响应对象
     * @return Response
     * @author Verdient。
     */
    protected function response(): Response
    {
        return Container::get(ResponseInterface::class);
    }

    /**
     * 获取是否是访客
     * @return bool
     * @author Verdient。
     */
    protected function isGuest(): bool
    {
        return Identity::isGuest();
    }

    /**
     * 获取当前访问的用户
     * @param boolean $cache 是否使用缓存中的数据
     * @return AbstractModel|null
     * @author Verdient。
     */
    protected function user($cache = true)
    {
        return Identity::user($cache);
    }

    /**
     * 发送响应
     * @param mixed $result 结果
     * @param int $code 状态码
     * @return array
     * @author Verdient。
     */
    protected function send($result, $code = 200, $message = 'Success')
    {
        if ($result instanceof DataProvider) {
            $result = Serializer::dataProvider($result);
        }
        return Serializer::normalize([
            'code' => $code,
            'data' => $result,
            'message' => $message
        ]);
    }

    /**
     * 发送提示消息
     * @param string $message 提示消息
     * @param int $code 状态码
     * @return array
     * @author Verdient。
     */
    protected function message($message, $code = 200)
    {
        return $this->send(null, $code, $message);
    }

    /**
     * 发送错误消息
     * @param string $message 提示消息
     * @param int $code 状态码
     * @return array
     * @author Verdient。
     */
    protected function errorMessage($message, $code = 422)
    {
        return $this->send(null, $code, $message);
    }
}
