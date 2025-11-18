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
    public static function getDateFromString(?string $content, string $format = self::FORMAT_DATE): ?DateTimeImmutable
    {
        return self::getFromString($content, $format)?->setTime(0, 0);
    }

    /**
     * @throws Exception
     */
    public static function getDateTimeFromString(?string $content, string $format = self::FORMAT_DATETIME): ?DateTimeImmutable
    {
        return self::getFromString($content, $format);
    }

    /**
     * @throws Exception
     */
    public static function getTimeFromString(?string $content, string $format = self::FORMAT_TIME): ?DateTimeImmutable
    {
        return self::getFromString($content, $format);
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
