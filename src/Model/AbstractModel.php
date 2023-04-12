<?php

declare(strict_types=1);

namespace Verdient\Dora\Model;

use Exception;
use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Model\Model;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;
use Hyperf\Snowflake\Concern\Snowflake;
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Utils\Str;
use Verdient\Dora\Component\ModelCastManager;
use Verdient\Dora\Component\SchemaManager;
use Verdient\Dora\Model\Builder as ModelBuilder;
use Verdient\Dora\Traits\HasEvent;
use Verdient\Dora\Utils\Container;
use Verdient\Dora\Utils\Math;

/**
 * 抽象模型
 * @author Verdient。
 */
abstract class AbstractModel extends Model implements CacheableInterface
{
    use Cacheable;
    use HasEvent;

    /**
     * @inheritdoc
     * @var string 日期格式
     * @author Verdient。
     */
    protected $dateFormat = 'U';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __isset($key)
    {
        if (!parent::__isset($key)) {
            if (substr($key, -6) === '_label') {
                $getter = Str::camel($key);
                return method_exists($this, $getter);
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getTable(): string
    {
        if (!$this->table) {
            $this->table = Str::snake(class_basename($this));
        }
        return $this->table;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getCasts(): array
    {
        $casts = parent::getCasts();
        return array_merge(ModelCastManager::get($this->getTable(), $this->getConnectionName()), $casts);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __get($key)
    {
        if (substr($key, -6) === '_label') {
            $getter = Str::camel($key);
            if (method_exists($this, $getter)) {
                return call_user_func([$this, $getter]);
            }
        }
        return parent::__get($key);
    }

    /**
     * 获取主键或生成主键
     * @return int
     * @author Verdient。
     */
    public function getKeyOrGenerate()
    {
        if (!$this->getKey()) {
            $this->{$this->getKeyName()} = $this->generateKey();
        }
        return $this->getKey();
    }

    /**
     * 生成主键
     * @return int
     * @author Verdient。
     */
    public static function generateKey()
    {
        $generator = Container::get(IdGeneratorInterface::class);
        return $generator->generate();
    }

    /**
     * 复制一个新对象
     * @return static
     * @author Verdient。
     */
    public function copy()
    {
        $model = new static;
        $attributes = $this->getAttributes();
        if ($keyName = $this->getKeyName()) {
            unset($attributes[$keyName]);
        }
        unset($attributes[static::CREATED_AT], $attributes[static::UPDATED_AT]);
        $model->setRawAttributes($attributes);
        return $model;
    }

    /**
     * 获取模型数据
     * @param array $attributes 属性名称
     * @param array $alias 别名
     * @author Verdient。
     */
    public function data($attributes = [], $alias = [])
    {
        $result = [];
        $data = $this->toArray();
        if (!empty($attributes)) {
            foreach ($data as $name => $value) {
                if (!in_array($name, $attributes)) {
                    unset($data[$name]);
                }
            }
        } else {
            $attributes = array_keys($data);
        }
        foreach ($attributes as $attribute) {
            $key = $alias[$attribute] ?? $attribute;
            if (array_key_exists($attribute, $data)) {
                $result[$key] = $data[$attribute];
            } else {
                $getter = Str::camel($attribute);
                if (method_exists($this, $getter)) {
                    $result[$key] = call_user_func([$this, $getter]);
                } else {
                    throw new Exception('Unknown attribute ' . $attribute);
                }
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     * @author Verdient.
     */
    protected function castAttribute($key, $value)
    {
        if (is_numeric($value)) {
            $castType = $this->getCastType($key);
            if ($castType === 'int' || $castType === 'integer') {
                $value = Math::add($value, 0, 0);
            }
        }
        return parent::castAttribute($key, $value);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function performInsert(Builder $query)
    {
        $attributes = $this->attributes;
        $columns = SchemaManager::getColumns($this->getTable(), $this->getConnectionName());
        foreach ($this->attributes as $name => $value) {
            if (!isset($columns[$name])) {
                unset($this->attributes[$name]);
            }
        }
        $result = parent::performInsert($query);
        foreach ($attributes as $name => $value) {
            if (!array_key_exists($name, $this->attributes)) {
                $this->attributes[$name] = $value;
            }
        }
        return $result;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function performUpdate(Builder $query)
    {
        $attributes = $this->attributes;
        $columns = SchemaManager::getColumns($this->getTable(), $this->getConnectionName());
        foreach ($this->attributes as $name => $value) {
            if (!isset($columns[$name])) {
                unset($this->attributes[$name]);
            }
        }
        $result = parent::performUpdate($query);
        foreach ($attributes as $name => $value) {
            if (!array_key_exists($name, $this->attributes)) {
                $this->attributes[$name] = $value;
            }
        }
        return $result;
    }

    /**
     * @inheritdoc
     * @return ModelBuilder
     * @author Verdient。
     */
    public function newModelBuilder($query)
    {
        return new ModelBuilder($query);
    }

    /**
     * @inheritdoc
     * @return ModelBuilder
     * @author Verdient。
     */
    public static function query(bool $cache = false): ModelBuilder
    {
        return parent::query($cache);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getFillable(): array
    {
        if (empty($this->fillable)) {
            return array_keys(SchemaManager::getColumns($this->getTable(), $this->getConnectionName()));
        }
        return $this->fillable;
    }
}
