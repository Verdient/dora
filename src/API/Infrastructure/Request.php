<?php

declare(strict_types=1);

namespace Verdient\Dora\API\Infrastructure;

use Verdient\Dora\Component\Request as ComponentRequest;

/**
 * 响应
 * @author Verdient。
 */
class Request extends ComponentRequest
{
    /**
     * @var string 授权秘钥
     * @author Verdient。
     */
    public $accessToken = null;

    /**
     * @inheritdoc
     * @return Response
     * @author Verdient。
     */
    public function send()
    {
        $this->addHeader('Authorization', $this->accessToken);
        return new Response(parent::send());
    }
}
