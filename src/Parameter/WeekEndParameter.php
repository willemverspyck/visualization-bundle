<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use DateTimeImmutable;
use Spyck\VisualizationBundle\Request\RequestInterface;

final class WeekEndParameter extends AbstractDateParameter
{
    public function __construct(private readonly int $weekday = DateParameterInterface::MONDAY)
    {
    }

    public function getData(): ?DateTimeImmutable
    {
        $data = parent::getData();

        if (null === $data) {
            return null;
        }

        $modifier = match($this->weekday) {
            DateParameterInterface::SUNDAY => 'Saturday this week',
            DateParameterInterface::MONDAY => 'Sunday this week',
            default => throw new Exception('Unknown weekday'),
        };

        return $data->modify($modifier);
    }

    public static function getField(): string
    {
        return RequestInterface::DATE_END;
    }

    public static function getName(): string
    {
        return RequestInterface::DATE_END;
    }
}
