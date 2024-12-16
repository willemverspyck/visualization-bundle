<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use DateTimeImmutable;
use Exception;
use Spyck\VisualizationBundle\Request\RequestInterface;

final class WeekStartParameter extends AbstractDateParameter
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
            DateParameterInterface::SUNDAY => 'Sunday this week',
            DateParameterInterface::MONDAY => 'Monday this week',
            default => throw new Exception('Unknown weekday'),
        };

        return $data->modify($modifier);
    }

    public static function getField(): string
    {
        return RequestInterface::DATE_START;
    }

    public static function getName(): string
    {
        return RequestInterface::DATE_START;
    }
}
