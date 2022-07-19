<?php

declare(strict_types=1);

namespace Verdient\Dora\API\Infrastructure;

use Verdient\http\Response as HttpResponse;
use Verdient\HttpAPI\AbstractResponse;
use Verdient\HttpAPI\Result;

/**
 * 响应
 * @author Verdient。
 */
class Response extends AbstractResponse
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function normailze(HttpResponse $response): Result
    {
        $result = new Result;
        $statusCode = $response->getStatusCode();
        $result->isOK = $statusCode >= 200 && $statusCode < 300;
        $body = $response->getBody();
        if (isset($body['code']) && isset($body['data'])) {
            $code = $body['code'];
            if ($code >= 200 && $code < 300) {
                $result->isOK = true;
                $result->data = $body['data'];
            }
        }
        if (!$result->isOK) {
            $result->errorCode = isset($code) ? $code : $statusCode;
            $result->errorMessage = isset($body['message']) ? $body['message'] : $response->getStatusMessage();
        }
        return $result;
    }
}
