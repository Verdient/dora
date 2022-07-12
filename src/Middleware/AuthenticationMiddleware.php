<?php

declare(strict_types=1);

namespace Verdient\Dora\Middleware;

use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as ContractResponseInterface;
use Hyperf\HttpServer\Router\Dispatched;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Verdient\Dora\HttpServer\Auth\GuardFactory;
use Verdient\Dora\Utils\Container;

/**
 * 认证中间件
 * @author Verdient。
 */
class AuthenticationMiddleware extends AbstractMiddleware
{
    /**
     * @var bool 是否跳过权限
     * @author Verdient。
     */
    protected $skipPrivilege = false;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __construct(ContainerInterface $container, ContractResponseInterface $response, RequestInterface $request)
    {
        parent::__construct($container, $response, $request);
        $this->skipPrivilege = config('skip_privilege', $this->skipPrivilege);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $dispatched = $request->getAttribute(Dispatched::class);
        if ($dispatched instanceof Dispatched && $dispatched->isFound() && is_array($dispatched->handler->callback)) {
            /**
             * @var GuardFactory
             */
            $guardFactory = Container::get(GuardFactory::class);
            $guard = $guardFactory->getGuard('http');
            if (!$guard->checkAuthentication($request)) {
                throw new HttpException(401, 'Unauthorized');
            }
            if ($this->skipPrivilege !== true && !$guard->checkPrivilege($request)) {
                throw new HttpException(403, 'Forbidden');
            }
        }
        return $handler->handle($request);
    }
}
