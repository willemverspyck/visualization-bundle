<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Utility;

use DateTime;
use DateTimeInterface;
use Exception;

final class DateTimeUtility
{
    public const FORMAT_DATE = 'Y-m-d';
    public const FORMAT_DATETIME = 'Y-m-d H:i:s';
    public const FORMAT_TIME = 'H:i:s';

    /**
     * @throws Exception
     */
    public static function getDateFromString(?string $content, string $format = self::FORMAT_DATE): ?DateTimeInterface
    {
        if (null === $content) {
            return null;
        }

        $date = DateTime::createFromFormat($format, $content);

        if (false === $date) {
            throw new Exception(sprintf('Date value "%s" not valid', $content));
        }

        $date->setTime(0, 0);

        return $date;
    }

    /**
     * @throws Exception
     */
    public static function getDateTimeFromString(?string $content, string $format = self::FORMAT_DATETIME): ?DateTimeInterface
    {
        if (null === $content) {
            return null;
        }

        $date = DateTime::createFromFormat($format, $content);

        if (false === $date) {
            throw new Exception(sprintf('DateTime value "%s" not valid', $content));
        }

        return $date;
    }

    /**
     * @throws Exception
     */
    public static function getTimeFromString(?string $content, string $format = self::FORMAT_TIME): ?DateTimeInterface
    {
        if (null === $content) {
            return null;
        }

        $date = DateTime::createFromFormat($format, $content);

        if (false === $date) {
            throw new Exception(sprintf('Time value "%s" not valid', $content));
        }

        return $date;
    }
}
