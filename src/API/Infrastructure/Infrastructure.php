<?php

declare(strict_types=1);

namespace Verdient\Dora\API\Infrastructure;

use Verdient\HttpAPI\AbstractClient;

/**
 * 基础设施
 * @author Verdient。
 */
class Infrastructure extends AbstractClient
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public $protocol = 'https';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public $host = 'api.infrastructure.targital.com.cn';

    /**
     * @var string 授权秘钥
     * @author Verdient。
     */
    protected $accessToken = null;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public $request = Request::class;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function request($path): Request
    {
        $request = parent::request($path);
        $request->accessToken = $this->accessToken;
        $request->addHeader('Content-Type', 'application/json');
        $request->setTimeout(60);
        return $request;
    }
}
