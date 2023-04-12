<?php

declare(strict_types=1);

namespace Verdient\Dora\Component;

use Exception;
use Hyperf\DbConnection\Db;

/**
 * 数据表结构管理器
 * @author Verdient。
 */
class SchemaManager
{
    /**
     * @var array 结构信息
     * @author Verdient。
     */
    protected static $schemas = [];

    /**
     * 获取表的字段
     * @param string $tableName 表名称
     * @param string $connectionName 连接名称
     * @return array
     * @author Verdient。
     */
    public static function getColumns($tableName, $connectionName = 'default'): array
    {
        if (!empty($schema = static::getSchema($tableName, $connectionName))) {
            return $schema['columns'];
        }
        return [];
    }

    /**
     * 获取表结构信息
     * @param string $tableName 表名称
     * @param string $connectionName 连接名称
     * @return array
     * @author Verdient。
     */
    public static function getSchema($tableName, $connectionName = 'default'): array
    {
        if (!isset(static::$schemas[$connectionName]) || !isset(static::$schemas[$connectionName][$tableName])) {
            $columns = [];
            $connection = Db::connection($connectionName);
            $database = call_user_func([$connection, 'getDatabaseName']);
            $rows = $connection->select('SELECT `column_name`, `column_default`, `is_nullable`, `column_type`, `column_comment` FROM `information_schema`.`columns` WHERE `table_schema` = \'' . $database . '\' AND `table_name` = \'' . $tableName . '\' ORDER BY ORDINAL_POSITION');
            if (empty($rows)) {
                throw new Exception('Table ' . $tableName . ' does not exists in ' . $database);
            }
            foreach ($rows as $row) {
                $columns[$row->column_name] = [
                    'type' => $row->column_type,
                    'default' => $row->column_default,
                    'is_nullable' => $row->is_nullable,
                    'comment' => $row->column_comment
                ];
            }
            if (!isset(static::$schemas[$connectionName])) {
                static::$schemas[$connectionName] = [];
            }
            static::$schemas[$connectionName][$tableName] = [
                'columns' => $columns
            ];
        }
        return static::$schemas[$connectionName][$tableName];
    }
}
