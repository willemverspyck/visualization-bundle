<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Utility;

use Exception;

final class CacheUtility
{
    /**
     * @throws Exception
     */
    public static function getCacheKey(string $region, array $fields): string
    {
        $content = preg_replace('/(\\\\)/i', '_', $region);
        $content = strtolower($content);

        foreach ($fields as $field) {
            if (null === $field) {
                throw new Exception('Empty cache key');
            }

            $key = sprintf('%s', $field);

            if (0 === strlen($key)) {
                throw new Exception('Empty cache key');
            }

            if (1 === preg_match('/[{}()\/\\\\@:]/i', $key)) {
                $key = md5($key);
            }

            $content .= sprintf('[%s]', $key);
        }

        return $content;
    }
}
