<?php

declare(strict_types=1);

namespace Verdient\Dora\Listener;

use Hyperf\Database\Model\Events\Updated;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Verdient\Dora\Annotation\DataRedundancyCollector;

/**
 * 模型数据更新监听器
 * @Listener
 * @author Verdient。
 */
class ModelDataUpdatedListener implements ListenerInterface
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function listen(): array
    {
        return [
            Updated::class
        ];
    }

    /**
     * @param Updated $event
     * @author Verdient。
     */
    public function process(object $event)
    {
        $model = $event->getModel();
        $dirty = $model->getDirty();
        if (!empty($dirty)) {
            if ($dataRedundancys = DataRedundancyCollector::get(get_class($model))) {
                $explainPlan = [];
                foreach ($dirty as $field => $value) {
                    if (isset($dataRedundancys[$field])) {
                        foreach ($dataRedundancys[$field] as $targetClass => $dataRedundancy) {
                            list($targetField, $sourceKeyField, $keyField) = $dataRedundancy;
                            $key = md5($targetClass . PHP_EOL . $sourceKeyField . PHP_EOL . $keyField);
                            if ($model->$sourceKeyField) {
                                if (!isset($explainPlan[$key])) {
                                    $explainPlan[$key] = [
                                        $targetClass,
                                        $keyField,
                                        $sourceKeyField,
                                        []
                                    ];
                                }
                                $explainPlan[$key][3][$targetField] = $value;
                            }
                        }
                    }
                }
                foreach ($explainPlan as $explain) {
                    list($targetClass, $keyField, $sourceKeyField, $updates) = $explain;
                    $query = $targetClass::query()->where([$keyField => $model->$sourceKeyField]);
                    $targetModel = $query->getModel();
                    if (property_exists($targetModel, 'timestamps')) {
                        $targetModel->timestamps = false;
                    }
                    $query->update($updates);
                }
            }
        }
    }
}
