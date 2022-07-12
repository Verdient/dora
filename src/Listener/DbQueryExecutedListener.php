<?php

declare(strict_types=1);

namespace Verdient\Dora\Listener;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Verdient\cli\Console;

/**
 * 数据库查询执行监听器
 * @Listener
 * @author Verdient。
 */
class DbQueryExecutedListener implements ListenerInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array 忽略的前缀
     * @author Verdient。
     */
    protected $ignorePrefixes = [
        'SELECT `column_name`, `column_default`, `is_nullable`, `column_type`, `column_comment` FROM `information_schema`.`columns` WHERE `table_schema` = '
    ];

    /**
     * @var bool 是否打印SQL
     * @author Verdient。
     */
    protected $printSql = false;

    /**
     * @var bool 是否将SQL记录到日志
     * @author Verdient。
     */
    protected $logSql = false;

    /**
     * @var bool 是否启用
     * @author Verdient。
     */
    protected $isEnabled = false;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __construct(ContainerInterface $container)
    {
        $this->printSql = config('print_sql');
        $this->logSql = config('log_sql');
        if ($this->logSql) {
            $this->logger = $container->get(LoggerFactory::class)->get('sql', 'sql');
        }
        $this->isEnabled = $this->printSql || $this->logSql;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function listen(): array
    {
        return [
            QueryExecuted::class,
        ];
    }

    /**
     * @param QueryExecuted $event
     * @author Verdient。
     */
    public function process(object $event)
    {
        if ($event instanceof QueryExecuted) {
            if ($this->isEnabled && !$this->isIgnore($event->sql)) {
                $sql = $event->sql;
                if (!Arr::isAssoc($event->bindings)) {
                    $placeholder = md5(random_bytes(64));
                    foreach ($event->bindings as $value) {
                        if (is_null($value)) {
                            $value = 'null';
                        } else if (is_int($value) || is_float($value)) {
                            $value = (string) $value;
                        } else {
                            $value = "'" . str_replace('?', $placeholder, $value) . "'";
                        }
                        $sql = Str::replaceFirst('?', $value, $sql);
                    }
                    $sql = str_replace($placeholder, '?', $sql);
                }
                if (config('print_sql')) {
                    Console::output(implode(' ', [
                        date('Y-m-d H:i:s'),
                        Console::colour('[' . $event->time . ' ms]', Console::FG_YELLOW),
                        Console::colour($event->connectionName, Console::FG_GREEN),
                        Console::colour($sql, Console::FG_BLUE)
                    ]));
                }
                if (config('log_sql', false)) {
                    $this->logger->info(sprintf('[%s ms] %s %s', $event->time, $event->connectionName, $sql));
                }
            }
        }
    }

    /**
     * 判断是否应该忽略
     * @param string $sql SQL语句
     * @return bool
     * @author Verdient。
     */
    protected function isIgnore($sql): bool
    {
        foreach ($this->ignorePrefixes as $prefix) {
            if (substr($sql, 0, strlen($prefix)) === $prefix) {
                return true;
            }
        }
        return false;
    }
}
