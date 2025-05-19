<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Utility;

use DateTimeImmutable;
use Exception;

final class DateTimeUtility
{
    public const string FORMAT_DATE = 'Y-m-d';
    public const string FORMAT_DATETIME = 'Y-m-d H:i:s';
    public const string FORMAT_TIME = 'H:i:s';

    /**
     * @throws Exception
     */
    public static function getDateFromString(?string $content): ?DateTimeImmutable
    {
        return self::getFromString($content, self::FORMAT_DATE)?->setTime(0, 0);
    }

    /**
     * @throws Exception
     */
    public static function getDateTimeFromString(?string $content): ?DateTimeImmutable
    {
        return self::getFromString($content, self::FORMAT_DATETIME);
    }

    /**
     * @throws Exception
     */
    public static function getTimeFromString(?string $content): ?DateTimeImmutable
    {
        return self::getFromString($content, self::FORMAT_TIME);
    }

    /**
     * @throws Exception
     */
    public static function getFromString(?string $content, string $format): ?DateTimeImmutable
    {
        if (null === $content) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat($format, $content);

        if (false === $date) {
            throw new Exception(sprintf('Date "%s" does not match "%s"', $content, $format));
        }

        return $date;
    }
}
