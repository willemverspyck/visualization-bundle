<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Utility;

use Exception;

final class ArrayUtility
{
    public static function hasKeysInArray(array $keys, array $data): bool
    {
        return array_all($keys, fn ($key) => array_key_exists($key, $data));
    }

    /**
     * @throws Exception
     */
    public static function hasKeysInArrayWithException(array $keys, array $data): void
    {
        if (false === self::hasKeysInArray($keys, $data)) {
            throw new Exception(sprintf('Field not found (%s)', implode(', ', $keys)));
        }
    }
}
