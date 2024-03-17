<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Utility;

use Exception;

final class DataUtility
{
    /**
     * @throws Exception
     */
    public static function assert(bool $condition, ?Exception $exception = null): void
    {
        if ($condition) {
            return;
        }

        if (null === $exception) {
            throw new Exception();
        }

        throw $exception;
    }
}
