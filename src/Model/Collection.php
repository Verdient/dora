<?php

declare(strict_types=1);

namespace Verdient\Dora\Model;

use Hyperf\Database\Model\Collection as ModelCollection;

/**
 * @template TKey of array-key
 * @template TModel of \Verdient\Dora\Model\AbstractModel
 * @method static<array<TKey, TValue>>keyBy((callable(TValue, TKey): array-key)|array|string $keyBy)
 * @author Verdient。
 */
class Collection extends ModelCollection
{
}
