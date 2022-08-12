<?php

declare(strict_types=1);

namespace Verdient\Dora\Languages;

use Verdient\Dora\Utils\Arr;

/**
 * 语言源
 * @author Verdient。
 */
class LanguageSource
{
    /**
     * 合并翻译文件
     * @param string $language 语言
     * @param string $name 名称
     * @param array $translates 要合并的翻译内容
     * @return array
     * @author Verdient。
     */
    public static function merge(string $language, string $name, array $translates)
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $name . '.php';
        if (file_exists($path)) {
            return Arr::merge(require($path), $translates);
        }
        return $translates;
    }
}
