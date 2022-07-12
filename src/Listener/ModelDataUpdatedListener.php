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
                foreach ($dirty as $field => $value) {
                    if (isset($dataRedundancys[$field])) {
                        foreach ($dataRedundancys[$field] as $targetClass => $dataRedundancy) {
                            list($targetField, $sourceKeyField, $keyField) = $dataRedundancy;
                            if ($model->$sourceKeyField) {
                                $query = $targetClass::query()->where([$keyField => $model->$sourceKeyField]);
                                $targetModel = $query->getModel();
                                if (property_exists($targetModel, 'timestamps')) {
                                    $targetModel->timestamps = false;
                                }
                                $query->update([$targetField => $value]);
                            }
                        }
                    }
                }
            }
        }
    }
}
