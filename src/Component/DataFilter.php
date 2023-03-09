<?php

declare(strict_types=1);

namespace Verdient\Dora\Component;

use Verdient\Dora\Model\Builder;

/**
 * 数据过滤器
 * @author Verdient。
 */
class DataFilter
{
    /**
     * @var array $query 查询参数
     * @author Verdient。
     */
    protected $querys;

    /**
     * @var array 规则
     * @author Verdient。
     */
    protected $rules = [];

    /**
     * @var array 关联规则
     * @author Verdient。
     */
    protected $hasRules = [];

    /**
     * @var array 连接规则
     * @author Verdient。
     */
    protected $joinRules = [];

    /**
     * @var array 无关联规则
     * @author Verdient。
     */
    protected $doesntHaveRules = [];

    /**
     * @var array 存在规则
     * @author Verdient。
     */
    protected $existRules = [];

    /**
     * @var bool 是否是导出
     * @author Verdient。
     */
    protected $isExport = false;

    /**
     * @var int 周期
     * @author Verdient。
     */
    protected $period = null;

    /**
     * @var Callable 构建器
     * @author Verdient。
     */
    protected $builder = null;

    /**
     * 构造函数
     * @author Verdient。
     */
    public function __construct(array $querys)
    {
        $this->querys = $querys;
    }

    /**
     * 设置导出
     * @param bool $export 是否是导出
     * @author Verdient。
     */
    public function setExport($export = true)
    {
        $this->isExport = $export;
        $this->period = floor(time() / 60);
        return $this;
    }

    /**
     * 获取检索条件
     * @return array
     * @author Verdient。
     */
    public function getQuerys(): array
    {
        return $this->querys;
    }

    /**
     * 根据名称获取检索条件
     * @return mixed
     * @author Verdient。
     */
    public function getQuery($name)
    {
        return $this->querys[$name] ?? false;
    }

    /**
     * 删除一条检索条件
     * @return mixed
     * @author Berlin
     */
    public function delQuery($name)
    {
        if (isset($this->querys[$name])) {
            unset($this->querys[$name]);
        }
        return $this;
    }

    /**
     * 添加规则
     * @param string $name 参数名称
     * @param string $operator 操作符
     * @param string|array $field 字段
     * @param bool $skipEmpty 为空时是否跳过
     * @param string $boolean 规则间的关系
     * @return static
     * @author Verdient。
     */
    public function addRule($name, $operator = '=', $field = null, $skipEmpty = true, $boolean = 'and')
    {
        $this->rules[] = [
            'name' => $name,
            'field' => $field,
            'operator' => $operator,
            'skipEmpty' => $skipEmpty,
            'boolean' => $boolean
        ];
        return $this;
    }

    /**
     * 添加无关联规则
     * @param string $relation 关联关系
     * @param string $name 参数名称
     * @param string $operator 操作符
     * @param string $field 字段
     * @param bool $skipEmpty 为空时是否跳过
     * @param string $boolean 规则间的关系
     * @return static
     * @author Verdient。
     */
    public function addDoesntHaveRule($relation, $name,  $operator = '=', $field = null, $skipEmpty = true, $boolean = 'and')
    {
        $this->doesntHaveRules[] = [
            'relation' => $relation,
            'name' => $name,
            'operator' => $operator,
            'field' => $field,
            'skipEmpty' => $skipEmpty,
            'boolean' => $boolean
        ];
        return $this;
    }

    /**
     * 添加存在规则
     * @param string $relation 关联关系
     * @param string $name 参数名称
     * @param string $operator 操作符
     * @param string $field 字段
     * @param bool $skipEmpty 为空时是否跳过
     * @param string $boolean 规则间的关系
     * @return static
     * @author Verdient。
     */
    public function addHasRule($relation, $name, $operator = '=', $field = null, $skipEmpty = true, $boolean = 'and')
    {
        $this->hasRules[] = [
            'relation' => $relation,
            'name' => $name,
            'operator' => $operator,
            'field' => $field,
            'skipEmpty' => $skipEmpty,
            'boolean' => $boolean
        ];
        return $this;
    }

    /**
     * 添加存在规则
     * @param string $relation 关联关系
     * @param string $name 参数名称
     * @param bool $skipEmpty 为空时是否跳过
     * @param string $boolean 规则间的关系
     * @return static
     * @author Verdient。
     */
    public function addExistRule($relation, $name, $skipEmpty = true, $boolean = 'and')
    {
        $this->existRules[] = [
            'relation' => $relation,
            'name' => $name,
            'skipEmpty' => $skipEmpty,
            'boolean' => $boolean
        ];
        return $this;
    }

    /**
     * 添加连接规则
     * @param string $relation 关联关系
     * @param string $name 参数名称
     * @param string $operator 操作符
     * @param string $field 字段
     * @param bool $skipEmpty 为空时是否跳过
     * @param string $boolean 规则间的关系
     * @return static
     * @author Verdient。
     */
    public function addJoinRule($relation, $name, $operator = '=', $field = null, $skipEmpty = true, $boolean = 'and')
    {
        $this->joinRules[] = [
            'relation' => $relation,
            'name' => $name,
            'operator' => $operator,
            'field' => $field,
            'skipEmpty' => $skipEmpty,
            'boolean' => $boolean
        ];
        return $this;
    }

    /**
     * 获取活跃的规则
     * @return array
     * @author Verdient。
     */
    public function getActiveRules()
    {
        $rules = [];
        foreach ($this->rules as $rule) {
            $value = $this->getQuery($rule['name']);
            if ($value !== false) {
                if ($value !== '' || $rule['skipEmpty'] === false) {
                    $rule['value'] = $value;
                    if ($rule['field'] === null) {
                        $rule['field'] = $rule['name'];
                    }
                    $rules[] = $rule;
                }
            }
        }
        return $rules;
    }

    /**
     * 获取活跃的存在规则
     * @return array
     * @author Verdient。
     */
    public function getActiveHasRules()
    {
        return $this->getActiveRelationRules('hasRules');
    }

    /**
     * 获取活跃的不存在规则
     * @return array
     * @author Verdient。
     */
    public function getActiveDoesntHaveRules()
    {
        return $this->getActiveRelationRules('doesntHaveRules');
    }

    /**
     * 获取活跃的关联规格
     * @param string $type 关联类型
     * @return array
     * @author Verdient。
     */
    protected function getActiveRelationRules($type)
    {
        $rules = [];
        foreach ($this->$type as $rule) {
            $value = $this->getQuery($rule['name']);
            if ($value !== false) {
                if ($value !== '' || $rule['skipEmpty'] === false) {
                    if (!isset($rules[$rule['relation']])) {
                        $rules[$rule['relation']] = [];
                    }
                    $rule['value'] = $value;
                    if ($rule['field'] === null) {
                        $rule['field'] = $rule['name'];
                    }
                    $rules[$rule['relation']][] = $rule;
                }
            }
        }
        return $rules;
    }

    /**
     * 获取活跃的存在规则
     * @return array
     * @author Verdient。
     */
    public function getActiveExistRules()
    {
        $rules = [];
        foreach ($this->existRules as $rule) {
            $value = $this->getQuery($rule['name']);
            if ($value !== false) {
                if ($value !== '' || $rule['skipEmpty'] === false) {
                    $rule['value'] = $value;
                    $rules[] = $rule;
                }
            }
        }
        return $rules;
    }

    /**
     * 构建
     * @param Builder 构建器
     * @return Builder
     * @author Verdient。
     */
    public function build(Builder $builder): Builder
    {
        if (is_callable($this->builder)) {
            return call_user_func($this->builder, $builder, $this);
        } else {
            return $this->buildIt($builder);
        }
    }

    /**
     * 自带的构建方法
     * @param Builder 构建器
     * @return Builder
     * @author Verdient。
     */
    public function buildIt(Builder $builder)
    {
        foreach ($this->getActiveRules() as $rule) {
            $this->buildWhere($builder, $rule);
        }
        foreach ([
            'hasRules' => 'whereHas',
            'doesntHaveRules' => 'whereDoesntHave'
        ] as $type => $method) {
            foreach ($this->getActiveRelationRules($type) as $relation => $rules) {
                call_user_func([$builder, $method], $relation, function ($builder2) use ($rules) {
                    foreach ($rules as $rule) {
                        $this->buildWhere($builder2, $rule);
                    }
                });
            }
        }
        foreach ($this->getActiveExistRules() as $rule) {
            if ($this->isTrue($rule['value'])) {
                $builder->has($rule['relation'], '>=', 1, $rule['boolean']);
            } else if ($this->isFalse($rule['value'])) {
                $builder->doesntHave($rule['relation'], $rule['boolean']);
            }
        }
        $joinRules = $this->getActiveRelationRules('joinRules');
        if (!empty($joinRules)) {
            $builder->groupBy($builder->getModel()->getKeyName());
            foreach ($joinRules as $relation => $rules) {
                $table = $builder->getRelationDefinition($relation)->getRelated()->getTable();
                $builder->joinWith($relation);
                foreach ($rules as $rule) {
                    if (is_array($rule['field'])) {
                        for ($index = 1; $index < count($rule['field']); $index++) {
                            $field = $rule['field'][$index];
                            if (strpos($field, '.') === false) {
                                $rule['field'][$index] = $table . '.' . $field;
                            }
                        }
                    } else if (strpos($rule['field'], '.') === false) {
                        $rule['field'] = $table . '.' . $rule['field'];
                    }
                    $this->buildWhere($builder, $rule, true);
                }
            }
        }
        return $builder;
    }

    /**
     * 获取唯一哈希值
     * @return string
     * @author Verdient。
     */
    public function getHash(): string
    {
        return hash('SHA256', serialize($this));
    }

    /**
     * 设置构造器
     * @return static
     * @author Verdient。
     */
    public function setBuilder(callable $builder)
    {
        $this->builder = $builder;
        return $this;
    }

    /**
     * 构建检索条件
     * @param Builder 构建器
     * @param array $rule 规则
     * @param bool $isJoin 是否是连接条件
     * @author Verdient。
     */
    protected function buildWhere(Builder $builder, $rule)
    {
        if (!is_array($rule['field'])) {
            $field = $rule['field'];
            switch ($rule['operator']) {
                case 'isNotNull':
                    $builder->whereNull($field, $rule['boolean'], $this->isTrue($rule['value']));
                    break;
                case 'isNull':
                    $builder->whereNull($field, $rule['boolean'], $this->isFalse($rule['value']));
                    break;
                case 'like':
                    $builder->where($field, 'like', '%' . $rule['value'] . '%', $rule['boolean']);
                    break;
                case 'in':
                    if (!is_array($rule['value'])) {
                        $rule['value'] = [$rule['value']];
                    }
                    $builder->whereIn($field, $rule['value'], $rule['boolean']);
                    break;
                default:
                    $builder->where($field, $rule['operator'], $rule['value'], $rule['boolean']);
                    break;
            }
        } else {
            $fields = $rule['field'];
            $boolean = array_shift($fields);
            $builder->where(function ($query) use ($fields, $rule, $boolean) {
                switch ($rule['operator']) {
                    case 'isNotNull':
                        foreach ($fields as $field) {
                            $query->whereNull($field, $boolean, $this->isTrue($rule['value']));
                        }
                        break;
                    case 'isNull':
                        foreach ($fields as $field) {
                            $query->whereNull($field, $boolean, $this->isFalse($rule['value']));
                        }
                        break;
                    case 'like':
                        foreach ($fields as $field) {
                            $query->where($field, 'like', '%' . $rule['value'] . '%', $boolean);
                        }
                        break;
                    case 'in':
                        if (!is_array($rule['value'])) {
                            $rule['value'] = [$rule['value']];
                        }
                        foreach ($fields as $field) {
                            $query->whereIn($field, $rule['value'], $boolean);
                        }
                        break;
                    default:
                        foreach ($fields as $field) {
                            $query->where($field, $rule['operator'], $rule['value'], $boolean);
                        }
                        break;
                }
            }, null, null, $rule['boolean']);
        }
    }

    /**
     * 判断是否为真
     * @param mixed $value 待判断的值
     * @return bool
     * @author Verdient。
     */
    protected function isTrue($value)
    {
        return $value === true || $value === 1 || $value === '1';
    }

    /**
     * 判断是否为假
     * @param mixed $value 待判断的值
     * @return bool
     * @author Verdient。
     */
    protected function isFalse($value)
    {
        return $value === false || $value === 0 || $value === '0';
    }
}
