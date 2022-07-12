<?php

declare(strict_types=1);

namespace Verdient\Dora\Exception;

use Hyperf\Server\Exception\ServerException;

/**
 * 业务异常
 * @author Verdient。
 */
class BusinessException extends ServerException
{
    /**
     * @var array 附带的数据
     * @author Verdient。
     */
    protected $data = [];

    /**
     * 获取数据
     * @return $this
     * @author Verdient。
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data 数据
     * @author Verdient。
     */
    public function __construct($message = "", $code = 418, $data = null, $previous = null)
    {
        $this->data = $data;
        return parent::__construct($message, $code, $previous);
    }
}
