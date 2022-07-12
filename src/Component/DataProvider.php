<?php

declare(strict_types=1);

namespace Verdient\Dora\Component;

use Hyperf\Contract\LengthAwarePaginatorInterface;
use Verdient\Dora\Model\AbstractModel;
use Verdient\Dora\Model\Builder;

/**
 * 数据提供器
 * @author Verdient。
 */
class DataProvider
{
    /**
     * @var Builder 构建器
     * @author Verdient。
     */
    protected $builder;

    /**
     * @var bool 是否已构建
     * @author Verdient。
     */
    protected $isBuilt = false;

    /**
     * @var DataFilter 过滤器
     * @author Verdient。
     */
    protected $filter;

    /**
     * @var Callable 序列化器
     * @author Verdient。
     */
    protected $serializer = null;

    /**
     * @var array 字段
     * @author Verdient。
     */
    protected $columns = ['*'];

    /**
     * @var array 排序
     * @author Verdient。
     */
    protected $sorts = [];

    /**
     * @var array 需要标签转译的字段
     * @author Verdient。
     */
    protected $labels = [];

    /**
     * @var array 别名
     * @author Verdient。
     */
    protected $alias = [];

    /**
     * @var
     * @author Verdient。
     */
    protected $paginator = null;

    /**
     * @var string 页码字段名称
     * @author Verdient。
     */
    protected $pageName = 'page';

    /**
     * @var string 分页大小字段名称
     * @author Verdient。
     */
    protected $pageSizeName = 'page_size';

    /**
     * @var int 默认页码
     * @author Verdinent。
     */
    protected $defaultPage = 1;

    /**
     * @var string 默认分页大小
     * @author Verdinent。
     */
    protected $defaultPageSize = 50;

    /**
     * 构造函数
     * @author Verdient。
     */
    public function __construct(Builder $builder, DataFilter $filter = null)
    {
        $this->builder = $builder;
        if ($filter === null) {
            $filter = new DataFilter([]);
        }
        $this->filter = $filter;
    }

    /**
     * 设置序列化器
     * @param Callable $serializer 序列化器
     * @return static
     * @author Verdient。
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
        return $this;
    }

    /**
     * 设置字段
     * @param array $columns 字段
     * @return static
     * @author Verdient。
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * 设置需要标签的字段
     * @param array $columns 字段
     * @return static
     * @author Verdient。
     */
    public function setLabels($columns)
    {
        $this->labels = $columns;
        return $this;
    }

    /**
     * 设置默认页码
     * @param int $page 页码
     * @return static
     * @author Vertdient。
     */
    public function setDefaultPage($page)
    {
        $this->defaultPage = $page;
        return $this;
    }

    /**
     * 设置默认分页大小
     * @param int $pageSize 分页大小
     * @return static
     * @author Vertdient。
     */
    public function setDefaultPageSize($pageSize)
    {
        $this->defaultPageSize = $pageSize;
        return $this;
    }

    /**
     * 添加排序
     * @param string $column 字段
     * @param string $sort 排序方式
     * @return static
     * @author Verdient。
     */
    public function addSort($column, $sort = 'desc')
    {
        $this->sorts[] = [$column, $sort];
        return $this;
    }


    /**
     * 设置页码字段名称
     * @param array $name 名称
     * @return static
     * @author Verdient。
     */
    public function setPageName($name)
    {
        $this->pageName = $name;
        return $this;
    }

    /**
     * 设置分页大小字段名称
     * @param array $name 名称
     * @return static
     * @author Verdient。
     */
    public function setPageSizeName($name)
    {
        $this->pageSizeName = $name;
        return $this;
    }

    /**
     * 设置字段别名
     * @param array $alias 别名
     * @return static
     * @author Vertdient。
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * 获取序列化器
     * @return Callable|null
     * @author Verdient。
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * 获取构建器
     * @return Builder
     * @author Verdient。
     */
    public function getBuilder(): Builder
    {
        if ($this->isBuilt) {
            return $this->builder;
        }
        foreach ($this->getSorts() as $sort) {
            $this->builder->orderBy($sort[0], $sort[1]);
        }
        return $this->filter->build($this->builder);
    }

    /**
     * 获取字段
     * @return array
     * @author Verdient。
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * 获取需要标签的字段
     * @return array
     * @author Verdient。
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * 获取排序
     * @return array
     * @author Verdient。
     */
    public function getSorts(): array
    {
        return $this->sorts;
    }

    /**
     * 获取别名
     * @return array
     * @author Verdient。
     */
    public function getAlias(): array
    {
        return $this->alias;
    }

    /**
     * 获取页码
     * @return int
     * @author Verdient。
     */
    public function getPage(): int
    {
        return (int) $this->filter->getQuery($this->pageName) ?: $this->defaultPage;
    }

    /**
     * 获取分页大小
     * @return int
     * @author Verdient。
     */
    public function getPageSize(): int
    {
        return (int) $this->filter->getQuery($this->pageSizeName) ?: $this->defaultPageSize;
    }

    /**
     * 获取分页器
     * @return LengthAwarePaginatorInterface
     * @author Verdient。
     */
    public function getPaginator(): LengthAwarePaginatorInterface
    {
        if (!$this->paginator) {
            $builder = $this->getBuilder();
            $this->paginator = $builder->paginate($this->getPageSize(), $this->getColumns(), '', $this->getPage());
        }
        return $this->paginator;
    }

    /**
     * 获取条目
     * @return array
     * @author Verdient。
     */
    public function getRows(): array
    {
        $paginator = $this->getPaginator();
        $rows = $paginator->items();
        if (is_callable($this->serializer)) {
            $rows = call_user_func($this->serializer, $rows);
        }
        if (is_array($rows)) {
            foreach ($rows as &$row) {
                if ($row instanceof AbstractModel) {
                    $data = $row->toArray();
                    if (!empty($this->labels)) {
                        foreach ($this->labels as $name) {
                            $name = $name . '_label';
                            $data[$name] = $row->$name;
                        }
                    }
                    $row = $data;
                }
                if (is_array($this->alias)) {
                    $alias = $this->alias;
                    $row = array_combine(array_map(function ($key) use ($alias) {
                        return $alias[$key] ?? $key;
                    }, array_keys($row)), array_values($row));
                }
            }
            return $rows;
        }
        return [];
    }

    /**
     * 获取总数
     * @return int
     * @author Verdient。
     */
    public function getCount(): int
    {
        return $this->getPaginator()->total();
    }

    /**
     * 获取最后的页码
     * @return int
     * @author Verdient。
     */
    public function getLastPage()
    {
        return $this->getPaginator()->lastPage();
    }
}
