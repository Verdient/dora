<?php

declare(strict_types=1);

namespace Verdient\Dora\Validation\Validators;

use Hyperf\Utils\Arr;
use Verdient\Dora\Annotation\Validator;
use Verdient\Dora\HttpServer\Auth\GuardFactory;
use Verdient\Dora\Utils\Container;

/**
 * 权限规则校验器
 * @Validator
 * @author Verdient。
 */
class PrivilegeRuleValidator extends AbstractValidator
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function validators(): array
    {
        return [
            'privilege_rule' => 'privilegeRule'
        ];
    }

    /**
     * 校验是否是合法的权限规则
     * @param string $value 值
     * @return bool
     * @author Verdient。
     */
    public static function privilegeRule($attribute, $value, $parameters, $validator)
    {
        if (!is_array($value)) {
            return false;
        }
        $actions = [];
        $guardFactory = Container::get(GuardFactory::class);
        $guard = $guardFactory->getGuard('http');
        foreach ($guard->services() as $service) {
            foreach ($guard->actions($service['service']) as $action) {
                $actions[$service['service']][] = $action['action'];
            }
        }
        if (Arr::isAssoc($value)) {
            $value = [$value];
        }
        if (count(array_unique($value, SORT_REGULAR)) != count($value)) {
            return false;
        }
        foreach ($value as $rule) {
            if (count($rule) !== 3) {
                return false;
            }
            if (!isset($rule['allow']) || !isset($rule['service']) || !isset($rule['action'])) {
                return false;
            }
            if (!is_bool($rule['allow'])) {
                return false;
            }
            if ($rule['service'] !== '*' && !isset($actions[$rule['service']])) {
                return false;
            }
            if ($rule['action'] !== '*' && !in_array($rule['action'], $actions[$rule['service']])) {
                return false;
            }
        }
        return true;
    }
}
