<?php

declare(strict_types=1);

namespace Verdient\Dora\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\DbConnection\Db;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Verdient\cli\Console;

/**
 * 检索字段
 * @Command
 * @author Verdient。
 */
class SearchColumnCommand extends AbstractCommand
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected $name = 'search:column';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function configure()
    {
        parent::configure();
        $this->setDescription('检索字段');
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function handle()
    {
        if (!$name = $this->input->getArgument('name')) {
            while (!$name) {
                $name = $this->ask('请输入要检索的字段');
            }
        }
        $pool = $this->input->getOption('pool');
        $connection = Db::connection($pool);
        $database = call_user_func([$connection, 'getDatabaseName']);
        $columns = $connection->select('SELECT `table_name`, `column_name`, `column_default`, `is_nullable`, `column_type`, `column_comment` from information_schema.columns where `table_schema` = \'' . $database . '\' ORDER BY `TABLE_NAME`, `ORDINAL_POSITION`');
        $result = [];
        foreach ($columns as $column) {
            if (strpos($column->column_name, $name) !== false) {
                $result[] = [
                    $column->table_name,
                    $column->column_name,
                    $column->column_type,
                    gettype($column->column_default) . ' ' . $column->column_default,
                    $column->is_nullable,
                    $column->column_comment,
                ];
            }
        }
        if (empty($result)) {
            return $this->info('没有找到与 ' . $name . ' 匹配的字段');
        }
        Console::table($result, ['数据表名称', '字段名称', '类型', '默认值', '允许为空', '注释']);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, '字段名称']
        ];
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function getOptions()
    {
        return [
            ['pool', 'p', InputOption::VALUE_OPTIONAL, '要使用的连接', 'default']
        ];
    }
}
