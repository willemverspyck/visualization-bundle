<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Utility;

use DateTimeInterface;
use Exception;
use Spyck\VisualizationBundle\Model\Callback;

final class FieldUtility
{
    /**
     * @throws Exception
     */
    public static function getDivision(array $data, array $parameters): ?float
    {
        ArrayUtility::hasKeysInArrayWithException(['dividend', 'divisor'], $parameters);

        $dataDividend = self::getValue($parameters['dividend'], $data);
        $dataDivisor = self::getValue($parameters['divisor'], $data);

        if (null !== $dataDividend && null !== $dataDivisor && $dataDivisor > 0) {
            return $dataDividend / $dataDivisor;
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public static function getSubtract(array $data, array $parameters): ?float
    {
        ArrayUtility::hasKeysInArrayWithException(['value', 'subtract'], $parameters);

        $dataValue = self::getValue($parameters['value'], $data);
        $dataSubtract = self::getValue($parameters['subtract'], $data);

        if (null !== $dataValue && null !== $dataSubtract) {
            return $dataValue - $dataSubtract;
        }

        return null;
    }

    /**
     * Get the rate of the difference.
     */
    public static function getSubtractRate(array $data, array $parameters): ?float
    {
        ArrayUtility::hasKeysInArrayWithException(['value', 'subtract'], $parameters);

        $dataValue = self::getValue($parameters['value'], $data);
        $dataSubtract = self::getValue($parameters['subtract'], $data);

        if (null !== $dataValue && null !== $dataSubtract && $dataSubtract > 0) {
            return ($dataValue / $dataSubtract) - 1;
        }

        return null;
    }

    public static function getValue(bool|Callback|DateTimeInterface|float|int|string|null $value, array $data): bool|DateTimeInterface|float|int|string|null
    {
        if ($value instanceof Callback) {
            return call_user_func($value->getName(), $data, $value->getParameters());
        }

        if (null === $value) {
            return null;
        }

        return array_key_exists($value, $data) ? $data[$value] : null;
    }
}
