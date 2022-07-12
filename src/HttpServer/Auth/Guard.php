<?php

declare(strict_types=1);

namespace Verdient\Dora\HttpServer\Auth;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\Handler;
use Hyperf\HttpServer\Router\RouteCollector;
use Psr\Http\Message\ServerRequestInterface;
use Verdient\Dora\Annotation\Authentication;
use Verdient\Dora\Traits\HasDocBlock;
use Verdient\Dora\Utils\Identity;
use Verdient\Dora\Utils\Privilege;
use Verdient\Dora\Utils\Token;
use Verdient\Dora\HttpServer\Auth\Identity as AuthIdentity;
use Verdient\Dora\Model\AbstractModel;

/**
 * 守卫
 * @author Verdient。
 */
class Guard
{
    use HasDocBlock;

    /**
     * @var array 所有的服务
     * @author Verdient。
     */
    protected $services = [];

    /**
     * @var array 所有的动作
     * @author Verdient。
     */
    protected $actions = [];

    /**
     * 注解
     * @author Verdient。
     */
    protected $annotations = [];

    /**
     * 受权限控制的动作
     * @author Verdient。
     */
    protected $privilegeActions = [];

    /**
     * 不受权限控制的动作
     * @author Verdient。
     */
    protected $skipPrivilegeActions = [];

    /**
     * @param RouteCollector 路由收集器
     * @author Verdient。
     */
    public function __construct(RouteCollector $router)
    {
        [$staticRouters, $variableRouters] = $router->getData();
        foreach ($staticRouters as $items) {
            foreach ($items as $handler) {
                $data = $this->analyzeHandler($handler);
                if ($data['service']) {
                    $this->addAction($data['service'], $data['action']);
                }
            }
        }
    }

    /**
     * 获取所有的服务
     * @return array
     * @author Verdient。
     */
    public function services(): array
    {
        return $this->services;
    }

    /**
     * 获取所有的动作
     * @param string $service 服务名称
     * @return array
     * @author Verdient。
     */
    public function actions($service = null): array
    {
        if ($service) {
            return $this->actions[$service] ?? [];
        }
        return $this->actions;
    }

    /**
     * 获取所有的注解
     * @return array
     * @author Verdient。
     */
    public function annotations(): array
    {
        return $this->annotations;
    }

    /**
     * 获取所有受权限控制的动作
     * @param string $service 服务名称
     * @return array
     * @author Verdient。
     */
    public function privilegeActions($service = null): array
    {
        if ($service) {
            return $this->privilegeActions[$service] ?? [];
        }
        return $this->privilegeActions;
    }

    /**
     * 获取所有不受权限控制的动作
     * @param string $service 服务名称
     * @return array
     * @author Verdient。
     */
    public function skipPrivilegeActions($service = null): array
    {
        if ($service) {
            return $this->skipPrivilegeActions[$service] ?? [];
        }
        return $this->skipPrivilegeActions;
    }

    /**
     * 是否需要登录
     * @param string $class 类
     * @param string $method 方法
     * @return bool
     * @author Verdient。
     */
    public function needAuthtication($class, $method): bool
    {
        return isset($this->annotations[$class]) && isset($this->annotations[$class][$method]);
    }

    /**
     * 是否受权限控制
     * @param string $service 服务
     * @param string $action 动作
     * @return bool
     * @author Verdient。
     */
    public function needPrivilege($service, $action): bool
    {
        return isset($this->privilegeActions[$service]) && in_array($action, $this->privilegeActions[$service]);
    }

    /**
     * 检查认证信息
     * @param ServerRequestInterface $request 请求
     * @return bool
     * @author Verdient。
     */
    public function checkAuthentication(ServerRequestInterface $request): bool
    {
        $dispatched = $request->getAttribute(Dispatched::class);
        list($class, $method) = $dispatched->handler->callback;
        if (!$annotation = $this->getAnnotation($class, $method)) {
            $annotation = new Authentication([]);
        }
        if ($user = $this->findUser($annotation, $request)) {
            $this->login($user);
        }
        if ($this->needAuthtication($class, $method) && Identity::isGuest()) {
            return false;
        }
        return true;
    }

    /**
     * 检查权限
     * @param ServerRequestInterface $request 请求
     * @return bool
     * @author Verdient。
     */
    public function checkPrivilege(ServerRequestInterface $request): bool
    {
        $dispatched = $request->getAttribute(Dispatched::class);
        list($class, $method) = $dispatched->handler->callback;
        if ($this->needPrivilege($class, $method)) {
            if (!$identity = $this->getIdentity()) {
                return false;
            }
            return Privilege::isAllowAccess($identity, $class, $method);
        }
        return true;
    }

    /**
     * 分析处理器
     * @param Handler $handler 处理方法
     * @return array
     * @author Verdient。
     */
    protected function analyzeHandler(Handler $handler): array
    {
        $service = null;
        if (is_array($handler->callback)) {
            $service = $handler->callback[0];
            $action = $handler->callback[1];
        } elseif (is_string($handler->callback)) {
            $action = $handler->callback;
        } elseif (is_callable($handler->callback)) {
            $action = 'Closure';
        } else {
            $action = (string) $handler->callback;
        }
        return [
            'action' => $action,
            'service' => $service
        ];
    }

    /**
     * 添加服务
     * @param string $service 服务
     * @return Guard
     * @author Verdient。
     */
    protected function addService($service): Guard
    {
        if (!isset($this->services[$service])) {
            $docComment = ReflectionManager::reflectClass($service)->getDocComment();
            $this->services[$service] = [
                'service' => $service,
                'description' => $docComment ? $this->getDocBlockParser()->create($docComment)->getSummary() : null
            ];
        }
        return $this;
    }

    /**
     * 添加服务
     * @param string $service 服务
     * @param string $action 动作
     * @return Guard
     * @author Verdient。
     */
    protected function addAction($service, $action): Guard
    {
        $this->addService($service);
        if (!isset($this->actions[$service])) {
            $this->actions[$service] = [];
        }
        if (!isset($this->actions[$service][$action])) {
            $docComment = ReflectionManager::reflectMethod($service, $action)->getDocComment();
            $this->actions[$service][$action] = [
                'action' => $action,
                'description' => $docComment ? $this->getDocBlockParser()->create($docComment)->getSummary() : null
            ];
        }
        if ($methodAnnotation = $this->getClassMethodAnnotation($service, $action)) {
            $this->addPrivilegeAction($service, $action, $methodAnnotation);
        } else if ($classAnnotation = $this->getClassAnnotation($service)) {
            $this->addPrivilegeAction($service, $action, $classAnnotation);
        }
        return $this;
    }

    /**
     * 添加权限动作
     * @param string $service 服务
     * @param string $action 动作
     * @param Authentication $annotation 注解
     * @return Guard
     * @author Verdient。
     */
    protected function addPrivilegeAction($service, $action, $annotation): Guard
    {
        if (!isset($this->annotations[$service])) {
            $this->annotations[$service] = [];
        }
        $this->annotations[$service][$action] = $annotation;
        if ($annotation->privilege === true) {
            if (!isset($this->privilegeActions[$service])) {
                $this->privilegeActions[$service] = [];
            }
            $this->privilegeActions[$service][] = $action;
        } else {
            if (!isset($this->skipPrivilegeActions[$service])) {
                $this->skipPrivilegeActions[$service] = [];
            }
            $this->skipPrivilegeActions[$service][] = $action;
        }
        return $this;
    }

    /**
     * 获取类中方法的注解
     * @param string $service 服务
     * @param string $action 动作
     * @return Authentication|null
     * @author Verdient。
     */
    protected function getClassMethodAnnotation($service, $action)
    {
        return AnnotationCollector::getClassMethodAnnotation($service, $action)[Authentication::class] ?? null;
    }

    /**
     * 获取类的注解
     * @param string $service 服务
     * @return Authentication|null
     * @author Verdient。
     */
    protected function getClassAnnotation($service)
    {
        return AnnotationCollector::getClassAnnotation($service, Authentication::class);
    }

    /**
     * 获取认证注解
     * @param string $class 类
     * @param string $method 方法
     * @return Authentication|null
     * @author Verdient。
     */
    protected function getAnnotation($class, $method)
    {
        return $this->annotations[$class][$method] ?? null;
    }

    /**
     * 解析令牌
     * @param Authentication $annotation 注解
     * @param ServerRequestInterface $request 请求
     * @return int|false
     * @author Verdient。
     */
    protected function parseToken($annotation, $request)
    {
        return Token::parse($this->getToken($request), $annotation->type);
    }

    /**
     * 获取令牌
     * @param ServerRequestInterface $request 请求
     * @return string
     * @author Verdient。
     */
    protected function getToken($request)
    {
        if (!$token = $request->getHeaderLine('authorization')) {
            $queryParams = $request->getQueryParams();
            return $queryParams['Authorization'] ?? ($queryParams['authorization'] ?? '');
        }
        return $token;
    }

    /**
     * 查找用户
     * @param Authentication $annotation 注解
     * @param ServerRequestInterface $request 请求
     * @return AbstractModel|false
     * @author Verdient。
     */
    protected function findUser($annotation, $request)
    {
        if ($id = $this->parseToken($annotation, $request)) {
            return $this->fetchUser($id);
        }
        return false;
    }

    /**
     * 获取用户
     * @param int $id 用户编号
     * @return AbstractModel|null
     * @author Verdient。
     */
    protected function fetchUser($id)
    {
        $class = config('identity_model');
        return $class::findFromCache($id);
    }

    /**
     * 登录
     * @param AbstractModel $user 用户对象
     * @author Verdient。
     */
    protected function login($user)
    {
        $identity = new AuthIdentity($user);
        Identity::login($identity);
    }

    /**
     * 获取认证信息
     * @return IdentityInterface|null
     * @author Verdient。
     */
    protected function getIdentity(): IdentityInterface
    {
        return Identity::get();
    }
}
