<?php

declare(strict_types=1);

namespace Verdient\Dora\Exception;

use Hyperf\Server\Exception\ServerException;

/**
 * 第三方异常
 * @author Verdient。
 */
class ThirdPartyException extends ServerException
{
    /**
     * @var array 响应数据
     * @author Verdient。
     */
    protected $response = [];

    /**
     * 获取响应
     * @return array
     * @author Verdient。
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param string $response 响应内容
     * @param string $message 提示内容
     * @param int $code 错误代码
     * @param Throwable $previous 先前的错误
     * @author Verdient。
     */
    public function __construct($response, $message = "", $code = 502, $previous = null)
    {
        $this->response = $response;
        return parent::__construct($message, $code, $previous);
    }
}
