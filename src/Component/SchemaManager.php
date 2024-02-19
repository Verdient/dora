<?php

declare(strict_types=1);

namespace Verdient\Dora\Component;

use Exception;
use Hyperf\DbConnection\Db;
use PDO;

/**
 * 数据表结构管理器
 * @author Verdient。
 */
class SchemaManager
{
    /**
     * 结构信息
     * @author Verdient。
     */
    protected static $schemas = [];

    /**
     * MySQL版本
     * @author Verdient。
     */
    protected static $versions = [];

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
     * 是否使用大写
     * @param string $connectionName 连接名称
     * @return bool
     * @author Verdient。
     */
    protected static function isUpperCase($connectionName)
    {
        $version = static::getVersion($connectionName);
        $majorVersion = (int) explode('.', $version)[0];
        return $majorVersion > 5;
    }

    /**
     * 获取版本号
     * @param string $connectionName 连接名称
     * @return string
     * @author Verdient。
     */
    protected static function getVersion($connectionName)
    {
        if (!array_key_exists($connectionName, static::$versions)) {
            $connection = Db::connection($connectionName);
            $pdo = call_user_func([$connection, 'getReadPdo']);
            $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
            static::$versions[$connectionName] = $version;
        }
        return static::$versions[$connectionName];
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
            $schemaColumns = ['column_name', 'column_default', 'is_nullable', 'column_type', 'column_comment'];
            $schemaTable = 'columns';
            $schemaConditions = [
                'table_schema' => $database,
                'table_name' => $tableName
            ];
            if (static::isUpperCase($connectionName)) {
                $schemaColumns = array_map('strtoupper', $schemaColumns);
                $schemaConditions = array_change_key_case($schemaConditions, CASE_UPPER);
                $schemaTable = 'COLUMNS';
            }
            $schemaColumns = '`' . implode('`, `', $schemaColumns) . '`';
            $schemaCondition = '';
            foreach ($schemaConditions as $conditionColumn => $conditionValue) {
                if ($schemaCondition) {
                    $schemaCondition .= ' AND ';
                }
                $schemaCondition .= '`' . $conditionColumn . '` = \'' . $conditionValue . '\'';
            }
            $sql = 'SELECT ' . $schemaColumns . ' FROM `information_schema`.`' . $schemaTable . '` WHERE ' . $schemaCondition . ' ORDER BY `ORDINAL_POSITION`';
            $rows = $connection->select($sql);
            if (empty($rows)) {
                throw new Exception('Table ' . $tableName . ' does not exists in ' . $database);
            }
            if (static::isUpperCase($connectionName)) {
                foreach ($rows as $row) {
                    $columns[$row->COLUMN_NAME] = [
                        'type' => $row->COLUMN_TYPE,
                        'default' => $row->COLUMN_DEFAULT,
                        'is_nullable' => $row->IS_NULLABLE,
                        'comment' => $row->COLUMN_COMMENT
                    ];
                }
            } else {
                foreach ($rows as $row) {
                    $columns[$row->column_name] = [
                        'type' => $row->column_type,
                        'default' => $row->column_default,
                        'is_nullable' => $row->is_nullable,
                        'comment' => $row->column_comment
                    ];
                }
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
