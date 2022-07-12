<?php

declare(strict_types=1);

namespace Verdient\Dora\Controller;

use Hyperf\Utils\Contracts\Arrayable;
use Verdient\Dora\Component\DataProvider;

/**
 * 序列化器
 * @author Verdient。
 */
class Serializer
{
    /**
     * 序列化数据提供器
     * @param DataProvider $dataProvider 数据提供器
     * @return array
     * @author Verdient。
     */
    public static function dataProvider(DataProvider $dataProvider): array
    {
        return [
            'page' => $dataProvider->getPage(),
            'page_size' => $dataProvider->getPageSize(),
            'last_page' => $dataProvider->getLastPage(),
            'count' => $dataProvider->getCount(),
            'rows' => $dataProvider->getRows()
        ];
    }

    /**
     * 格式化数据
     * @param mixed $data 待格式化的数据
     * @return mixed
     * @author Verdient。
     */
    public static function normalize($data)
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = static::normalize($value);
            }
        } else if (is_int($data)) {
            if ($data > 2147483647) {
                $data = (string) $data;
            }
        }
        return $data;
    }
}
