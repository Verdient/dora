<?php

declare(strict_types=1);

namespace Verdient\Dora\Model;

use Closure;
use Exception;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\HasOneOrMany;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\Utils\Contracts\Arrayable;
use Iterator;

/**
 * @inheritdoc
 * @method static withTrashed(bool $withTrashed = true)
 * @method static onlyTrashed()
 * @method static withoutTrashed()
 * @method bool exists()
 * @method static select(array|mixed $columns = ['*'])
 * @method static whereNotNull(string $column, string $boolean = 'and')
 * @method static lockForUpdate()
 * @method static inRandomOrder(string $seed = '')
 * @method static limit(int $value)
 * @method static whereNotIn(string $column, string $values, $boolean = 'and')
 * @method static orderByDesc(string $column)
 * @method static take(int $value)
 * @method static whereColumn(array|string $first, null|string $operator = null, null|string string $second = null, string $boolean = 'and')
 * @method static whereExists(Closure $callback, $boolean = 'and', $not = false)
 * @method static whereNotExists(Closure $callback, $boolean = 'and')
 * @method static whereBetween(string $column, array $values, string $boolean = 'and', bool $not = false)
 * @method static whereNotBetween(string $column, array $values, string $boolean = 'and')
 * @method static whereJsonContains(string $column, mixed $value, string $boolean = 'and', bool $not = false)
 * @method static whereJsonDoesntContain(string $column, mixed $value, string $boolean = 'and')
 * @method static orWhereJsonContains(string $column, mixed $value)
 * @method static orWhereJsonDoesntContain(string $column, mixed $value)
 * @method static whereJsonLength(string $column, mixed $operator, null|mixed $value = null, string $boolean = 'and')
 * @method static orWhereJsonLength(string $column, mixed $operator, null|mixed $value = null)
 * @method static whereRaw(string $sql, mixed $bindings = [], string $boolean = 'and')
 * @method static whereRowValues(array $columns, string $operator, array $values, string $boolean = 'and')
 * @method static whereInSub(string $column, Closure $callback, string $boolean, bool $not)
 * @method static whereInExistingQuery(string $column, \Hyperf\Database\Query\Builder|static $query, string $boolean, bool $not)
 * @method static whereSub(string $column, string $operator, Closure $callback, string $boolean)
 * @method static having(string $column, null|string $operator = null, null|string $value = null, string $boolean = 'and')
 * @method int count(string $columns = '*')
 * @method int min(string $column)
 * @method int max(string $column)
 * @method int sum(string $column)
 * @method int avg(string $column)
 * @method int average(string $column)
 * @author Verdient。
 */
class Builder extends \Hyperf\Database\Model\Builder
{
    /**
     * @var array 已连接的表
     * @author Verdient。
     */
    protected $joinedTables = [];

    /**
     * @var array 关联定义
     * @author Verdient。
     */
    protected $relationDefinitions = [];

    /**
     * 获取关联的定义
     * @param string $name 关联名称
     * @return Relation
     * @author Verdient。
     */
    public function getRelationDefinition($name)
    {
        if (!isset($this->relationDefinitions[$name])) {
            if (!method_exists($this->model, $name)) {
                throw new Exception('Relation ' . $name . ' does not exists in ' . get_class($this->model));
            }
            $relation = call_user_func([$this->model, $name]);
            if (!$relation instanceof Relation) {
                throw new Exception('Relation ' . $name . ' does not exists in ' . get_class($this->model));
            }
            $this->relationDefinitions[$name] = $relation;
        }
        return $this->relationDefinitions[$name];
    }

    /**
     * @inheritdoc
     * @return Collection
     * @author Verdient。
     */
    public function get($columns = ['*'])
    {
        return parent::get($this->supplementTableName($columns));
    }

    /**
     * @inheritdoc
     * @return AbstractModel
     * @author Verdient。
     */
    public function first($columns = ['*'])
    {
        return parent::first($this->supplementTableName($columns));
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function pluck($column, $key = null)
    {
        return parent::pluck($this->supplementTableName($column, $key));
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function paginate(?int $perPage = null, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginatorInterface
    {
        return parent::paginate($perPage, $this->supplementTableName($columns), $pageName, $page);
    }

    /**
     * In条件
     * @param string $column 字段
     * @param array $values 值
     * @param string $boolean 连接关系
     * @param bool $not 是否是NOT
     * @return static
     * @author Verdient。
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }
        $column = $this->supplementTableName($column);
        if (count($values) === 1) {
            $operator = $not ? '!=' : '=';
            parent::where($column, $operator, reset($values), $boolean);
        } else {
            parent::whereIn($column, $values, $boolean, $not);
        }
        return $this;
    }

    /**
     * @inheritdoc
     * @return static
     * @author Verdient。
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (is_scalar($column)) {
            $column = $this->supplementTableName($column);
        }
        if ($operator !== null) {
            $operator = (string) $operator;
        }
        parent::where($column, $operator, $value, $boolean);
        return $this;
    }

    /**
     * Null条件
     * @param array|string $columns 字段
     * @param string $boolean 连接关系
     * @param bool $not 是否是NOT
     * @return static
     * @author Verdient。
     */
    public function whereNull($columns, $boolean = 'and', $not = false)
    {
        return parent::whereNull($this->supplementTableName($columns), $boolean, $not);
        return $this;
    }

    /**
     * 连接查询
     * @param string $table
     * @param \Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @param string $type
     * @param bool $where
     * @return $this
     * @author Verdient。
     */
    public function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        if (!in_array($table, $this->joinedTables)) {
            $this->joinedTables[] = $table;
            parent::join($table, $first, $operator, $second, $type, $where);
        }
        return $this;
    }

    /**
     * 连接查询
     * @param string $table 表
     * @param string $type 类型
     * @return static
     * @author Verdient。
     */
    public function joinWith($relationName, $type = 'inner')
    {
        $relation = $this->getRelationDefinition($relationName);
        $relationClass = get_class($relation);
        if ($relation instanceof HasOneOrMany) {
            $relatedClass = $relation->getRelated();
            $table1 = $this->model->getTable();
            $table2 = $relatedClass->getTable();
            $first = $table1 . '.' . $relation->getLocalKeyName();
            $second = $table2 . '.' . $relation->getForeignKeyName();
        } else if ($relation instanceof BelongsTo) {
            $relatedClass = $relation->getRelated();
            $table1 = $this->model->getTable();
            $table2 = $relatedClass->getTable();
            $first = $table1 . '.' . $relation->getForeignKeyName();
            $second = $table2 . '.' . $relation->getOwnerKeyName();
        } else {
            throw new Exception('Unsupported relation ' . $relationClass);
        }
        static::join($table2, $first, '=', $second, $type, false);
        if (method_exists($relatedClass, 'bootSoftDeletes')) {
            $this->whereNull($relatedClass->getQualifiedDeletedAtColumn());
        }
        return $this;
    }

    /**
     * 左连接
     * @param string $relationName 关联名称
     * @return static
     * @author Verdient。
     */
    public function leftJoinWith($relationName)
    {
        return $this->joinWith($relationName, 'left');
    }

    /**
     * 连接查询条件
     * @param string $relationName 关联名称
     * @param string $column 字段名称
     * @param string $operator 操作符
     * @param string $value 值
     * @param string $boolean 关系
     * @return static
     * @author Verdient。
     */
    public function whereJoin($relationName, $column, $operator = null, $value = null, $boolean = 'and')
    {
        $relation = $this->getRelationDefinition($relationName);
        $this->where($relation->getRelated()->getTable() . '.' . $column, $operator, $value, $boolean);
        return $this;
    }

    /**
     * 分组
     * @return static
     * @author Verdient。
     */
    public function groupBy(...$groups)
    {
        $groups = $this->supplementTableName($groups);
        parent::groupBy(...$groups);
        return $this;
    }

    /**
     * 排序
     * @param string $column 字段名称
     * @param string $direction
     * @return static
     * @author Verdient。
     */
    public function orderBy($column, $direction = 'desc')
    {
        $column = $this->supplementTableName($column);
        parent::orderBy($column, $direction);
        return $this;
    }

    /**
     * 约束查询到给定ID后的下一页
     * @param int $perPage
     * @param null|int $lastId
     * @param string $column
     * @return static
     * @author Verdient。
     */
    public function forPageAfterId($perPage = 15, $lastId = 0, $column = 'id')
    {
        $column = $this->supplementTableName($column);
        parent::forPageAfterId($perPage, $lastId, $column);
        return $this;
    }

    /**
     * 补充表名称
     * @param array|string 字段名称
     * @return array|string
     * @author Verdient。
     */
    protected function supplementTableName($field)
    {
        if (is_string($field)) {
            return $this->getModel()->qualifyColumn($field);
        }
        if (is_array($field)) {
            return array_map(function ($column) {
                return $this->supplementTableName($column);
            }, $field);
        }
        return $field;
    }

    /**
     * 处理表名称
     * @author Verdient。
     */
    protected function handleTableName()
    {
        if (!empty($this->query->joins)) {
            $query = $this->query;
            if (is_array($query->wheres)) {
                foreach ($query->wheres as $key => $where) {
                    if (isset($where['column'])) {
                        $query->wheres[$key]['column'] = $this->supplementTableName($where['column']);
                    }
                }
            }
            if (is_array($query->orders)) {
                foreach ($query->orders as $key => $order) {
                    $query->orders[$key]['column'] = $this->supplementTableName($order['column']);
                }
            }
            if (is_array($query->groups)) {
                foreach ($query->groups as $key => $group) {
                    $query->groups[$key] = $this->supplementTableName($group);
                }
            }
        }
    }

    /**
     * 游标
     * @return Iterator
     * @author Verdient。
     */
    public function cursor()
    {
        foreach ($this->applyScopes()->query->select($this->supplementTableName('*'))->cursor() as $record) {
            yield $this->model->newFromBuilder($record);
        }
    }

    /**
     * 批量迭代
     * @param int $size 分批大小
     * @return Iterator
     * @author Verdient。
     */
    public function batch(int $size = 500): Iterator
    {
        $rows = [];
        $i = 0;
        foreach ($this->cursor() as $row) {
            $i++;
            $rows[] = $row;
            if ($i === $size) {
                yield $rows;
                $i = 0;
                $rows = [];
            }
        }
        yield $rows;
    }
}
